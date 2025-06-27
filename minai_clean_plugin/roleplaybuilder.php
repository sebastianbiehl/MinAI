<?php
// MinAI Clean Plugin - Roleplay/Translation Feature
// Transforms player input into character-appropriate speech

require_once("/var/www/html/HerikaServer/lib/data_functions.php");
require_once("util.php");

/**
 * Convert text from third person to first person
 */
function convertToFirstPerson($text, $name, $pronouns) {
    if (empty($text)) {
        return "";
    }

    // Basic name replacements
    $text = str_replace([
        "{$name} is",
        "{$name} has",
        "{$name}'s",
    ], [
        "You are",
        "You have", 
        "Your",
    ], $text);

    // General name replacement
    $text = preg_replace('/\b' . preg_quote($name, '/') . '\b(?!\')/', 'you', $text);
    
    // Capitalize 'you' at sentence start
    $text = preg_replace('/([.!?]\s+)you\b/', '$1You', $text);
    $text = preg_replace('/^you\b/', 'You', $text);

    // Pronoun replacements
    $text = str_replace([
        " {$pronouns['subject']} ",
        " {$pronouns['object']} ",
        " {$pronouns['possessive']} ",
        "Her ", "His ", "Their ",
        "She ", "He ", "They ",
    ], [
        " you ",
        " you ",
        " your ",
        "Your ", "Your ", "Your ",
        "You ", "You ", "You ",
    ], $text);

    return trim($text);
}

/**
 * Get player voice type for TTS
 */
function GetPlayerVoiceType() {
    if (isset($GLOBALS['player_voice_model']) && !empty($GLOBALS['player_voice_model'])) {
        return $GLOBALS['player_voice_model'];
    }
    
    $playerRace = isset($GLOBALS['PLAYER_RACE']) ? strtolower($GLOBALS['PLAYER_RACE']) : 'nord';
    $playerGender = isset($GLOBALS['PLAYER_GENDER']) ? strtolower($GLOBALS['PLAYER_GENDER']) : 'female';
    
    $voiceKey = $playerGender . $playerRace;
    if (isset($GLOBALS['voicetype_fallbacks'][$voiceKey])) {
        return $GLOBALS['voicetype_fallbacks'][$voiceKey];
    }
    
    return $playerGender === 'male' ? 'maleeventoned' : 'femaleeventoned';
}

/**
 * Main roleplay input processing function
 */
function interceptRoleplayInput() {
    // Check if this is roleplay input
    $isRoleplayMode = ($GLOBALS["gameRequest"][0] == "minai_roleplay");
    $isTranslationMode = IsEnabled($GLOBALS["PLAYER_NAME"], "isRoleplaying") && isPlayerInput();
    
    if (!$isRoleplayMode && !$isTranslationMode) {
        return;
    }
    
    minai_log("info", "Processing roleplay/translation input");
    
    // Disable roleplay flag
    if ($isTranslationMode) {
        SetEnabled($GLOBALS["PLAYER_NAME"], "isRoleplaying", false);
    }
    
    // Get configuration
    $settings = $GLOBALS['roleplay_settings'];
    $playerName = $GLOBALS["PLAYER_NAME"];
    $playerPronouns = GetActorPronouns($playerName);
    
    // Get original input
    $originalInput = GetCleanedMessage();
    
    // Build context
    $contextData = GetRecentContext("", $settings['context_messages']);
    $nearbyActors = array_filter(array_map('trim', explode('|', DataBeingsInRange())));
    $vitals = GetActorValue($playerName, "vitals");
    $equipment = GetUnifiedEquipmentContext($playerName, true);
    
    // Determine scene type
    $inCombat = IsEnabled($playerName, "inCombat");
    $isExplicit = IsExplicitScene();
    
    // Select appropriate prompts
    if ($isRoleplayMode) {
        $systemPrompt = $inCombat ? $settings['roleplay_system_prompt_combat'] : $settings['roleplay_system_prompt'];
        $requestFormat = $inCombat ? $settings['roleplay_request_combat'] : $settings['roleplay_request'];
    } else {
        $systemPrompt = $inCombat ? $settings['system_prompt_combat'] : $settings['system_prompt'];
        $requestFormat = $inCombat ? $settings['translation_request_combat'] : $settings['translation_request'];
    }
    
    // Variable replacements
    $variables = [
        'PLAYER_NAME' => $playerName,
        'PLAYER_BIOS' => $GLOBALS["PLAYER_BIOS"],
        'HERIKA_PERS' => $GLOBALS["HERIKA_PERS"],
        'HERIKA_DYNAMIC' => $GLOBALS["HERIKA_DYNAMIC"],
        'ORIGINAL_INPUT' => $originalInput,
        'NEARBY_ACTORS' => implode(", ", $nearbyActors),
        'VITALS' => $vitals,
        'CLOTHING_STATUS' => $equipment,
        'WEATHER' => GetActorValue($playerName, "weather"),
        'RECENT_EVENTS' => implode("\n", array_map(function($ctx) { 
            return $ctx['content'] ?? ''; 
        }, $contextData)),
        'PLAYER_SUBJECT' => $playerPronouns['subject'],
        'PLAYER_OBJECT' => $playerPronouns['object'],
        'PLAYER_POSSESSIVE' => $playerPronouns['possessive']
    ];
    
    // Build messages
    $systemPrompt = replaceVariables($systemPrompt, $variables);
    $requestPrompt = replaceVariables($requestFormat, $variables);
    
    // Build context message
    $contextMessage = "";
    foreach ($settings['sections'] as $section) {
        if (!$section['enabled']) continue;
        
        if ($contextMessage !== '') {
            $contextMessage .= "\n\n";
        }
        
        $content = $section['header'] . "\n";
        $content .= replaceVariables($section['content'], $variables);
        $contextMessage .= $content;
    }
    
    $messages = [
        ['role' => 'system', 'content' => $systemPrompt],
        ['role' => 'system', 'content' => $contextMessage],
        ['role' => 'user', 'content' => $requestPrompt]
    ];
    
    minai_log("info", "Sending roleplay request to LLM");
    
    // Call LLM (simplified - would need full integration)
    $response = callLLM($messages, $GLOBALS["CONNECTOR"]["openrouter"]["model"], [
        'temperature' => 0.7,
        'max_tokens' => 1024
    ]);
    
    if ($response !== null) {
        // Clean up response
        $response = trim($response, "\"' \n");
        $response = preg_replace('/^' . preg_quote($playerName . ':') . '\s*/', '', $response);
        $response = str_replace(['"', '"'], '', $response);
        
        minai_log("info", "Transformed input: '{$originalInput}' -> '{$response}'");
        
        // Format output
        if ($isRoleplayMode) {
            // For roleplay mode, generate TTS
            $GLOBALS["gameRequest"][0] = "inputtext";
            $GLOBALS["gameRequest"][3] = $playerName . ": " . $response;
            $GLOBALS["FORCED_TTS"] = true;
        } else {
            // For translation mode, replace the input
            $GLOBALS["gameRequest"][0] = "inputtext";
            $GLOBALS["gameRequest"][3] = $playerName . ": " . $response;
            $GLOBALS["FORCED_TTS"] = true;
        }
    } else {
        minai_log("warning", "Failed to generate roleplay response");
    }
}