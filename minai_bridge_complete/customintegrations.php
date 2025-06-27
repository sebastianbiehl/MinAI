<?php
/**
 * MinAI Complete Bridge - Custom Integrations
 * 
 * This handles direct MinAI requests (minai_narrator_talk, minai_dungeon_master, etc.)
 */

// Prevent multiple inclusions
if (!defined('MINAI_BRIDGE_CUSTOMINTEGRATIONS_LOADED')) {
    define('MINAI_BRIDGE_CUSTOMINTEGRATIONS_LOADED', true);

// Ensure prerequest was loaded
if (!isset($GLOBALS['MINAI_BRIDGE_CONFIG'])) {
    require_once(__DIR__ . '/prerequest.php');
}

// Handle direct MinAI requests
if (isset($GLOBALS['gameRequest']) && is_array($GLOBALS['gameRequest'])) {
    $gameRequest = $GLOBALS['gameRequest'];
    $requestType = $gameRequest[0] ?? '';
    
    // List of direct MinAI request types that should be handled here
    $directMinaiTypes = [
        'minai_narrator_talk',
        'minai_dungeon_master', 
        'minai_translate',
        'minai_roleplay',
        'minai_narrator',
        'minai_diary',
        'minai_diary_player',
        'minai_combatendvictory',
        'minai_bleedoutself',
        'minai_updateprofile',
        'minai_updateprofile_player',
        'minai_force_rechat'
    ];
    
    if (in_array($requestType, $directMinaiTypes)) {
        minai_bridge_log("Handling direct MinAI request: {$requestType}");
        
        // Skip if backend is not running
        if (!isset($GLOBALS['MINAI_BRIDGE_BACKEND_RUNNING']) || !$GLOBALS['MINAI_BRIDGE_BACKEND_RUNNING']) {
            minai_bridge_log("Backend not running, skipping MinAI request: {$requestType}");
        } else {
            $config = $GLOBALS['MINAI_BRIDGE_CONFIG'];
            
            // Forward to JavaScript backend
            $response = minai_bridge_forward_request($gameRequest, $config['backend_url'], $config['timeout']);
            
            if ($response && isset($response['dialogue'])) {
                minai_bridge_log("Got response from backend: " . substr($response['dialogue'], 0, 100) . "...");
                
                // Apply response based on the action type
                switch ($response['action']) {
                    case 'narrator_thought':
                    case 'combat_narrator':
                        // Set narrator as the speaker
                        $GLOBALS['HERIKA_NAME'] = 'The Narrator';
                        $GLOBALS['target'] = 'The Narrator';
                        
                        // Set narrator voice if provided
                        if (isset($response['tts']['voice'])) {
                            $GLOBALS['TTS']['FORCED_VOICE_DEV'] = $response['tts']['voice'];
                            $GLOBALS['TTS']['MELOTTS']['voiceid'] = $response['tts']['voice'];
                        }
                        break;
                        
                    case 'translated_speech':
                        // Format as player speech
                        $playerName = $GLOBALS["PLAYER_NAME"] ?? 'Player';
                        $response['dialogue'] = $playerName . ": " . $response['dialogue'];
                        $GLOBALS["FORCED_TTS"] = true;
                        
                        // Set player voice if provided
                        if (isset($response['tts']['voice'])) {
                            $GLOBALS['TTS']['FORCED_VOICE_DEV'] = $response['tts']['voice'];
                            $GLOBALS['TTS']['MELOTTS']['voiceid'] = $response['tts']['voice'];
                        }
                        break;
                        
                    case 'dungeonmaster_event':
                        // Set as narrator for DM events
                        $GLOBALS['HERIKA_NAME'] = 'The Narrator';
                        
                        // Set narrator voice for DM events
                        $GLOBALS['TTS']['FORCED_VOICE_DEV'] = 'narrator';
                        $GLOBALS['TTS']['MELOTTS']['voiceid'] = 'narrator';
                        break;
                }
                
                // Override the game request with our response
                $GLOBALS["gameRequest"][3] = $response['dialogue'];
                
                // Set the request type to inputtext so HerikaServer processes it normally
                $GLOBALS["gameRequest"][0] = 'inputtext';
                
                minai_bridge_log("Applied MinAI response to game request");
            } else {
                minai_bridge_log("No valid response from backend for: {$requestType}");
            }
        }
    }
}

} // End include guard
?>