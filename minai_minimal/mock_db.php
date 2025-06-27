<?php
/**
 * Mock database interface for MinAI minimal
 * Provides stub functions to prevent database errors
 */

class MockDatabase {
    public function escape($value) {
        return addslashes($value);
    }
    
    public function fetchAll($query) {
        return array(); // Return empty array for all queries
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