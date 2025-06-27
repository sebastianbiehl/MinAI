<?php
// MinAI Clean Plugin Configuration
// Core settings for Self Narrator, Roleplay/Translation, and DungeonMaster features

// Self Narrator Configuration
$GLOBALS['self_narrator'] = false;
$GLOBALS['use_narrator_profile'] = false;

// Core System Settings
$GLOBALS['disable_nsfw'] = false;
$GLOBALS['enable_prompt_slop_cleanup'] = false;
$GLOBALS['enforce_short_responses'] = false;

// Voice Configuration
$GLOBALS['voicetype_fallbacks'] = Array(
    "maleargonian" => "argonianmale", 
    "femaleargonian" => "argonianfemale",
    "malekhajiit" => "khajiitmale", 
    "femalekhajiit" => "khajiitfemale",
    "maleredguard" => "maleeventonedaccented", 
    "femaleredguard" => "femaleeventonedaccented",
    "malenord" => "malecondescending", 
    "femalenord" => "femalecondescending",
    "malebreton" => "malecommoner", 
    "femalebreton" => "femalecommoner",
    "maleimperial" => "maleeventoned", 
    "femaleimperial" => "femaleeventoned",
    "maleorc" => "maleorc", 
    "femaleorc" => "femaleorc",
    "malealtmer" => "maleelfhaughty", 
    "femalealtmer" => "femaleelfthaughty",
    "malehighelf" => "maleelfhaughty", 
    "femalehighelf" => "femaleelfthaughty",
    "maledunmer" => "maledarkelf", 
    "femaledunmer" => "femaledarkelf",
    "maledarkelf" => "maledarkelf", 
    "femaledarkelf" => "femaledarkelf",
    "malewoodelf" => "bosmermaleeventoned", 
    "femalewoodelf" => "bosmerfemaleeventoned"
);

// Action Prompts - Core Features Only
$GLOBALS['action_prompts'] = Array(
    "self_narrator_normal" => "Respond as #player_name#, thinking privately to #player_object#self about the current situation and recent events. Stay in first person, capturing #player_possessive# genuine thoughts, emotions, and internal conflicts. Focus on #player_possessive# personal perspective and feelings. Keep the response introspective and true to how #player_name# would think.",
    
    "self_narrator_explicit" => "Respond as #player_name# with immediate, first-person reactions to what's happening right now. Express genuine emotions and thoughts exactly as #player_name# would experience them in this moment. Keep it natural and direct.",
    
    "normal_scene" => "Your answer for #target# should reflect what #herika_name# would say in this situation. Express #herika_name#'s thoughts using vocabulary and speaking style that reflects #herika_name#'s personality. This response should feel authentic and progress the conversation naturally."
);

// Roleplay/Translation Settings - Simplified
$GLOBALS['roleplay_settings'] = Array(
    "context_messages" => 8,
    
    // Translation Mode Prompts
    "system_prompt" => "You are #PLAYER_NAME#. TRANSLATION MODE: Convert casual speech into how your character would say the same thing, preserving the original meaning.",
    "system_prompt_combat" => "You are #PLAYER_NAME# in combat. TRANSLATION MODE: Convert casual speech into urgent, combat-appropriate language while maintaining your character's style.",
    "translation_request" => "TRANSLATE this casual speech into your character's voice while keeping the same meaning: \"#ORIGINAL_INPUT#\"",
    "translation_request_combat" => "TRANSLATE this casual speech into tense, urgent language appropriate for combat while maintaining your character's style: \"#ORIGINAL_INPUT#\"",
    
    // Roleplay Mode Prompts
    "roleplay_system_prompt" => "You are #PLAYER_NAME#. ROLEPLAY MODE: Generate natural character responses based on your personality and the current situation.",
    "roleplay_system_prompt_combat" => "You are #PLAYER_NAME# in combat. ROLEPLAY MODE: Generate natural character responses appropriate for this urgent combat situation.",
    "roleplay_request" => "ROLEPLAY as #PLAYER_NAME#. Respond naturally as your character would in this situation with appropriate dialogue.",
    "roleplay_request_combat" => "ROLEPLAY as #PLAYER_NAME# in combat. Respond naturally with combat-appropriate dialogue as your character would.",
    
    // Context Sections - Simplified
    "sections" => Array(
        "CHARACTER_BACKGROUND" => Array(
            "enabled" => true,
            "header" => "## YOUR CHARACTER",
            "content" => "#PLAYER_BIOS#\n#HERIKA_PERS#\nPronouns: #PLAYER_SUBJECT#/#PLAYER_OBJECT#/#PLAYER_POSSESSIVE#\nCurrent State: #HERIKA_DYNAMIC#",
            "order" => 0
        ),
        "CURRENT_STATUS" => Array(
            "enabled" => true,
            "header" => "## CURRENT STATUS",
            "content" => "#VITALS#\n#CLOTHING_STATUS#",
            "order" => 1
        ),
        "ENVIRONMENT" => Array(
            "enabled" => true,
            "header" => "## ENVIRONMENT",
            "content" => "Characters: #NEARBY_ACTORS#\nLocations: #NEARBY_LOCATIONS#\nWeather: #WEATHER#",
            "order" => 2
        ),
        "RECENT_EVENTS" => Array(
            "enabled" => true,
            "header" => "## RECENT EVENTS",
            "content" => "#RECENT_EVENTS#",
            "order" => 3
        )
    )
);

// Context Builder Configuration - Essential Only
$GLOBALS['minai_context'] = array(
    // Character basics
    'personality' => true,
    'player_background' => true,
    'vitals' => true,
    'equipment' => true,
    'combat' => true,
    
    // Environmental basics
    'weather' => true,
    'location' => true,
    'nearby_characters' => true,
    
    // Core interaction
    'interaction' => true,
    'relationship' => true,
    
    // Everything else disabled by default
    'physical_description' => false,
    'tattoos' => false,
    'arousal' => false,
    'fertility' => false,
    'survival' => false,
    'bounty' => false,
    'mind_influence' => false,
    'nsfw_reputation' => false
);