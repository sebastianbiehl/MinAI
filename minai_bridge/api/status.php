<?php
/**
 * MinAI Bridge Status API
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $status = [
        'success' => true,
        'bridge_active' => defined('MINAI_BRIDGE_PREREQUEST_LOADED'),
        'backend_url' => 'http://localhost:8080',
        'timestamp' => date('c')
    ];
    
    // Check if backend is running
    $context = stream_context_create([
        'http' => [
            'timeout' => 2,
            'method' => 'GET'
        ]
    ]);
    
    $backendHealth = @file_get_contents('http://localhost:8080/health', false, $context);
    $status['backend_running'] = $backendHealth !== false;
    
    if ($backendHealth) {
        $healthData = json_decode($backendHealth, true);
        if ($healthData) {
            $status['backend_status'] = $healthData;
        }
    }
    
    // Check bridge components
    $bridgeDir = dirname(__DIR__);
    $status['files_present'] = [
        'prerequest' => file_exists("$bridgeDir/prerequest.php"),
        'preprocessing' => file_exists("$bridgeDir/preprocessing.php"),
        'postrequest' => file_exists("$bridgeDir/postrequest.php"),
        'manifest' => file_exists("$bridgeDir/manifest.json")
    ];
    
    // Check JavaScript backend directory
    $jsBackendDir = "$bridgeDir/../minai_js";
    $status['js_backend'] = [
        'directory_exists' => file_exists($jsBackendDir),
        'package_json_exists' => file_exists("$jsBackendDir/package.json"),
        'node_modules_exists' => file_exists("$jsBackendDir/node_modules"),
        'server_js_exists' => file_exists("$jsBackendDir/server.js")
    ];
    
    echo json_encode($status, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('c')
    ]);
}
?>