<?php
/**
 * Test the MinAI Bridge Integration
 */

echo "<h1>MinAI Bridge Integration Test</h1>\n";
echo "<pre>\n";

// Set up globals that HerikaServer would normally provide
$GLOBALS["PLAYER_NAME"] = "TestPlayer";
$GLOBALS["gameRequest"] = ["inputtext", "TestPlayer", "", "hey what's up dude", ""];

echo "=== Test Setup ===\n";
echo "Player Name: " . $GLOBALS["PLAYER_NAME"] . "\n";
echo "Request: " . json_encode($GLOBALS["gameRequest"]) . "\n\n";

// Load the bridge components
echo "=== Loading Bridge Components ===\n";

// Load prerequest
echo "Loading prerequest.php...\n";
require_once(__DIR__ . '/prerequest.php');

if (isset($GLOBALS['MINAI_BRIDGE_BACKEND_RUNNING'])) {
    echo "Backend status: " . ($GLOBALS['MINAI_BRIDGE_BACKEND_RUNNING'] ? "RUNNING" : "NOT RUNNING") . "\n";
} else {
    echo "Backend status: UNKNOWN\n";
}

// Load custom integrations
echo "Loading customintegrations.php...\n";
require_once(__DIR__ . '/customintegrations.php');

// Load preprocessing
echo "Loading preprocessing.php...\n";
require_once(__DIR__ . '/preprocessing.php');

echo "\n=== Results ===\n";
echo "Final gameRequest: " . json_encode($GLOBALS["gameRequest"]) . "\n";

if (isset($GLOBALS['HERIKA_NAME'])) {
    echo "Speaker set to: " . $GLOBALS['HERIKA_NAME'] . "\n";
}

if (isset($GLOBALS['TTS']['FORCED_VOICE_DEV'])) {
    echo "Voice set to: " . $GLOBALS['TTS']['FORCED_VOICE_DEV'] . "\n";
}

// Test with different request types
echo "\n=== Testing Different Request Types ===\n";

$testRequests = [
    ["minai_translate", "TestPlayer", "", "lol this is awesome", ""],
    ["minai_dungeon_master", "DM", "all", "A dragon appears", ""],
    ["inputtext", "TestPlayer", "The Narrator", "I'm thinking about my adventure", ""]
];

foreach ($testRequests as $testRequest) {
    echo "\nTesting: " . json_encode($testRequest) . "\n";
    
    // Reset globals
    $GLOBALS["gameRequest"] = $testRequest;
    unset($GLOBALS['HERIKA_NAME']);
    unset($GLOBALS['TTS']['FORCED_VOICE_DEV']);
    
    // Test the bridge logic
    if (function_exists('minai_bridge_should_handle_request')) {
        $shouldHandle = minai_bridge_should_handle_request($testRequest);
        echo "Should handle: " . ($shouldHandle ? "YES" : "NO") . "\n";
        
        if ($shouldHandle && function_exists('minai_bridge_convert_request')) {
            $converted = minai_bridge_convert_request($testRequest);
            echo "Converted to: " . json_encode($converted) . "\n";
        }
    }
}

echo "\n=== Logs ===\n";
$logFile = __DIR__ . '/logs/bridge.log';
if (file_exists($logFile)) {
    $logs = file($logFile, FILE_IGNORE_NEW_LINES);
    $recentLogs = array_slice($logs, -10);
    foreach ($recentLogs as $log) {
        echo $log . "\n";
    }
} else {
    echo "No logs found.\n";
}

echo "</pre>\n";
?>

<style>
body { font-family: monospace; background: #1a1a1a; color: #e0e0e0; margin: 20px; }
pre { background: #2d2d2d; padding: 20px; border-radius: 8px; overflow-x: auto; }
h1 { color: #4CAF50; }
</style>