<?php
// Simple MinAI Bridge - Prerequest
// Just start the backend if it's not running

// Check if backend is running
function is_backend_running() {
    $context = stream_context_create([
        'http' => [
            'timeout' => 1,
            'method' => 'GET'
        ]
    ]);
    
    $result = @file_get_contents('http://localhost:8080/health', false, $context);
    return $result !== false;
}

// Start backend if needed
function start_backend() {
    $jsDir = __DIR__ . '/../minai_js';
    
    if (!file_exists($jsDir)) {
        error_log("MinAI Bridge: JavaScript backend directory not found: $jsDir");
        return false;
    }
    
    // Check if Node.js is available
    $nodeCheck = shell_exec('which node 2>&1');
    if (!$nodeCheck) {
        error_log("MinAI Bridge: Node.js not found");
        return false;
    }
    
    // Install dependencies if needed
    if (!file_exists("$jsDir/node_modules")) {
        error_log("MinAI Bridge: Installing dependencies...");
        shell_exec("cd $jsDir && npm install 2>&1");
    }
    
    // Start the backend
    $command = "cd $jsDir && nohup node server.js > logs/server.log 2>&1 & echo $!";
    $pid = shell_exec($command);
    
    if ($pid) {
        error_log("MinAI Bridge: Started backend with PID: " . trim($pid));
        sleep(2); // Wait for startup
        return true;
    }
    
    return false;
}

// Check if backend is running, start if not
if (!is_backend_running()) {
    error_log("MinAI Bridge: Backend not running, attempting to start...");
    start_backend();
    
    // Check again after startup attempt
    if (is_backend_running()) {
        error_log("MinAI Bridge: Backend started successfully");
    } else {
        error_log("MinAI Bridge: Failed to start backend");
    }
} else {
    error_log("MinAI Bridge: Backend already running");
}
?>