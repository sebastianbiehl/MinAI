<?php
// MinAI Clean Plugin - Preprocessing
// Main entry point for processing game requests

require_once("util.php");
require_once("roleplaybuilder.php");

/**
 * Main preprocessing function called by HerikaServer
 */
function preprocessRequest() {
    try {
        // Process roleplay/translation input
        interceptRoleplayInput();
        
        minai_log("info", "Preprocessing completed");
        
    } catch (Exception $e) {
        minai_log("error", "Error in preprocessing: " . $e->getMessage());
    }
}

// Auto-execute preprocessing
preprocessRequest();