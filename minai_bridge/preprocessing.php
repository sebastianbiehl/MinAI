<?php
/**
 * MinAI Bridge - Main Processing
 * 
 * This file handles the actual request forwarding to the JavaScript backend
 */

// Prevent multiple inclusions
if (!defined('MINAI_BRIDGE_PROCESSING_LOADED')) {
    define('MINAI_BRIDGE_PROCESSING_LOADED', true);

// Ensure prerequest was loaded
if (!isset($GLOBALS['MINAI_BRIDGE_CONFIG'])) {
    require_once(__DIR__ . '/prerequest.php');
}

// Forward request to JavaScript backend
if (!function_exists('minai_bridge_forward_request')) {
    function minai_bridge_forward_request($gameRequest, $backendUrl, $timeout = 10) {
        $requestData = [
            'gameRequest' => $gameRequest
        ];
        
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => json_encode($requestData),
                'timeout' => $timeout
            ]
        ]);
        
        $response = @file_get_contents($backendUrl, false, $context);
        
        if ($response === false) {
            minai_bridge_log("Failed to forward request to JavaScript backend");
            return null;
        }
        
        $decodedResponse = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            minai_bridge_log("Invalid JSON response from JavaScript backend: " . json_last_error_msg());
            return null;
        }
        
        return $decodedResponse;
    }
}

// Check if this is a MinAI request that should be handled
if (!function_exists('minai_bridge_should_handle')) {
    function minai_bridge_should_handle($gameRequest) {
        if (!is_array($gameRequest) || empty($gameRequest[0])) {
            return false;
        }
        
        $requestType = $gameRequest[0];
        
        // Handle these request types
        $handledTypes = [
            'inputtext',
            'minai_roleplay', 
            'minai_translate',
            'minai_narrator',
            'minai_diary',
            'minai_diary_player',
            'minai_dungeon_master',
            'minai_combatendvictory',
            'minai_bleedoutself',
            'minai_updateprofile',
            'minai_updateprofile_player'
        ];
        
        return in_array($requestType, $handledTypes);
    }
}

// Apply response to HerikaServer globals
if (!function_exists('minai_bridge_apply_response')) {
    function minai_bridge_apply_response($response) {
        if (!$response || !isset($response['dialogue'])) {
            return false;
        }
        
        // Set the response text that HerikaServer will use
        if (isset($response['target']) && $response['target']) {
            $GLOBALS['HERIKA_NAME'] = $response['target'];
        }
        
        // Set the main response text
        $GLOBALS['gameRequest'][3] = $response['dialogue'];
        
        // Handle TTS if provided
        if (isset($response['tts']) && $response['tts']) {
            if (isset($response['tts']['voice'])) {
                $GLOBALS['TTS']['FORCED_VOICE_DEV'] = $response['tts']['voice'];
                $GLOBALS['TTS']['MELOTTS']['voiceid'] = $response['tts']['voice'];
            }
        }
        
        // Handle special action types
        switch ($response['action']) {
            case 'narrator_thought':
            case 'combat_narrator':
                // For narrator responses, set appropriate speaker
                $GLOBALS['HERIKA_NAME'] = 'The Narrator';
                break;
                
            case 'translated_speech':
                // For translations, keep the original speaker
                break;
                
            case 'dungeonmaster_event':
                // For DM events, broadcast to all
                $GLOBALS['HERIKA_NAME'] = 'The Narrator';
                break;
        }
        
        minai_bridge_log("Applied response: " . substr($response['dialogue'], 0, 100) . "...");
        return true;
    }
}

// Main processing logic
if (isset($GLOBALS['gameRequest']) && is_array($GLOBALS['gameRequest'])) {
    $gameRequest = $GLOBALS['gameRequest'];
    
    // Check if backend is running
    if (!isset($GLOBALS['MINAI_BRIDGE_BACKEND_RUNNING']) || !$GLOBALS['MINAI_BRIDGE_BACKEND_RUNNING']) {
        minai_bridge_log("Skipping request - JavaScript backend not running");
    } 
    // Check if this is a request we should handle
    else if (minai_bridge_should_handle($gameRequest)) {
        $config = $GLOBALS['MINAI_BRIDGE_CONFIG'];
        
        minai_bridge_log("Forwarding request: " . $gameRequest[0] . " - " . substr($gameRequest[3] ?? '', 0, 50));
        
        // Forward the request to JavaScript backend
        $response = minai_bridge_forward_request($gameRequest, $config['backend_url'], $config['timeout']);
        
        if ($response) {
            // Apply the response to HerikaServer
            minai_bridge_apply_response($response);
            minai_bridge_log("Successfully processed request via JavaScript backend");
        } else {
            minai_bridge_log("Failed to get response from JavaScript backend");
        }
    }
}

} // End include guard
?>