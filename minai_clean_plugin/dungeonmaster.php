<?php
// MinAI Clean Plugin - DungeonMaster Feature
// Allows players to send direct prompts to NPCs

require_once("util.php");

/**
 * Set up DungeonMaster prompts
 * @param string $message The message from the dungeon master
 */
function SetDungeonMasterPrompts($message = "") {
    minai_log("info", "Setting up dungeon master prompts: {$message}");
    
    if (!empty($message)) {
        // For NPCs, use the standard prompt format
        $GLOBALS["PROMPTS"]["minai_dungeon_master"] = [
            "cue" => [
                "The Narrator: {$message}"
            ]
        ];
    } else {
        // Generic event trigger
        $GLOBALS["PROMPTS"]["minai_dungeon_master"] = [
            "cue" => [
                "The Narrator: Something significant has happened that you should respond to."
            ]
        ];
    }
}

/**
 * Process a dungeon master event
 * @param string $requestData The raw request data from the game
 */
function ProcessDungeonMasterEvent($requestData) {
    minai_log("info", "Processing dungeon master event: {$requestData}");
    
    // Check if the target is The Narrator
    if ($GLOBALS["HERIKA_NAME"] == "The Narrator") {
        minai_log("info", "Dungeon master event targeting The Narrator");
        
        // Set up narrator for self-narrator mode if enabled
        if (isset($GLOBALS['self_narrator']) && $GLOBALS['self_narrator']) {
            require_once("selfnarrator.php");
            SetNarratorPrompts(true);
        }
    }
    
    // Parse the message from the request data
    $message = "";
    
    // Check if this is a generic event trigger
    if (stripos($requestData, "The dungeon master has triggered an event") !== false) {
        minai_log("info", "Generic dungeon master event trigger detected");
        $message = "";
    }
    // Check specifically for "The dungeon master says:" prefix
    elseif (preg_match('/.*The dungeon master says:\s*(.*)$/i', $requestData, $matches)) {
        $message = trim($matches[1]);
        minai_log("info", "Extracted DM message: {$message}");
    }
    // Otherwise try the general pattern
    elseif (preg_match('/^.*?:\s*(.*)$/i', $requestData, $matches)) {
        $message = trim($matches[1]);
    } else {
        $message = $requestData;
    }

    // Set up the prompts with the extracted message
    SetDungeonMasterPrompts($message);
}

/**
 * Handle dungeon master input from the game
 */
function HandleDungeonMasterInput() {
    if (IsEnabled($GLOBALS["PLAYER_NAME"], "isDungeonMaster") && isPlayerInput()) {
        minai_log("info", "Processing dungeon master input");
        
        // Disable the flag
        SetEnabled($GLOBALS["PLAYER_NAME"], "isDungeonMaster", false);
        
        // Set up for processing
        $GLOBALS["minai_processing_input"] = true;
        $GLOBALS["gameRequest"][0] = "minai_dungeon_master";
        
        // Clean up the message format
        $message = $GLOBALS["gameRequest"][3];
        $playerPrefix = $GLOBALS["PLAYER_NAME"] . ":";
        if (strpos($message, $playerPrefix) === 0) {
            $message = trim(substr($message, strlen($playerPrefix)));
        }
        
        // Format as narrator message
        $GLOBALS["gameRequest"][3] = "The Narrator: " . $message;
        
        minai_log("info", "Formatted DM message: " . $GLOBALS["gameRequest"][3]);
    }
}