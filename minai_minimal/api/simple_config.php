<?php
// Simplified config API for MinAI minimal
require_once(__DIR__ . "/../config.base.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle configuration save
    $input = json_decode(file_get_contents('php://input'), true);
    
    if ($input) {
        try {
            // Update global configuration
            if (isset($input['self_narrator'])) {
                $GLOBALS['self_narrator'] = $input['self_narrator'];
            }
            
            if (isset($input['narrator_voice'])) {
                $GLOBALS['devious_narrator_eldritch_voice'] = $input['narrator_voice'];
            }
            
            if (isset($input['translation_enabled'])) {
                $GLOBALS['translation_enabled'] = $input['translation_enabled'];
            }
            
            if (isset($input['player_voice_model'])) {
                $GLOBALS['player_voice_model'] = $input['player_voice_model'];
            }
            
            if (isset($input['minai_enabled'])) {
                $GLOBALS['minai_enabled'] = $input['minai_enabled'];
            }
            
            if (isset($input['context_messages'])) {
                $GLOBALS['roleplay_settings']['context_messages'] = intval($input['context_messages']);
            }
            
            // Save configuration to file (simplified)
            $configData = [
                'self_narrator' => $GLOBALS['self_narrator'],
                'narrator_voice' => $GLOBALS['devious_narrator_eldritch_voice'],
                'translation_enabled' => isset($GLOBALS['translation_enabled']) ? $GLOBALS['translation_enabled'] : true,
                'player_voice_model' => $GLOBALS['player_voice_model'],
                'minai_enabled' => isset($GLOBALS['minai_enabled']) ? $GLOBALS['minai_enabled'] : true,
                'context_messages' => $GLOBALS['roleplay_settings']['context_messages']
            ];
            
            $configFile = __DIR__ . "/../user_config.json";
            file_put_contents($configFile, json_encode($configData, JSON_PRETTY_PRINT));
            
            echo json_encode(['success' => true, 'message' => 'Configuration saved successfully']);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error saving configuration: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    }
    
} else {
    // Handle configuration load
    try {
        $configFile = __DIR__ . "/../user_config.json";
        
        if (file_exists($configFile)) {
            $savedConfig = json_decode(file_get_contents($configFile), true);
        } else {
            $savedConfig = [];
        }
        
        // Return current configuration
        $config = [
            'self_narrator' => isset($savedConfig['self_narrator']) ? $savedConfig['self_narrator'] : $GLOBALS['self_narrator'],
            'narrator_voice' => isset($savedConfig['narrator_voice']) ? $savedConfig['narrator_voice'] : $GLOBALS['devious_narrator_eldritch_voice'],
            'translation_enabled' => isset($savedConfig['translation_enabled']) ? $savedConfig['translation_enabled'] : true,
            'player_voice_model' => isset($savedConfig['player_voice_model']) ? $savedConfig['player_voice_model'] : $GLOBALS['player_voice_model'],
            'minai_enabled' => isset($savedConfig['minai_enabled']) ? $savedConfig['minai_enabled'] : true,
            'context_messages' => isset($savedConfig['context_messages']) ? $savedConfig['context_messages'] : $GLOBALS['roleplay_settings']['context_messages']
        ];
        
        echo json_encode($config);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error loading configuration: ' . $e->getMessage()]);
    }
}
?>