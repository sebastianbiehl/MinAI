<?php
/**
 * MinAI Complete Bridge - Preprocessing
 * 
 * This handles regular player input processing and translation
 */

// Prevent multiple inclusions
if (!defined('MINAI_BRIDGE_PREPROCESSING_LOADED')) {
    define('MINAI_BRIDGE_PREPROCESSING_LOADED', true);

// Ensure prerequest was loaded
if (!isset($GLOBALS['MINAI_BRIDGE_CONFIG'])) {
    require_once(__DIR__ . '/prerequest.php');
}

// Mock functions that MinAI expects
if (!function_exists('IsEnabled')) {
    function IsEnabled($name, $flag) {
        // For bridge mode, assume basic features are enabled
        $enabledFlags = [
            'isRoleplaying' => true,
            'isTalkingToNarrator' => true, 
            'isDungeonMaster' => true
        ];
        return isset($enabledFlags[$flag]) ? $enabledFlags[$flag] : false;
    }
}

// Check if text needs translation
if (!function_exists('minai_bridge_needs_translation')) {
    function minai_bridge_needs_translation($text) {
        if (empty($text)) return false;
        
        $casualMarkers = [
            'lol', 'lmao', 'wtf', 'omg', 'tbh', 'ngl', 'fr', 'imo',
            'yeah', 'yep', 'nah', 'nope', 'gonna', 'wanna', 'gotta',
            'dunno', 'kinda', 'sorta', 'hey', 'yo', 'dude', 'bro'
        ];
        
        $lowerText = strtolower($text);
        
        foreach ($casualMarkers as $marker) {
            if (strpos($lowerText, $marker) !== false) {
                return true;
            }
        }
        
        return false;
    }
}

// Check if this request should be handled by MinAI for translation/narrator
if (!function_exists('minai_bridge_should_process_player_input')) {
    function minai_bridge_should_process_player_input($gameRequest) {
        if (!is_array($gameRequest) || empty($gameRequest[0])) {
            return false;
        }
        
        $requestType = $gameRequest[0];
        $message = isset($gameRequest[3]) ? $gameRequest[3] : '';
        
        // Player input types that might need processing
        $playerInputTypes = ['inputtext', 'inputtext_s', 'ginputtext', 'ginputtext_s'];
        
        if (in_array($requestType, $playerInputTypes)) {
            // Check if player is in roleplay mode (translation)
            if (IsEnabled($GLOBALS["PLAYER_NAME"] ?? 'Player', 'isRoleplaying')) {
                return true;
            }
            
            // Check if talking to narrator
            if (IsEnabled($GLOBALS["PLAYER_NAME"] ?? 'Player', 'isTalkingToNarrator')) {
                return true;
            }
            
            // Check if this looks like casual speech that needs translation
            if (minai_bridge_needs_translation($message)) {
                return true;
            }
        }
        
        // Radiant conversation types
        $radiantTypes = [
            'radiant', 'radiantsearchinghostile', 'radiantsearchingfriend',
            'radiantcombathostile', 'radiantcombatfriend', 'rechat'
        ];
        
        if (in_array($requestType, $radiantTypes)) {
            return true;
        }
        
        return false;
    }
}

// Main preprocessing logic - intercept and process player input
if (isset($GLOBALS['gameRequest']) && is_array($GLOBALS['gameRequest'])) {
    $gameRequest = $GLOBALS['gameRequest'];
    
    // Skip if backend is not running
    if (!isset($GLOBALS['MINAI_BRIDGE_BACKEND_RUNNING']) || !$GLOBALS['MINAI_BRIDGE_BACKEND_RUNNING']) {
        minai_bridge_log("Skipping preprocessing - JavaScript backend not running");
    }
    // Only process if this is player input that needs MinAI processing
    else if (minai_bridge_should_process_player_input($gameRequest)) {
        $config = $GLOBALS['MINAI_BRIDGE_CONFIG'];
        
        minai_bridge_log("Processing player input: " . $gameRequest[0] . " - " . substr($gameRequest[3] ?? '', 0, 50));
        
        // Convert request type for backend
        $backendRequest = $gameRequest;
        $originalType = $gameRequest[0];
        
        // Map player input to appropriate MinAI processing
        if (in_array($originalType, ['inputtext', 'inputtext_s', 'ginputtext', 'ginputtext_s'])) {
            // Check if target is narrator
            if (isset($gameRequest[2]) && $gameRequest[2] === 'The Narrator') {
                $backendRequest[0] = 'inputtext'; // Narrator thoughts
            } else {
                $backendRequest[0] = 'minai_translate'; // Translation
            }
        }
        
        // Forward to JavaScript backend
        $response = minai_bridge_forward_request($backendRequest, $config['backend_url'], $config['timeout']);
        
        if ($response && isset($response['dialogue'])) {
            minai_bridge_log("Got response from backend, applying to game request");
            
            // Apply the response back to HerikaServer globals
            switch ($response['action']) {
                case 'narrator_thought':
                case 'combat_narrator':
                    // Narrator responses - set narrator as speaker
                    $GLOBALS['HERIKA_NAME'] = 'The Narrator';
                    $GLOBALS['target'] = 'The Narrator';
                    $GLOBALS["gameRequest"][3] = $response['dialogue'];
                    
                    // Set narrator voice
                    if (isset($response['tts']['voice'])) {
                        $GLOBALS['TTS']['FORCED_VOICE_DEV'] = $response['tts']['voice'];
                        $GLOBALS['TTS']['MELOTTS']['voiceid'] = $response['tts']['voice'];
                    }
                    break;
                    
                case 'translated_speech':
                    // Translation responses - format as player speech
                    $playerName = $GLOBALS["PLAYER_NAME"] ?? 'Player';
                    $GLOBALS["gameRequest"][0] = "inputtext";
                    $GLOBALS["gameRequest"][3] = $playerName . ": " . $response['dialogue'];
                    $GLOBALS["FORCED_TTS"] = true;
                    
                    // Set player voice
                    if (isset($response['tts']['voice'])) {
                        $GLOBALS['TTS']['FORCED_VOICE_DEV'] = $response['tts']['voice'];
                        $GLOBALS['TTS']['MELOTTS']['voiceid'] = $response['tts']['voice'];
                    }
                    break;
                    
                case 'dungeonmaster_event':
                    // DM events - set as narrator and broadcast
                    $GLOBALS['HERIKA_NAME'] = 'The Narrator';
                    $GLOBALS["gameRequest"][3] = $response['dialogue'];
                    
                    // Set narrator voice for DM events
                    $GLOBALS['TTS']['FORCED_VOICE_DEV'] = 'narrator';
                    $GLOBALS['TTS']['MELOTTS']['voiceid'] = 'narrator';
                    break;
                    
                default:
                    // Generic response handling
                    $GLOBALS["gameRequest"][3] = $response['dialogue'];
                    
                    if (isset($response['target']) && $response['target']) {
                        $GLOBALS['HERIKA_NAME'] = $response['target'];
                    }
                    
                    if (isset($response['tts']['voice'])) {
                        $GLOBALS['TTS']['FORCED_VOICE_DEV'] = $response['tts']['voice'];
                        $GLOBALS['TTS']['MELOTTS']['voiceid'] = $response['tts']['voice'];
                    }
            }
            
            minai_bridge_log("Successfully processed player input via JavaScript backend");
        } else {
            minai_bridge_log("Failed to get response from JavaScript backend for player input: " . $originalType);
        }
    } else {
        // Log that we're not handling this request
        minai_bridge_log("Not processing player input type: " . ($gameRequest[0] ?? 'unknown'));
    }
}

} // End include guard
?>