<?php
/**
 * MinAI Complete Bridge - Configuration
 */

// Prevent multiple inclusions
if (!defined('MINAI_BRIDGE_CONFIG_LOADED')) {
    define('MINAI_BRIDGE_CONFIG_LOADED', true);

    // Configuration for the bridge
    $GLOBALS['MINAI_BRIDGE_CONFIG'] = [
        'backend_url' => 'http://localhost:8080',
        'timeout' => 30,
        'log_file' => '/var/www/html/HerikaServer/log/minai_bridge.log',
        'auto_start_backend' => true,
        'backend_script' => __DIR__ . '/../minai_js/server.js'
    ];
    
    // Enable debug logging
    $GLOBALS['MINAI_BRIDGE_DEBUG'] = true;
    $GLOBALS['MINAI_BRIDGE_BACKEND_RUNNING'] = false;
}
?>