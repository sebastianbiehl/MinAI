<?php
/**
 * MinAI Bridge - Post-request Processing
 * 
 * This file handles any cleanup or final processing after the response
 */

// Prevent multiple inclusions
if (!defined('MINAI_BRIDGE_POSTREQUEST_LOADED')) {
    define('MINAI_BRIDGE_POSTREQUEST_LOADED', true);

// Post-processing cleanup
if (isset($GLOBALS['MINAI_BRIDGE_CONFIG'])) {
    // Log the final response for debugging
    if (isset($GLOBALS['gameRequest'][3]) && !empty($GLOBALS['gameRequest'][3])) {
        $responseLength = strlen($GLOBALS['gameRequest'][3]);
        minai_bridge_log("Final response length: $responseLength characters");
    }
    
    // Any additional cleanup can go here
    minai_bridge_log("Request processing completed");
}

} // End include guard
?>