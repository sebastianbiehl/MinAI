<?php
/**
 * MinAI Complete Bridge - Pre-request Processing
 * 
 * This handles initialization and backend startup
 */

// Prevent multiple inclusions
if (!defined('MINAI_BRIDGE_PREREQUEST_LOADED')) {
    define('MINAI_BRIDGE_PREREQUEST_LOADED', true);

// Load metrics utilities
require_once(__DIR__ . '/utils/metrics_util.php');

// Ensure config is loaded
if (!isset($GLOBALS['MINAI_BRIDGE_CONFIG'])) {
    require_once(__DIR__ . '/config.php');
}

// Logging function
if (!function_exists('minai_bridge_log')) {
    function minai_bridge_log($message) {
        if ($GLOBALS['MINAI_BRIDGE_DEBUG']) {
            $timestamp = date('Y-m-d H:i:s');
            $logMessage = "[{$timestamp}] MinAI Bridge: {$message}\n";
            error_log($logMessage, 3, $GLOBALS['MINAI_BRIDGE_CONFIG']['log_file']);
        }
    }
}

// Check if backend is running
if (!function_exists('minai_bridge_check_backend')) {
    function minai_bridge_check_backend($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . '/health');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode === 200;
    }
}

// Start backend server
if (!function_exists('minai_bridge_start_backend')) {
    function minai_bridge_start_backend($scriptPath) {
        if (!file_exists($scriptPath)) {
            minai_bridge_log("Backend script not found: {$scriptPath}");
            return false;
        }
        
        minai_bridge_log("Starting backend server: {$scriptPath}");
        
        // Start the Node.js server in background
        $command = "cd " . escapeshellarg(dirname($scriptPath)) . " && node " . escapeshellarg(basename($scriptPath)) . " > /dev/null 2>&1 &";
        $result = shell_exec($command);
        
        // Wait a moment for server to start
        sleep(2);
        
        return true;
    }
}

// Forward request to backend
if (!function_exists('minai_bridge_forward_request')) {
    function minai_bridge_forward_request($gameRequest, $url, $timeout = 30) {
        $ch = curl_init();
        
        $requestData = [
            'type' => $gameRequest[0],
            'speaker' => $gameRequest[1] ?? '',
            'target' => $gameRequest[2] ?? '',
            'message' => $gameRequest[3] ?? '',
            'extra' => $gameRequest[4] ?? ''
        ];
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($requestData))
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_error($ch)) {
            minai_bridge_log("cURL error: " . curl_error($ch));
            curl_close($ch);
            return null;
        }
        
        curl_close($ch);
        
        if ($httpCode !== 200) {
            minai_bridge_log("HTTP error {$httpCode}: {$response}");
            return null;
        }
        
        $decodedResponse = json_decode($response, true);
        if (!$decodedResponse) {
            minai_bridge_log("Invalid JSON response: {$response}");
            return null;
        }
        
        return $decodedResponse;
    }
}

// Initialize backend on first load
$config = $GLOBALS['MINAI_BRIDGE_CONFIG'];

// Check if backend is already running
if (minai_bridge_check_backend($config['backend_url'])) {
    $GLOBALS['MINAI_BRIDGE_BACKEND_RUNNING'] = true;
    minai_bridge_log("Backend server is already running");
} else if ($config['auto_start_backend']) {
    // Try to start the backend
    if (minai_bridge_start_backend($config['backend_script'])) {
        // Wait and check if it started successfully
        sleep(3);
        if (minai_bridge_check_backend($config['backend_url'])) {
            $GLOBALS['MINAI_BRIDGE_BACKEND_RUNNING'] = true;
            minai_bridge_log("Backend server started successfully");
        } else {
            minai_bridge_log("Failed to start backend server");
        }
    }
} else {
    minai_bridge_log("Backend server not running and auto-start disabled");
}

} // End include guard
?>