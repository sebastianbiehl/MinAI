<?php
// MinAI Clean Plugin - Self Narrator Feature
// Transforms narrator responses into first-person inner voice

require_once("util.php");

/**
 * Set up the self narrator system
 */
function SetSelfNarrator() {
    $GLOBALS["self_narrator"] = true;
    minai_log("info", "Self narrator mode activated");
}

/**
 * Set up narrator prompts based on self narrator setting
 */
function SetNarratorPrompts($useSelfNarrator = false) {
    $player = $GLOBALS["PLAYER_NAME"];
    $pronouns = GetActorPronouns($player);
    $inCombat = IsEnabled($player, "inCombat");
    $isExplicit = IsExplicitScene();
    
    if ($useSelfNarrator) {
        minai_log("info", "Setting up first-person narrator prompts");
        
        if ($isExplicit) {
            $prompt = $GLOBALS['action_prompts']['self_narrator_explicit'];
        } else if ($inCombat) {
            $prompt = "Think to yourself about this intense combat situation. Express your immediate thoughts, fears, and tactical considerations as you fight. Keep it first-person and in the moment.";
        } else {
            $prompt = $GLOBALS['action_prompts']['self_narrator_normal'];
        }
        
        // Replace variables in the prompt
        $prompt = str_replace([
            "#player_name#",
            "#player_object#",
            "#player_possessive#"
        ], [
            $player,
            $pronouns['object'],
            $pronouns['possessive']
        ], $prompt);
        
        $GLOBALS["PROMPTS"]["minai_self_narrator"] = [
            "cue" => [$prompt]
        ];
        
    } else {
        minai_log("info", "Setting up third-person narrator prompts");
        
        if ($isExplicit) {
            $prompt = "Describe what {$player} is experiencing from an omniscient narrator perspective. Focus on the scene and {$pronouns['possessive']} reactions.";
        } else if ($inCombat) {
            $prompt = "Narrate the combat scene involving {$player}. Describe the action, danger, and {$pronouns['possessive']} state of mind from an omniscient perspective.";
        } else {
            $prompt = "Narrate the current scene involving {$player}. Describe what's happening and provide context as an omniscient storyteller.";
        }
        
        $GLOBALS["PROMPTS"]["minai_narrator"] = [
            "cue" => [$prompt]
        ];
    }
}