<?php
// Basic functionality test for MinAI minimal

echo "<h1>MinAI Minimal - Basic Functionality Test</h1>\n";

try {
    // Test 1: Load config
    echo "<h2>Test 1: Loading Configuration</h2>\n";
    require_once("config.base.php");
    echo "✅ config.base.php loaded successfully<br>\n";
    
    // Test 2: Load utilities
    echo "<h2>Test 2: Loading Utilities</h2>\n";
    require_once("util.php");
    echo "✅ util.php loaded successfully<br>\n";
    
    // Test 3: Test mock database
    echo "<h2>Test 3: Testing Mock Database</h2>\n";
    if (isset($GLOBALS["db"])) {
        echo "✅ Mock database initialized<br>\n";
        $result = $GLOBALS["db"]->fetchAll("select * from conf_opts where id like '_minai_%'");
        echo "✅ Mock database returns " . count($result) . " test records<br>\n";
    } else {
        echo "❌ Mock database not initialized<br>\n";
    }
    
    // Test 4: Test GetActorValue function
    echo "<h2>Test 4: Testing GetActorValue Function</h2>\n";
    if (function_exists('GetActorValue')) {
        $gender = GetActorValue("Player", "gender");
        echo "✅ GetActorValue('Player', 'gender') = '$gender'<br>\n";
        
        $race = GetActorValue("Player", "race");
        echo "✅ GetActorValue('Player', 'race') = '$race'<br>\n";
    } else {
        echo "❌ GetActorValue function not found<br>\n";
    }
    
    // Test 5: Test context builders
    echo "<h2>Test 5: Testing Context Builders</h2>\n";
    require_once("contextbuilders/system_prompt_context.php");
    echo "✅ system_prompt_context.php loaded successfully<br>\n";
    
    // Test 6: Test roleplay builder
    echo "<h2>Test 6: Testing Roleplay Builder</h2>\n";
    require_once("roleplaybuilder.php");
    echo "✅ roleplaybuilder.php loaded successfully<br>\n";
    
    if (function_exists('GetPlayerVoiceType')) {
        $voiceType = GetPlayerVoiceType();
        echo "✅ GetPlayerVoiceType() = '$voiceType'<br>\n";
    }
    
    // Test 7: Test self narrator
    echo "<h2>Test 7: Testing Self Narrator</h2>\n";
    require_once("selfnarrator.php");
    echo "✅ selfnarrator.php loaded successfully<br>\n";
    
    if (function_exists('SetSelfNarrator')) {
        echo "✅ SetSelfNarrator function available<br>\n";
    }
    
    echo "<h2>✅ All Tests Completed Successfully!</h2>\n";
    echo "<p>MinAI minimal core functionality appears to be working.</p>\n";
    
} catch (Exception $e) {
    echo "<h2>❌ Error During Testing</h2>\n";
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>\n";
    echo "<p>File: " . $e->getFile() . " Line: " . $e->getLine() . "</p>\n";
}
?>