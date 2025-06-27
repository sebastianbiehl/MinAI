<?php
echo "MinAI Bridge Diagnostics\n";
echo "========================\n\n";

// 1. Check current working directory
echo "1. Current working directory:\n";
echo "   " . getcwd() . "\n\n";

// 2. Check if bridge directory exists
$bridgeDir = __DIR__ . '/minai_bridge';
echo "2. Bridge directory check:\n";
echo "   Path: $bridgeDir\n";
echo "   Exists: " . (file_exists($bridgeDir) ? "YES" : "NO") . "\n\n";

// 3. Check if JavaScript directory exists
$jsDir = __DIR__ . '/minai_js';
echo "3. JavaScript directory check:\n";
echo "   Path: $jsDir\n";
echo "   Exists: " . (file_exists($jsDir) ? "YES" : "NO") . "\n\n";

// 4. Check Node.js from PHP
echo "4. Node.js availability from PHP:\n";
$whichNode = shell_exec('which node 2>&1');
echo "   which node: " . ($whichNode ? trim($whichNode) : "NOT FOUND") . "\n";

if ($whichNode) {
    $nodeVersion = shell_exec('node --version 2>&1');
    echo "   version: " . trim($nodeVersion) . "\n";
}

$whichNpm = shell_exec('which npm 2>&1');
echo "   which npm: " . ($whichNpm ? trim($whichNpm) : "NOT FOUND") . "\n\n";

// 5. Test backend connectivity
echo "5. Backend connectivity test:\n";
$healthUrl = 'http://localhost:8080/health';
$context = stream_context_create([
    'http' => [
        'timeout' => 3,
        'method' => 'GET'
    ]
]);

$result = @file_get_contents($healthUrl, false, $context);
echo "   URL: $healthUrl\n";
echo "   Response: " . ($result ? "SUCCESS" : "FAILED") . "\n";
if ($result) {
    echo "   Content: $result\n";
}
echo "\n";

// 6. Test manual startup
if (file_exists($jsDir) && $whichNode) {
    echo "6. Manual startup test:\n";
    echo "   Testing npm install...\n";
    
    $installCmd = "cd $jsDir && npm install 2>&1";
    $installOutput = shell_exec($installCmd);
    echo "   Install result: " . (strpos($installOutput, 'added') !== false ? "SUCCESS" : "CHECK OUTPUT") . "\n";
    
    // Check if node_modules exists
    $nodeModulesExists = file_exists("$jsDir/node_modules");
    echo "   node_modules exists: " . ($nodeModulesExists ? "YES" : "NO") . "\n";
    
    if ($nodeModulesExists) {
        echo "   Dependencies are ready\n";
    } else {
        echo "   Install output:\n";
        echo "   " . str_replace("\n", "\n   ", trim($installOutput)) . "\n";
    }
} else {
    echo "6. Manual startup test: SKIPPED (missing requirements)\n";
}

echo "\n";

// 7. Show exact paths the bridge is looking for
echo "7. Bridge configuration paths:\n";
$expectedJsDir = dirname(__FILE__) . '/minai_bridge/../minai_js';
$resolvedJsDir = realpath($expectedJsDir);
echo "   Bridge expects JS dir at: $expectedJsDir\n";
echo "   Resolves to: " . ($resolvedJsDir ? $resolvedJsDir : "INVALID PATH") . "\n";
echo "   Actual JS dir: " . realpath($jsDir) . "\n";
echo "   Paths match: " . ($resolvedJsDir === realpath($jsDir) ? "YES" : "NO") . "\n\n";

// 8. Recommendations
echo "8. Recommendations:\n";

if (!$result) {
    echo "   ⚠️  Backend is not running. Try:\n";
    echo "      cd " . realpath($jsDir) . "\n";
    echo "      npm install\n";
    echo "      npm start\n\n";
}

if (!$whichNode) {
    echo "   ❌ Node.js not found in PHP PATH. Install Node.js or fix PATH.\n\n";
}

if ($resolvedJsDir !== realpath($jsDir)) {
    echo "   ⚠️  Path mismatch. Bridge looking in wrong location.\n";
    echo "      Move minai_js to: " . dirname($expectedJsDir) . "\n\n";
}

echo "   ✅ To manually start backend:\n";
echo "      php " . __DIR__ . "/minai_bridge/manual_start.php\n\n";

echo "   📋 Installation paths for HerikaServer:\n";
echo "      cp -r minai_bridge /var/www/html/HerikaServer/ext/\n";
echo "      cp -r minai_js /var/www/html/HerikaServer/ext/\n";
?>