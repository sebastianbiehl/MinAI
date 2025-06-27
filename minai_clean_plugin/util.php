<?php
// MinAI Clean Plugin - Core Utilities
// Essential functions for Self Narrator, Roleplay/Translation, and DungeonMaster

require_once("/var/www/html/HerikaServer/lib/data_functions.php");

// Actor value cache system
define("MINAI_ACTOR_VALUE_CACHE", "minai_actor_value_cache");
$GLOBALS[MINAI_ACTOR_VALUE_CACHE] = [];

// Logging function
function minai_log($level, $message) {
    error_log("[MinAI Clean] [$level] $message");
}

// Get actor value from cache or database
function GetActorValue($name, $key) {
    $name = strtolower($name);
    $key = strtolower($key);
    
    // Check cache first
    if (isset($GLOBALS[MINAI_ACTOR_VALUE_CACHE][$name][$key])) {
        return $GLOBALS[MINAI_ACTOR_VALUE_CACHE][$name][$key];
    }
    
    // Query database
    $id = "_minai_{$name}//{$key}";
    $query = "SELECT value FROM conf_opts WHERE id = ?";
    $result = $GLOBALS["db"]->fetch($query, [$id]);
    
    if ($result) {
        // Cache the result
        if (!isset($GLOBALS[MINAI_ACTOR_VALUE_CACHE][$name])) {
            $GLOBALS[MINAI_ACTOR_VALUE_CACHE][$name] = [];
        }
        $GLOBALS[MINAI_ACTOR_VALUE_CACHE][$name][$key] = $result['value'];
        return $result['value'];
    }
    
    return "";
}

// Set actor value in database and cache
function SetActorValue($name, $key, $value) {
    $name = strtolower($name);
    $key = strtolower($key);
    
    $id = "_minai_{$name}//{$key}";
    $query = "INSERT OR REPLACE INTO conf_opts (id, value) VALUES (?, ?)";
    $GLOBALS["db"]->query($query, [$id, $value]);
    
    // Update cache
    if (!isset($GLOBALS[MINAI_ACTOR_VALUE_CACHE][$name])) {
        $GLOBALS[MINAI_ACTOR_VALUE_CACHE][$name] = [];
    }
    $GLOBALS[MINAI_ACTOR_VALUE_CACHE][$name][$key] = $value;
}

// Check if feature is enabled for actor
function IsEnabled($name, $feature) {
    $value = GetActorValue($name, $feature);
    return strtolower($value) === "true";
}

// Enable/disable feature for actor
function SetEnabled($name, $feature, $enabled) {
    SetActorValue($name, $feature, $enabled ? "true" : "false");
}

// Check if input is from player
function isPlayerInput() {
    return isset($GLOBALS["gameRequest"]) && 
           isset($GLOBALS["gameRequest"][1]) && 
           $GLOBALS["gameRequest"][1] == $GLOBALS["PLAYER_NAME"];
}

// Get target actor
function GetTargetActor() {
    if (isset($GLOBALS["gameRequest"]) && isset($GLOBALS["gameRequest"][2])) {
        return $GLOBALS["gameRequest"][2];
    }
    return $GLOBALS["HERIKA_NAME"];
}

// Check if actor is female
function IsFemale($name) {
    $gender = GetActorValue($name, "gender");
    return strtolower($gender) === "female";
}

// Get actor pronouns
function GetActorPronouns($name) {
    $isFemale = IsFemale($name);
    return [
        'subject' => $isFemale ? 'she' : 'he',
        'object' => $isFemale ? 'her' : 'him', 
        'possessive' => $isFemale ? 'her' : 'his'
    ];
}

// Check if scene is explicit
function IsExplicitScene() {
    // Basic implementation - can be expanded
    return IsEnabled($GLOBALS["PLAYER_NAME"], "isInSexScene") ||
           IsEnabled($GLOBALS["HERIKA_NAME"], "isInSexScene");
}

// Get cleaned message from request
function GetCleanedMessage() {
    if (isset($GLOBALS["gameRequest"][3])) {
        $message = $GLOBALS["gameRequest"][3];
        // Remove player name prefix if present
        $playerPrefix = $GLOBALS["PLAYER_NAME"] . ":";
        if (strpos($message, $playerPrefix) === 0) {
            $message = trim(substr($message, strlen($playerPrefix)));
        }
        return $message;
    }
    return "";
}

// Override game request prompt
function OverrideGameRequestPrompt($newPrompt) {
    $GLOBALS["gameRequest"][3] = $newPrompt;
}

// Replace variables in text
function replaceVariables($text, $variables) {
    foreach ($variables as $key => $value) {
        $text = str_replace("#{$key}#", $value, $text);
    }
    return $text;
}

// Basic context builder for equipment
function GetUnifiedEquipmentContext($actorName, $firstPerson = false) {
    $equipment = GetActorValue($actorName, "wornEquipment");
    if (empty($equipment)) {
        return "";
    }
    
    $prefix = $firstPerson ? "You are wearing" : "{$actorName} is wearing";
    return "{$prefix}: {$equipment}";
}

// Get recent conversation context
function GetRecentContext($actorName, $limit = 5) {
    // Simplified implementation - returns empty array
    // In full implementation, this would query conversation history
    return [];
}

// Simple LLM call function
function callLLM($messages, $model, $parameters = []) {
    // This would integrate with the HerikaServer LLM system
    // For now, return null to indicate no response
    return null;
}