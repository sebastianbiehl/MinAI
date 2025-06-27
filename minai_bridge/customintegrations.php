<?php
/**
 * MinAI Bridge - Custom Integrations
 * 
 * Handles MinAI-specific request types that bypass normal processing
 */

// Ensure the bridge is loaded
if (!isset($GLOBALS['MINAI_BRIDGE_CONFIG'])) {
    require_once(__DIR__ . '/prerequest.php');
}

function ProcessIntegrations() {
    if (!isset($GLOBALS["gameRequest"])) {
        return;
    }
    
    $gameRequest = $GLOBALS["gameRequest"];
    $eventType = $gameRequest[0] ?? '';
    
    // Handle fast commands that should skip processing
    $fastCommands = [
        "minai_init", "storecontext", "registeraction", "updatethreadsdb",
        "storetattoodesc", "minai_storeitem", "minai_storeitem_batch",
        "minai_clearinventory", "addnpc", "_quest", "setconf", "request",
        "_speech", "infoloc", "infonpc", "infonpc_close", "infoaction",
        "status_msg", "delete_event", "itemfound", "_questdata", "_uquest",
        "location", "_questreset"
    ];
    
    if (in_array($eventType, $fastCommands)) {
        minai_bridge_log("Fast command detected: $eventType, setting skip flag");
        $GLOBALS["minai_skip_processing"] = true;
        
        // For certain commands, we still want to send a simple response
        if ($eventType === "minai_init") {
            minai_bridge_log("MinAI initialization request");
            // Just acknowledge the init
            die('X-CUSTOM-CLOSE');
        }
        
        return;
    }
    
    // Handle MinAI-specific requests that need special processing
    $minaiSpecificRequests = [
        "minai_diary", "minai_updateprofile", "minai_narrator_talk",
        "minai_dungeon_master", "minai_combatendvictory", "minai_bleedoutself"
    ];
    
    if (in_array($eventType, $minaiSpecificRequests)) {
        minai_bridge_log("MinAI-specific request detected: $eventType");
        
        // Check if backend is running
        if (!isset($GLOBALS['MINAI_BRIDGE_BACKEND_RUNNING']) || !$GLOBALS['MINAI_BRIDGE_BACKEND_RUNNING']) {
            minai_bridge_log("Backend not running, cannot process MinAI request");
            return;
        }
        
        $config = $GLOBALS['MINAI_BRIDGE_CONFIG'];
        
        // Forward directly to backend
        $response = minai_bridge_forward_request($gameRequest, $config['backend_url'], $config['timeout']);
        
        if ($response) {
            // Apply the response
            if (isset($response['dialogue'])) {
                $GLOBALS["gameRequest"][3] = $response['dialogue'];
                
                // Set appropriate speaker
                if (isset($response['target'])) {
                    $GLOBALS['HERIKA_NAME'] = $response['target'];
                }
                
                // Handle TTS
                if (isset($response['tts']['voice'])) {
                    $GLOBALS['TTS']['FORCED_VOICE_DEV'] = $response['tts']['voice'];
                    $GLOBALS['TTS']['MELOTTS']['voiceid'] = $response['tts']['voice'];
                }
                
                minai_bridge_log("Processed MinAI-specific request: $eventType");
            }
        } else {
            minai_bridge_log("Failed to process MinAI-specific request: $eventType");
        }
        
        return;
    }
    
    // Handle radiant conversations
    $radiantTypes = ["radiant", "radiantsearchinghostile", "radiantsearchingfriend", 
                     "radiantcombathostile", "radiantcombatfriend", "minai_force_rechat"];
    
    if (in_array(strtolower($eventType), $radiantTypes)) {
        minai_bridge_log("Radiant conversation detected: $eventType");
        
        // For radiant conversations, we can let the normal processing handle it
        // by ensuring it goes through the main preprocessing logic
        return;
    }
    
    // Handle player input types that might need processing
    $playerInputTypes = ["inputtext", "inputtext_s", "ginputtext", "ginputtext_s"];
    
    if (in_array($eventType, $playerInputTypes)) {
        // Let the main preprocessing handle player input
        minai_bridge_log("Player input detected: $eventType");
        return;
    }
}

// Process integrations
ProcessIntegrations();
?>