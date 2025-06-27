<?php
/**
 * MinAI Bridge Logs API
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $logFile = dirname(__DIR__) . '/logs/bridge.log';
    
    if (!file_exists($logFile)) {
        echo json_encode([
            'success' => true,
            'logs' => 'No logs available yet. The bridge will create logs when it processes requests.',
            'timestamp' => date('c')
        ]);
        exit;
    }
    
    // Read last 50 lines of log file
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $recentLines = array_slice($lines, -50);
    
    echo json_encode([
        'success' => true,
        'logs' => implode("\n", $recentLines),
        'total_lines' => count($lines),
        'showing_lines' => count($recentLines),
        'timestamp' => date('c')
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('c')
    ]);
}
?>