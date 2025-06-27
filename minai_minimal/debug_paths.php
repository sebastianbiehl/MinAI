<?php
// Debug script to check MinAI minimal paths and dependencies

echo "<h1>MinAI Minimal Path Debug</h1>\n";

echo "<h2>PHP Configuration</h2>\n";
echo "PHP Version: " . PHP_VERSION . "<br>\n";
echo "Current Working Directory: " . getcwd() . "<br>\n";
echo "Script Directory: " . __DIR__ . "<br>\n";

echo "<h2>Critical Files Check</h2>\n";

$criticalFiles = [
    'config.php',
    'config.base.php', 
    'util.php',
    'globals.php',
    'logger.php',
    'roleplaybuilder.php',
    'selfnarrator.php',
    'utils/llm_utils.php',
    'api/main.php',
    'api/simple_config.php'
];

foreach ($criticalFiles as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        echo "✅ $file exists<br>\n";
    } else {
        echo "❌ $file MISSING at $path<br>\n";
    }
}

echo "<h2>Directory Structure</h2>\n";
$dirs = ['api', 'utils', 'contextbuilders', 'functions', 'prompts'];
foreach ($dirs as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (is_dir($path)) {
        $count = count(glob($path . '/*'));
        echo "✅ $dir/ exists ($count files)<br>\n";
    } else {
        echo "❌ $dir/ MISSING<br>\n";
    }
}

echo "<h2>HerikaServer Integration</h2>\n";
$herikaPath = "/var/www/html/HerikaServer";
if (is_dir($herikaPath)) {
    echo "✅ HerikaServer found<br>\n";
    
    $confPath = "$herikaPath/conf/conf.php";
    if (file_exists($confPath)) {
        echo "✅ HerikaServer config found<br>\n";
    } else {
        echo "❌ HerikaServer config missing<br>\n";
    }
    
    $libPath = "$herikaPath/lib";
    if (is_dir($libPath)) {
        echo "✅ HerikaServer lib directory found<br>\n";
    } else {
        echo "❌ HerikaServer lib directory missing<br>\n";
    }
} else {
    echo "❌ HerikaServer not found at $herikaPath<br>\n";
}

echo "<h2>Permissions Check</h2>\n";
$logDir = "/var/www/html/HerikaServer/log";
if (is_dir($logDir)) {
    if (is_writable($logDir)) {
        echo "✅ Log directory writable<br>\n";
    } else {
        echo "❌ Log directory not writable<br>\n";
    }
} else {
    echo "❌ Log directory missing<br>\n";
}

echo "<h2>Include Path Test</h2>\n";
try {
    if (file_exists(__DIR__ . '/config.php')) {
        include_once(__DIR__ . '/config.php');
        echo "✅ config.php loaded successfully<br>\n";
    } else {
        echo "❌ config.php not found<br>\n";
    }
    
    if (file_exists(__DIR__ . '/util.php')) {
        include_once(__DIR__ . '/util.php');
        echo "✅ util.php loaded successfully<br>\n";
    } else {
        echo "❌ util.php not found<br>\n";
    }
} catch (Exception $e) {
    echo "❌ Error loading files: " . $e->getMessage() . "<br>\n";
}

echo "<h2>Globals Check</h2>\n";
if (isset($GLOBALS['roleplay_settings'])) {
    echo "✅ Roleplay settings loaded<br>\n";
} else {
    echo "❌ Roleplay settings missing<br>\n";
}

if (isset($GLOBALS['action_prompts'])) {
    echo "✅ Action prompts loaded<br>\n";
} else {
    echo "❌ Action prompts missing<br>\n";
}

echo "<h2>Function Check</h2>\n";
if (function_exists('minai_log')) {
    echo "✅ minai_log function available<br>\n";
} else {
    echo "❌ minai_log function missing<br>\n";
}

echo "<h2>Translation System Check</h2>\n";
if (file_exists(__DIR__ . '/roleplaybuilder.php')) {
    include_once(__DIR__ . '/roleplaybuilder.php');
    if (function_exists('interceptRoleplayInput')) {
        echo "✅ Translation function available<br>\n";
    } else {
        echo "❌ Translation function missing<br>\n";
    }
} else {
    echo "❌ roleplaybuilder.php missing<br>\n";
}

echo "<h2>Narrator System Check</h2>\n";
if (file_exists(__DIR__ . '/selfnarrator.php')) {
    include_once(__DIR__ . '/selfnarrator.php');
    if (function_exists('SetSelfNarrator')) {
        echo "✅ Narrator function available<br>\n";
    } else {
        echo "❌ Narrator function missing<br>\n";
    }
} else {
    echo "❌ selfnarrator.php missing<br>\n";
}

echo "<h2>Debug Complete</h2>\n";
echo "Check the results above to identify any issues.<br>\n";
?>