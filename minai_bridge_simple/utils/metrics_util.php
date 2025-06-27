<?php
// Simple metrics stub for compatibility

if (!function_exists('minai_start_timer')) {
    function minai_start_timer($name, $parent = null) {
        // Simple stub
    }
}

if (!function_exists('minai_stop_timer')) {
    function minai_stop_timer($name) {
        // Simple stub
    }
}

if (!function_exists('minai_log')) {
    function minai_log($level, $message) {
        error_log("MinAI [$level]: $message");
    }
}
?>