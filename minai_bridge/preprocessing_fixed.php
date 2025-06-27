<?php
/**
 * MinAI Bridge - Fixed Main Processing
 * 
 * This handles the actual request processing the way original MinAI does
 */

// Prevent multiple inclusions
if (!defined('MINAI_BRIDGE_PROCESSING_LOADED')) {
    define('MINAI_BRIDGE_PROCESSING_LOADED', true);

// Ensure prerequest was loaded
if (!isset($GLOBALS['MINAI_BRIDGE_CONFIG'])) {
    require_once(__DIR__ . '/prerequest.php');
}

// Mock functions that MinAI expects but we don't have in bridge mode
if (!function_exists('IsEnabled')) {
    function IsEnabled($name, $flag) {
        // For bridge mode, we'll assume basic features are enabled
        $enabledFlags = [
            'isRoleplaying' => true,
            'isTalkingToNarrator' => true, 
            'isDungeonMaster' => true
        ];
        return isset($enabledFlags[$flag]) ? $enabledFlags[$flag] : false;
    }
}

if (!function_exists('GetActorValue')) {
    function GetActorValue($name, $key, $default = null) {
        // Simple mock - in real implementation this would check a database/cache
        return $default;
    }
}

if (!function_exists('SetActorValue')) {
    function SetActorValue($name, $key, $value) {
        // Simple mock - in real implementation this would store in database/cache
        return true;
    }
}

// Check if this is a request MinAI should handle
if (!function_exists('minai_bridge_should_handle_request')) {
    function minai_bridge_should_handle_request($gameRequest) {
        if (!is_array($gameRequest) || empty($gameRequest[0])) {
            return false;
        }
        
        $requestType = $gameRequest[0];
        $message = isset($gameRequest[3]) ? $gameRequest[3] : '';
        
        // Direct MinAI requests
        $directMinaiTypes = [
            'minai_roleplay',
            'minai_translate', 
            'minai_narrator',
            'minai_diary',
            'minai_diary_player',
            'minai_dungeon_master',
            'minai_combatendvictory',
            'minai_bleedoutself',
            'minai_updateprofile',
            'minai_updateprofile_player',
            'minai_narrator_talk',
            'minai_force_rechat'
        ];
        
        if (in_array($requestType, $directMinaiTypes)) {
            return true;
        }
        
        // Player input that might need translation/roleplay
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

// Check if text needs translation (copied from translator.js logic)
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

// Convert request to backend format
if (!function_exists('minai_bridge_convert_request')) {
    function minai_bridge_convert_request($gameRequest) {
        $requestType = $gameRequest[0];
        $speaker = $gameRequest[1] ?? '';
        $target = $gameRequest[2] ?? '';  
        $message = $gameRequest[3] ?? '';
        $additionalData = $gameRequest[4] ?? '';
        
        // Map original request types to backend types
        $typeMapping = [
            'inputtext' => 'minai_translate', // Player input goes through translation
            'inputtext_s' => 'minai_translate',
            'ginputtext' => 'minai_translate', 
            'ginputtext_s' => 'minai_translate',
            'minai_roleplay' => 'minai_translate',
            'minai_narrator_talk' => 'inputtext', // Narrator talk becomes narrator thoughts
            'rechat' => 'inputtext'
        ];
        
        $backendType = isset($typeMapping[$requestType]) ? $typeMapping[$requestType] : $requestType;
        
        // Special handling for narrator
        if ($target === 'The Narrator' || $speaker === 'The Narrator') {
            $backendType = 'inputtext'; // Narrator requests
        }
        
        return [
            $backendType,
            $speaker ?: ($GLOBALS["PLAYER_NAME"] ?? 'Player'),
            $target ?: '',
            $message,
            $additionalData
        ];
    }
}

// Apply backend response to HerikaServer globals
if (!function_exists('minai_bridge_apply_response')) {
    function minai_bridge_apply_response($response, $originalRequest) {
        if (!$response || !isset($response['dialogue'])) {
            return false;
        }
        
        $originalType = $originalRequest[0];
        $playerName = $GLOBALS["PLAYER_NAME"] ?? 'Player';
        
        // Handle different response types
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
                if (isset($response['tts']['voice'])) {
                    $GLOBALS['TTS']['FORCED_VOICE_DEV'] = 'narrator';
                    $GLOBALS['TTS']['MELOTTS']['voiceid'] = 'narrator';
                }
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
        
        minai_bridge_log("Applied response: " . substr($response['dialogue'], 0, 100) . "...");
        return true;
    }
}

// Main processing logic - this is where the magic happens
if (isset($GLOBALS['gameRequest']) && is_array($GLOBALS['gameRequest'])) {
    $gameRequest = $GLOBALS['gameRequest'];
    
    // Skip if backend is not running
    if (!isset($GLOBALS['MINAI_BRIDGE_BACKEND_RUNNING']) || !$GLOBALS['MINAI_BRIDGE_BACKEND_RUNNING']) {
        minai_bridge_log("Skipping request - JavaScript backend not running");
    }
    // Check if this request should be handled by MinAI
    else if (minai_bridge_should_handle_request($gameRequest)) {
        $config = $GLOBALS['MINAI_BRIDGE_CONFIG'];
        
        minai_bridge_log("Processing MinAI request: " . $gameRequest[0] . " - " . substr($gameRequest[3] ?? '', 0, 50));
        
        // Convert request to backend format
        $backendRequest = minai_bridge_convert_request($gameRequest);
        
        // Forward to JavaScript backend
        $response = minai_bridge_forward_request($backendRequest, $config['backend_url'], $config['timeout']);
        
        if ($response) {
            // Apply the response back to HerikaServer
            minai_bridge_apply_response($response, $gameRequest);
            minai_bridge_log("Successfully processed MinAI request via JavaScript backend");
        } else {
            minai_bridge_log("Failed to get response from JavaScript backend for: " . $gameRequest[0]);
        }
    } else {
        // Log that we're not handling this request
        minai_bridge_log("Not handling request type: " . ($gameRequest[0] ?? 'unknown'));
    }
}

} // End include guard
?>