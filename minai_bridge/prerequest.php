<?php
/**
 * MinAI Bridge - Pre-request Processing
 * 
 * This file ensures the JavaScript backend is running and handles initialization
 */

// Prevent multiple inclusions
if (!defined('MINAI_BRIDGE_PREREQUEST_LOADED')) {
    define('MINAI_BRIDGE_PREREQUEST_LOADED', true);

// Basic logging function
if (!function_exists('minai_bridge_log')) {
    function minai_bridge_log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logFile = __DIR__ . '/logs/bridge.log';
        $logDir = dirname($logFile);
        
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND | LOCK_EX);
    }
}

// Configuration
$MINAI_BRIDGE_CONFIG = [
    'backend_url' => 'http://localhost:8080',
    'backend_dir' => __DIR__ . '/../minai_js',
    'auto_start' => true,
    'timeout' => 5
];

// Check if JavaScript backend is running
if (!function_exists('minai_bridge_check_backend')) {
    function minai_bridge_check_backend($url, $timeout = 2) {
        $context = stream_context_create([
            'http' => [
                'timeout' => $timeout,
                'method' => 'GET'
            ]
        ]);
        
        $result = @file_get_contents($url . '/health', false, $context);
        return $result !== false;
    }
}

// Start JavaScript backend if needed
if (!function_exists('minai_bridge_start_backend')) {
    function minai_bridge_start_backend($backendDir) {
        if (!file_exists($backendDir)) {
            minai_bridge_log("Backend directory not found: $backendDir");
            return false;
        }
        
        $nodeCommand = 'node';
        $npmCommand = 'npm';
        
        // Check if Node.js is available
        exec("which $nodeCommand 2>/dev/null", $output, $returnCode);
        if ($returnCode !== 0) {
            minai_bridge_log("Node.js not found. Please install Node.js from https://nodejs.org/");
            return false;
        }
        
        // Check if dependencies are installed
        if (!file_exists("$backendDir/node_modules")) {
            minai_bridge_log("Installing JavaScript dependencies...");
            exec("cd $backendDir && $npmCommand install 2>&1", $installOutput, $installCode);
            if ($installCode !== 0) {
                minai_bridge_log("Failed to install dependencies: " . implode("\n", $installOutput));
                return false;
            }
        }
        
        // Start the server in background
        $command = "cd $backendDir && $nodeCommand server.js > logs/server.log 2>&1 & echo $!";
        $pidOutput = shell_exec($command);
        $pid = trim($pidOutput);
        
        if ($pid) {
            // Save PID for later cleanup
            file_put_contents(__DIR__ . '/backend.pid', $pid);
            minai_bridge_log("Started JavaScript backend with PID: $pid");
            
            // Wait a moment for startup
            sleep(2);
            return true;
        }
        
        minai_bridge_log("Failed to start JavaScript backend");
        return false;
    }
}

// Initialize backend
$backendRunning = minai_bridge_check_backend($MINAI_BRIDGE_CONFIG['backend_url'], 1);

if (!$backendRunning && $MINAI_BRIDGE_CONFIG['auto_start']) {
    minai_bridge_log("JavaScript backend not running, attempting to start...");
    
    if (minai_bridge_start_backend($MINAI_BRIDGE_CONFIG['backend_dir'])) {
        // Check again after startup attempt
        sleep(1);
        $backendRunning = minai_bridge_check_backend($MINAI_BRIDGE_CONFIG['backend_url'], 2);
        
        if ($backendRunning) {
            minai_bridge_log("JavaScript backend started successfully");
        } else {
            minai_bridge_log("JavaScript backend failed to start properly");
        }
    }
}

// Store backend status in globals for other files
$GLOBALS['MINAI_BRIDGE_BACKEND_RUNNING'] = $backendRunning;
$GLOBALS['MINAI_BRIDGE_CONFIG'] = $MINAI_BRIDGE_CONFIG;

if ($backendRunning) {
    minai_bridge_log("MinAI Bridge initialized - JavaScript backend is running");
} else {
    minai_bridge_log("MinAI Bridge initialized - WARNING: JavaScript backend is not running");
}

} // End include guard
?>