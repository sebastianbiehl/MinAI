<?php
// MinAI Clean Plugin - Pre-Request Processing
// Handles special request types before main processing

require_once("util.php");
require_once("dungeonmaster.php");
require_once("selfnarrator.php");

/**
 * Handle special request types
 */
function handlePreRequest() {
    try {
        // Handle Narrator Talk events
        if (IsEnabled($GLOBALS["PLAYER_NAME"], "isTalkingToNarrator") && isPlayerInput()) {
            minai_log("info", "Processing narrator talk event");
            
            SetEnabled($GLOBALS["PLAYER_NAME"], "isTalkingToNarrator", false);
            $GLOBALS["HERIKA_NAME"] = "The Narrator";
            $GLOBALS["using_self_narrator"] = true;
            
            // Use self narrator mode if enabled
            if (isset($GLOBALS["self_narrator"]) && $GLOBALS["self_narrator"]) {
                $pronouns = GetActorPronouns($GLOBALS["PLAYER_NAME"]);
                $message = GetCleanedMessage();
                OverrideGameRequestPrompt($GLOBALS["PLAYER_NAME"] . " thinks to " . $pronouns["object"] . "self: " . $message);
            }
        }
        
        // Handle Dungeon Master events
        require_once("dungeonmaster.php");
        HandleDungeonMasterInput();
        
        minai_log("info", "Pre-request processing completed");
        
    } catch (Exception $e) {
        minai_log("error", "Error in pre-request processing: " . $e->getMessage());
    }
}

// Auto-execute pre-request handling
handlePreRequest();