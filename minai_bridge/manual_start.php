<?php
/**
 * Manual Backend Starter
 * Use this if automatic startup isn't working
 */

echo "<h1>MinAI Bridge - Manual Backend Starter</h1>\n";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>Starting Backend...</h2>\n";
    echo "<pre>\n";
    
    $jsBackendDir = __DIR__ . '/../minai_js';
    
    if (!file_exists($jsBackendDir)) {
        echo "ERROR: JavaScript backend directory not found: $jsBackendDir\n";
        echo "Make sure you copied the minai_js directory to the correct location.\n";
        exit;
    }
    
    // Check Node.js
    $nodeCheck = shell_exec('which node 2>&1');
    if (!$nodeCheck) {
        echo "ERROR: Node.js not found. Please install Node.js from https://nodejs.org/\n";
        exit;
    }
    
    echo "✓ Node.js found: " . trim($nodeCheck) . "\n";
    
    // Install dependencies
    echo "\n1. Installing dependencies...\n";
    $installCmd = "cd $jsBackendDir && npm install 2>&1";
    $installOutput = shell_exec($installCmd);
    echo $installOutput . "\n";
    
    // Check if installation was successful
    if (!file_exists("$jsBackendDir/node_modules")) {
        echo "ERROR: npm install failed. Check the output above.\n";
        exit;
    }
    
    echo "✓ Dependencies installed successfully\n";
    
    // Kill any existing backend processes
    echo "\n2. Stopping any existing backend...\n";
    $killCmd = "pkill -f 'node.*server.js' 2>/dev/null || true";
    shell_exec($killCmd);
    echo "✓ Cleared existing processes\n";
    
    // Start the backend
    echo "\n3. Starting backend server...\n";
    $startCmd = "cd $jsBackendDir && nohup node server.js > logs/server.log 2>&1 & echo $!";
    $pid = shell_exec($startCmd);
    $pid = trim($pid);
    
    if ($pid) {
        file_put_contents(__DIR__ . '/backend.pid', $pid);
        echo "✓ Backend started with PID: $pid\n";
        
        // Wait a moment and check if it's running
        sleep(2);
        
        $healthCheck = @file_get_contents('http://localhost:8080/health');
        if ($healthCheck) {
            echo "✓ Backend is responding on port 8080\n";
            echo "✓ Health check response: $healthCheck\n";
            echo "\n🎉 SUCCESS! Backend is now running.\n";
            echo "\nYou can now:\n";
            echo "- Access configuration: http://localhost:8080/config\n";
            echo "- Check status in the bridge dashboard\n";
            echo "- Use MinAI features in Skyrim\n";
        } else {
            echo "⚠ Backend started but not responding yet. Check logs:\n";
            echo "- JavaScript logs: $jsBackendDir/logs/server.log\n";
            echo "- Bridge logs: " . __DIR__ . "/logs/bridge.log\n";
        }
    } else {
        echo "ERROR: Failed to start backend. Check permissions and try again.\n";
    }
    
    echo "</pre>\n";
} else {
    // Show the form
    ?>
    <style>
        body { font-family: Arial, sans-serif; background: #1a1a1a; color: #e0e0e0; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: #2d2d2d; padding: 30px; border-radius: 10px; }
        .btn { background: #4CAF50; color: white; padding: 15px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        .btn:hover { background: #45a049; }
        pre { background: #1e1e1e; padding: 15px; border-radius: 5px; }
        .warning { background: #ff9800; color: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
    </style>
    
    <div class="container">
        <h1>🛠️ Manual Backend Starter</h1>
        <p>Use this if the automatic backend startup isn't working.</p>
        
        <div class="warning">
            <strong>⚠️ Before using this:</strong>
            <ul>
                <li>Make sure Node.js is installed on your server</li>
                <li>Ensure the minai_js directory is in the correct location</li>
                <li>Check that port 8080 is available</li>
            </ul>
        </div>
        
        <h3>What this will do:</h3>
        <ol>
            <li>Install JavaScript dependencies (npm install)</li>
            <li>Stop any existing backend processes</li>
            <li>Start the MinAI JavaScript backend</li>
            <li>Verify it's running properly</li>
        </ol>
        
        <form method="POST">
            <button type="submit" class="btn">🚀 Start Backend Manually</button>
        </form>
        
        <h3>Alternative: Command Line</h3>
        <p>You can also start the backend manually from the command line:</p>
        <pre>cd /var/www/html/HerikaServer/ext/minai_js
npm install
npm start</pre>
        
        <h3>Troubleshooting</h3>
        <p>If this doesn't work, try:</p>
        <ul>
            <li><strong>Debug info:</strong> <a href="debug.php" style="color: #4CAF50;">debug.php</a></li>
            <li><strong>Check logs:</strong> Look in minai_bridge/logs/ and minai_js/logs/</li>
            <li><strong>Port conflicts:</strong> Make sure nothing else is using port 8080</li>
            <li><strong>Permissions:</strong> Ensure PHP can execute shell commands</li>
        </ul>
    </div>
    <?php
}
?>