<?php
// MinAI Clean Plugin - Configuration API
header('Content-Type: application/json');

require_once("/var/www/html/HerikaServer/lib/data_functions.php");

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'load':
        loadConfiguration();
        break;
    case 'save':
        saveConfiguration();
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}

function loadConfiguration() {
    try {
        // Load configuration from file
        $configFile = __DIR__ . '/../config.base.php';
        if (file_exists($configFile)) {
            // Clear any existing globals first
            $originalGlobals = $GLOBALS;
            
            // Include the config file
            include $configFile;
            
            // Extract the configuration values
            $config = [
                'self_narrator' => $GLOBALS['self_narrator'] ?? false,
                'disable_nsfw' => $GLOBALS['disable_nsfw'] ?? false,
                'enforce_short_responses' => $GLOBALS['enforce_short_responses'] ?? false,
                'context_messages' => $GLOBALS['roleplay_settings']['context_messages'] ?? 8,
                'system_prompt' => $GLOBALS['roleplay_settings']['system_prompt'] ?? '',
                'translation_request' => $GLOBALS['roleplay_settings']['translation_request'] ?? '',
                'roleplay_system_prompt' => $GLOBALS['roleplay_settings']['roleplay_system_prompt'] ?? '',
                'roleplay_request' => $GLOBALS['roleplay_settings']['roleplay_request'] ?? '',
                'self_narrator_normal' => $GLOBALS['action_prompts']['self_narrator_normal'] ?? '',
                'self_narrator_explicit' => $GLOBALS['action_prompts']['self_narrator_explicit'] ?? ''
            ];
            
            // Restore original globals
            $GLOBALS = $originalGlobals;
            
            echo json_encode($config);
        } else {
            echo json_encode(['error' => 'Configuration file not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function saveConfiguration() {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            throw new Exception('Invalid JSON input');
        }
        
        // Generate new configuration file content
        $config = generateConfigFile($input);
        
        // Write to config file
        $configFile = __DIR__ . '/../config.base.php';
        if (file_put_contents($configFile, $config)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Failed to write configuration file']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function generateConfigFile($input) {
    $selfNarrator = $input['self_narrator'] ? 'true' : 'false';
    $disableNsfw = $input['disable_nsfw'] ? 'true' : 'false';
    $enforceShort = $input['enforce_short_responses'] ? 'true' : 'false';
    $contextMessages = (int)($input['context_messages'] ?? 8);
    
    // Escape strings for PHP
    function escapePhpString($str) {
        return addslashes($str);
    }
    
    $systemPrompt = escapePhpString($input['system_prompt'] ?? '');
    $translationRequest = escapePhpString($input['translation_request'] ?? '');
    $roleplaySystemPrompt = escapePhpString($input['roleplay_system_prompt'] ?? '');
    $roleplayRequest = escapePhpString($input['roleplay_request'] ?? '');
    $selfNarratorNormal = escapePhpString($input['self_narrator_normal'] ?? '');
    $selfNarratorExplicit = escapePhpString($input['self_narrator_explicit'] ?? '');
    
    return <<<PHP
<?php
// MinAI Clean Plugin Configuration
// Generated automatically - do not edit manually

// Self Narrator Configuration
\$GLOBALS['self_narrator'] = {$selfNarrator};
\$GLOBALS['use_narrator_profile'] = false;

// Core System Settings
\$GLOBALS['disable_nsfw'] = {$disableNsfw};
\$GLOBALS['enable_prompt_slop_cleanup'] = false;
\$GLOBALS['enforce_short_responses'] = {$enforceShort};

// Voice Configuration
\$GLOBALS['voicetype_fallbacks'] = Array(
    "maleargonian" => "argonianmale", 
    "femaleargonian" => "argonianfemale",
    "malekhajiit" => "khajiitmale", 
    "femalekhajiit" => "khajiitfemale",
    "maleredguard" => "maleeventonedaccented", 
    "femaleredguard" => "femaleeventonedaccented",
    "malenord" => "malecondescending", 
    "femalenord" => "femalecondescending",
    "malebreton" => "malecommoner", 
    "femalebreton" => "femalecommoner",
    "maleimperial" => "maleeventoned", 
    "femaleimperial" => "femaleeventoned",
    "maleorc" => "maleorc", 
    "femaleorc" => "femaleorc",
    "malealtmer" => "maleelfhaughty", 
    "femalealtmer" => "femaleelfthaughty",
    "malehighelf" => "maleelfhaughty", 
    "femalehighelf" => "femaleelfthaughty",
    "maledunmer" => "maledarkelf", 
    "femaledunmer" => "femaledarkelf",
    "maledarkelf" => "maledarkelf", 
    "femaledarkelf" => "femaledarkelf",
    "malewoodelf" => "bosmermaleeventoned", 
    "femalewoodelf" => "bosmerfemaleeventoned"
);

// Action Prompts - Core Features Only
\$GLOBALS['action_prompts'] = Array(
    "self_narrator_normal" => "{$selfNarratorNormal}",
    "self_narrator_explicit" => "{$selfNarratorExplicit}",
    "normal_scene" => "Your answer for #target# should reflect what #herika_name# would say in this situation. Express #herika_name#'s thoughts using vocabulary and speaking style that reflects #herika_name#'s personality."
);

// Roleplay/Translation Settings
\$GLOBALS['roleplay_settings'] = Array(
    "context_messages" => {$contextMessages},
    "system_prompt" => "{$systemPrompt}",
    "translation_request" => "{$translationRequest}",
    "roleplay_system_prompt" => "{$roleplaySystemPrompt}",
    "roleplay_request" => "{$roleplayRequest}",
    "sections" => Array(
        "CHARACTER_BACKGROUND" => Array(
            "enabled" => true,
            "header" => "## YOUR CHARACTER",
            "content" => "#PLAYER_BIOS#\\n#HERIKA_PERS#\\nPronouns: #PLAYER_SUBJECT#/#PLAYER_OBJECT#/#PLAYER_POSSESSIVE#\\nCurrent State: #HERIKA_DYNAMIC#",
            "order" => 0
        ),
        "CURRENT_STATUS" => Array(
            "enabled" => true,
            "header" => "## CURRENT STATUS",
            "content" => "#VITALS#\\n#CLOTHING_STATUS#",
            "order" => 1
        ),
        "ENVIRONMENT" => Array(
            "enabled" => true,
            "header" => "## ENVIRONMENT", 
            "content" => "Characters: #NEARBY_ACTORS#\\nLocations: #NEARBY_LOCATIONS#\\nWeather: #WEATHER#",
            "order" => 2
        ),
        "RECENT_EVENTS" => Array(
            "enabled" => true,
            "header" => "## RECENT EVENTS",
            "content" => "#RECENT_EVENTS#",
            "order" => 3
        )
    )
);
PHP;
}