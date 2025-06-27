<?php
// MinAI Clean Plugin - Custom Integrations
// Handles special game events and integrations

require_once("util.php");
require_once("dungeonmaster.php");
require_once("selfnarrator.php");

/**
 * Handle custom integration events
 */
function handleCustomIntegrations() {
    try {
        // Handle narrator talk events
        if (isset($GLOBALS["gameRequest"]) && $GLOBALS["gameRequest"][0] == "minai_narrator_talk") {
            minai_log("info", "Processing minai_narrator_talk event");
            
            SetEnabled($GLOBALS["PLAYER_NAME"], "isTalkingToNarrator", false);
            $GLOBALS["HERIKA_NAME"] = "The Narrator";
            
            // Set up narrator prompts with self narrator mode if enabled
            $useSelfNarrator = isset($GLOBALS["self_narrator"]) && $GLOBALS["self_narrator"];
            SetNarratorPrompts($useSelfNarrator);
        }
        
        // Handle dungeon master events
        if (isset($GLOBALS["gameRequest"]) && $GLOBALS["gameRequest"][0] == "minai_dungeon_master") {
            minai_log("info", "Processing minai_dungeon_master event");
            
            $requestData = isset($GLOBALS["gameRequest"][3]) ? $GLOBALS["gameRequest"][3] : "";
            ProcessDungeonMasterEvent($requestData);
        }
        
        // Handle roleplay events
        if (isset($GLOBALS["gameRequest"]) && $GLOBALS["gameRequest"][0] == "minai_roleplay") {
            minai_log("info", "Processing minai_roleplay event");
            // This will be handled by the roleplay system in preprocessing
        }
        
    } catch (Exception $e) {
        minai_log("error", "Error in custom integrations: " . $e->getMessage());
    }
}

// Auto-execute custom integrations
handleCustomIntegrations();