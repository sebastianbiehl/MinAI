<?php
// Simplified MinAI Bridge - Just Replace the Processing Function
require_once("utils/metrics_util.php");

$fast_commands = ["addnpc","_quest","setconf","request","_speech","infoloc","infonpc","infonpc_close",
"infoaction","status_msg","delete_event","itemfound","_questdata","_uquest","location","_questreset"];

// Check for exact matches against fast commands
if (isset($GLOBALS["gameRequest"]) && in_array($GLOBALS["gameRequest"][0], $fast_commands)) {
    $GLOBALS["minai_skip_processing"] = true;
}

// Avoid processing for fast / storage events
if (isset($GLOBALS["minai_skip_processing"]) && $GLOBALS["minai_skip_processing"]) {
    return;
}

minai_start_timer('preprocessing_php', 'MinAI');

// Initialize common variables (simplified)
if (!isset($GLOBALS["PLAYER_NAME"])) {
    $GLOBALS["PLAYER_NAME"] = "Player";
}

// Check for banned phrases
$banned_phrases = ["Thank you for watching", "Thanks for watching", "Thank you very much for watching"];
if (isset($GLOBALS["gameRequest"][3])) {
    $message = strtolower($GLOBALS["gameRequest"][3]);
    foreach ($banned_phrases as $phrase) {
        if (stripos($message, strtolower($phrase)) !== false) {
            error_log("MinAI: Aborting request due to banned phrase: " . $phrase);
            die("Banned phrase detected: " . $phrase);
        }
    }
}

// Handle diary requests
if (isset($GLOBALS["gameRequest"][0]) && $GLOBALS["gameRequest"][0] == "minai_diary") {
    $GLOBALS["gameRequest"][0] = "diary";
}

if (isset($GLOBALS["gameRequest"][0]) && $GLOBALS["gameRequest"][0] == "minai_diary_player") {
    $GLOBALS["gameRequest"][0] = "diary";
}

// THIS IS THE KEY PART - Replace interceptRoleplayInput with JavaScript backend call
function interceptRoleplayInput() {
    minai_start_timer('interceptRoleplayInput', 'preprocessing_php');
    
    // Check if we should process this request
    if (!isset($GLOBALS["gameRequest"]) || !is_array($GLOBALS["gameRequest"])) {
        minai_stop_timer('interceptRoleplayInput');
        return;
    }
    
    $gameRequest = $GLOBALS["gameRequest"];
    $requestType = $gameRequest[0] ?? '';
    $message = $gameRequest[3] ?? '';
    
    // Skip empty messages
    if (empty($message)) {
        minai_stop_timer('interceptRoleplayInput');
        return;
    }
    
    // Only process these request types
    $processableTypes = [
        'inputtext', 'inputtext_s', 'ginputtext', 'ginputtext_s',
        'minai_roleplay', 'minai_translate', 'diary',
        'minai_dungeon_master', 'minai_narrator'
    ];
    
    if (!in_array($requestType, $processableTypes)) {
        minai_stop_timer('interceptRoleplayInput');
        return;
    }
    
    // Check if JavaScript backend is running
    $backendUrl = 'http://localhost:8080';
    $context = stream_context_create([
        'http' => [
            'timeout' => 2,
            'method' => 'GET'
        ]
    ]);
    
    $healthCheck = @file_get_contents($backendUrl . '/health', false, $context);
    if (!$healthCheck) {
        error_log("MinAI Bridge: JavaScript backend not running on port 8080");
        minai_stop_timer('interceptRoleplayInput');
        return;
    }
    
    // Prepare request for JavaScript backend
    $backendRequest = [
        'gameRequest' => $gameRequest
    ];
    
    // Send to JavaScript backend
    $postContext = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n",
            'content' => json_encode($backendRequest),
            'timeout' => 10
        ]
    ]);
    
    $response = @file_get_contents($backendUrl, false, $postContext);
    
    if ($response) {
        $decodedResponse = json_decode($response, true);
        
        if ($decodedResponse && isset($decodedResponse['dialogue'])) {
            error_log("MinAI Bridge: Got response from backend: " . substr($decodedResponse['dialogue'], 0, 100));
            
            // Apply the response based on type
            $action = $decodedResponse['action'] ?? 'unknown';
            
            switch ($action) {
                case 'translated_speech':
                    // For translation, format as player speech
                    $playerName = $GLOBALS["PLAYER_NAME"] ?? 'Player';
                    $GLOBALS["gameRequest"][0] = "inputtext";
                    $GLOBALS["gameRequest"][3] = $playerName . ": " . $decodedResponse['dialogue'];
                    break;
                    
                case 'narrator_thought':
                case 'combat_narrator':
                    // For narrator, set as narrator response
                    $GLOBALS['HERIKA_NAME'] = 'The Narrator';
                    $GLOBALS["gameRequest"][3] = $decodedResponse['dialogue'];
                    break;
                    
                case 'dungeonmaster_event':
                    // For DM events, set as narrator
                    $GLOBALS['HERIKA_NAME'] = 'The Narrator';
                    $GLOBALS["gameRequest"][3] = $decodedResponse['dialogue'];
                    break;
                    
                default:
                    // Generic response
                    $GLOBALS["gameRequest"][3] = $decodedResponse['dialogue'];
            }
            
            // Set TTS voice if provided
            if (isset($decodedResponse['tts']['voice'])) {
                $GLOBALS['TTS']['FORCED_VOICE_DEV'] = $decodedResponse['tts']['voice'];
                $GLOBALS['TTS']['MELOTTS']['voiceid'] = $decodedResponse['tts']['voice'];
            }
            
        } else {
            error_log("MinAI Bridge: Invalid response from backend");
        }
    } else {
        error_log("MinAI Bridge: Failed to get response from backend");
    }
    
    minai_stop_timer('interceptRoleplayInput');
}

// Call the function just like the original does
interceptRoleplayInput();

minai_stop_timer('preprocessing_php');
?>