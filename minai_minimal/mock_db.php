<?php
/**
 * Mock database interface for MinAI minimal
 * Provides stub functions to prevent database errors
 */

class MockDatabase {
    private $mockData = array();
    
    public function __construct() {
        // Initialize some basic mock data for essential functions
        $this->mockData = array(
            'default_settings' => array(
                // Player settings
                array('id' => '_minai_player//gender', 'value' => 'female'),
                array('id' => '_minai_player//race', 'value' => 'nord'),
                array('id' => '_minai_player//level', 'value' => '1'),
                array('id' => '_minai_player//incombat', 'value' => 'false'),
                array('id' => '_minai_player//vitals', 'value' => '100~100~100~100~100~100~0'),
                array('id' => '_minai_player//voice', 'value' => 'femaleeventoned'),
                
                // Herika/NPC settings  
                array('id' => '_minai_herika//gender', 'value' => 'female'),
                array('id' => '_minai_herika//race', 'value' => 'nord'),
                array('id' => '_minai_herika//voice', 'value' => 'femaleeventoned'),
                
                // The Narrator settings
                array('id' => '_minai_the narrator//gender', 'value' => 'neutral'),
                array('id' => '_minai_the narrator//voice', 'value' => 'dragon'),
                
                // Basic game state
                array('id' => '_minai_game//nearbyactors', 'value' => ''),
                array('id' => '_minai_game//location', 'value' => 'Whiterun'),
                array('id' => '_minai_game//weather', 'value' => 'clear')
            )
        );
    }
    
    public function escape($value) {
        return addslashes($value);
    }
    
    public function fetchAll($query) {
        // For actor value cache queries, return some basic data
        if (strpos($query, 'conf_opts') !== false && strpos($query, '_minai_') !== false) {
            return $this->mockData['default_settings'];
        }
        return array(); // Return empty array for other queries
    }
    
    public function query($query) {
        return true; // Always return success
    }
    
    public function execQuery($query) {
        return true; // Always return success
    }
}

// Initialize mock database if no real database exists
if (!isset($GLOBALS["db"]) || !$GLOBALS["db"]) {
    $GLOBALS["db"] = new MockDatabase();
}