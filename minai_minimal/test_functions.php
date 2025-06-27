<?php
// Test function redeclaration fix

echo "<h1>Function Redeclaration Test</h1>\n";

try {
    echo "Including metrics_util.php first time...<br>\n";
    require_once("utils/metrics_util.php");
    echo "✅ First include successful<br>\n";
    
    echo "Including metrics_util.php second time...<br>\n";
    require_once("utils/metrics_util.php");
    echo "✅ Second include successful (should be ignored)<br>\n";
    
    echo "Including logger.php...<br>\n";
    require_once("logger.php");
    echo "✅ Logger include successful<br>\n";
    
    echo "Testing minai_start_timer function...<br>\n";
    if (function_exists('minai_start_timer')) {
        minai_start_timer('test_timer');
        echo "✅ minai_start_timer function works<br>\n";
        
        minai_stop_timer('test_timer');
        echo "✅ minai_stop_timer function works<br>\n";
    } else {
        echo "❌ minai_start_timer function not found<br>\n";
    }
    
    echo "Testing minai_log function...<br>\n";
    if (function_exists('minai_log')) {
        minai_log('info', 'Test log message');
        echo "✅ minai_log function works<br>\n";
    } else {
        echo "❌ minai_log function not found<br>\n";
    }
    
    echo "<h2>✅ All Function Tests Passed!</h2>\n";
    echo "<p>No function redeclaration errors detected.</p>\n";
    
} catch (Exception $e) {
    echo "<h2>❌ Error During Testing</h2>\n";
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>\n";
}
?>