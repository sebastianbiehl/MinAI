<?php
/**
 * MinAI Complete Bridge - Post-request Processing
 * 
 * This handles any cleanup needed after request processing
 */

// Simple post-processing - just log that we completed processing
if (isset($GLOBALS['MINAI_BRIDGE_DEBUG']) && $GLOBALS['MINAI_BRIDGE_DEBUG']) {
    if (function_exists('minai_bridge_log')) {
        minai_bridge_log("Post-request processing completed");
    }
}
?>