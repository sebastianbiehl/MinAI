<?php
/**
 * MinAI Complete Bridge - Main Functions Entry Point
 * 
 * This file is loaded by HerikaServer's prompt.includes.php and provides
 * the main entry point for the MinAI bridge functionality.
 */

// Ensure we only load once
if (!defined('MINAI_BRIDGE_COMPLETE_LOADED')) {
    define('MINAI_BRIDGE_COMPLETE_LOADED', true);
    
    // Load the bridge components
    require_once(__DIR__ . '/config.php');
    require_once(__DIR__ . '/prerequest.php');
    require_once(__DIR__ . '/customintegrations.php');
    require_once(__DIR__ . '/preprocessing.php');
}

// No additional functions needed here - the preprocessing.php handles the main logic
?>