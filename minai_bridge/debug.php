<?php
/**
 * MinAI Bridge Debug Script
 * Run this to diagnose backend startup issues
 */

echo "<h1>MinAI Bridge Debug Information</h1>\n";
echo "<pre>\n";

// Check basic environment
echo "=== Environment Check ===\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Current User: " . get_current_user() . "\n";
echo "Current Directory: " . getcwd() . "\n";
echo "Bridge Directory: " . __DIR__ . "\n";

// Check Node.js availability
echo "\n=== Node.js Check ===\n";
$nodeCheck = shell_exec('which node 2>&1');
echo "Node.js Path: " . ($nodeCheck ? trim($nodeCheck) : "NOT FOUND") . "\n";

if ($nodeCheck) {
    $nodeVersion = shell_exec('node --version 2>&1');
    echo "Node.js Version: " . trim($nodeVersion) . "\n";
}

$npmCheck = shell_exec('which npm 2>&1');
echo "npm Path: " . ($npmCheck ? trim($npmCheck) : "NOT FOUND") . "\n";

if ($npmCheck) {
    $npmVersion = shell_exec('npm --version 2>&1');
    echo "npm Version: " . trim($npmVersion) . "\n";
}

// Check JavaScript backend directory
echo "\n=== JavaScript Backend Check ===\n";
$jsBackendDir = __DIR__ . '/../minai_js';
echo "JavaScript Backend Directory: $jsBackendDir\n";
echo "Directory Exists: " . (file_exists($jsBackendDir) ? "YES" : "NO") . "\n";

if (file_exists($jsBackendDir)) {
    echo "Contents:\n";
    $contents = scandir($jsBackendDir);
    foreach ($contents as $item) {
        if ($item !== '.' && $item !== '..') {
            echo "  - $item\n";
        }
    }
    
    echo "\nKey Files:\n";
    $keyFiles = ['package.json', 'server.js', 'node_modules'];
    foreach ($keyFiles as $file) {
        $path = "$jsBackendDir/$file";
        echo "  - $file: " . (file_exists($path) ? "EXISTS" : "MISSING") . "\n";
    }
}

// Check if backend is running
echo "\n=== Backend Status Check ===\n";
$backendUrl = 'http://localhost:8080/health';
echo "Checking: $backendUrl\n";

$context = stream_context_create([
    'http' => [
        'timeout' => 3,
        'method' => 'GET'
    ]
]);

$result = @file_get_contents($backendUrl, false, $context);
echo "Backend Running: " . ($result ? "YES" : "NO") . "\n";

if ($result) {
    echo "Response: $result\n";
} else {
    echo "Connection failed - backend is not running\n";
}

// Check logs
echo "\n=== Log Files ===\n";
$bridgeLogFile = __DIR__ . '/logs/bridge.log';
echo "Bridge Log File: $bridgeLogFile\n";
echo "Bridge Log Exists: " . (file_exists($bridgeLogFile) ? "YES" : "NO") . "\n";

if (file_exists($bridgeLogFile)) {
    echo "Bridge Log Content (last 10 lines):\n";
    $logLines = file($bridgeLogFile, FILE_IGNORE_NEW_LINES);
    $recentLines = array_slice($logLines, -10);
    foreach ($recentLines as $line) {
        echo "  $line\n";
    }
}

$jsLogFile = "$jsBackendDir/logs/server.log";
echo "\nJavaScript Log File: $jsLogFile\n";
echo "JavaScript Log Exists: " . (file_exists($jsLogFile) ? "YES" : "NO") . "\n";

if (file_exists($jsLogFile)) {
    echo "JavaScript Log Content (last 10 lines):\n";
    $logLines = file($jsLogFile, FILE_IGNORE_NEW_LINES);
    $recentLines = array_slice($logLines, -10);
    foreach ($recentLines as $line) {
        echo "  $line\n";
    }
}

// Test manual startup
echo "\n=== Manual Startup Test ===\n";
if (file_exists($jsBackendDir) && $nodeCheck) {
    echo "Attempting to install dependencies...\n";
    $installOutput = shell_exec("cd $jsBackendDir && npm install 2>&1");
    echo "Install Output:\n$installOutput\n";
    
    echo "Attempting to start backend manually (will timeout after 5 seconds)...\n";
    $startCommand = "cd $jsBackendDir && timeout 5s node server.js 2>&1";
    $startOutput = shell_exec($startCommand);
    echo "Start Output:\n$startOutput\n";
} else {
    echo "Cannot test manual startup - missing requirements\n";
}

// Check permissions
echo "\n=== Permissions Check ===\n";
echo "Bridge Directory Writable: " . (is_writable(__DIR__) ? "YES" : "NO") . "\n";
if (file_exists($jsBackendDir)) {
    echo "JavaScript Directory Writable: " . (is_writable($jsBackendDir) ? "YES" : "NO") . "\n";
}

// Check processes
echo "\n=== Running Processes ===\n";
$processes = shell_exec("ps aux | grep -E '(node|npm)' | grep -v grep 2>/dev/null");
echo "Node.js processes:\n";
echo $processes ? $processes : "No Node.js processes found\n";

echo "</pre>\n";
?>

<style>
body { font-family: monospace; background: #1a1a1a; color: #e0e0e0; margin: 20px; }
pre { background: #2d2d2d; padding: 20px; border-radius: 8px; overflow-x: auto; }
h1 { color: #4CAF50; }
</style>