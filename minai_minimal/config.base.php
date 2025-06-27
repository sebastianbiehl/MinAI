<?php
$GLOBALS['PROMPT_HEAD_OVERRIDE'] = "";
$GLOBALS['use_narrator_profile'] = false;
$GLOBALS['stop_narrator_context_leak'] = true;
$GLOBALS['devious_narrator_eldritch_voice'] = "dragon";
$GLOBALS['devious_narrator_telvanni_voice'] = "TelvanniNarrator";
$GLOBALS['self_narrator'] = false;
$GLOBALS['force_voice_type'] = false;
$GLOBALS['disable_nsfw'] = true;
$GLOBALS['restrict_nonfollower_functions'] = true;
$GLOBALS['always_enable_functions'] = true;
$GLOBALS['force_aiff_name_to_ingame_name'] = true;
$GLOBALS['enable_prompt_slop_cleanup'] = false;
$GLOBALS['commands_to_purge'] = Array("TakeASeat", "Folow");
$GLOBALS['events_to_ignore'] = Array("rpg_lvlup");
$GLOBALS['use_defeat'] = false;
$GLOBALS["realnames_support"] = false;
$GLOBALS['disable_worn_equipment'] = false;
$GLOBALS['radiance_rechat_h'] = 8;
$GLOBALS['radiance_rechat_p'] = 20;
$GLOBALS['xtts_server_override'] = "";
$GLOBALS['strip_emotes_from_output'] = false;
$GLOBALS['input_delay_for_radiance'] = 15;
$GLOBALS['voicetype_fallbacks'] = Array("maleargonian" => "argonianmale", "femaleargonian" => "argonianfemale", "malekhajiit" => "khajiitmale", "femalekhajiit" => "khajiitfemale", "maleredguard" => "maleeventonedaccented", "femaleredguard" => "femaleeventonedaccented", "malenord" => "malecondescending", "femalenord" => "femalecondescending", "malebreton" => "malecommoner", "femalebreton" => "femalecommoner", "maleimperial" => "maleeventoned", "femaleimperial" => "femaleeventoned", "maleorc" => "maleorc", "femaleorc" => "femaleorc", "malealtmer" => "maleelfhaughty", "femalealtmer" => "femaleelfthaughty", "malehighelf" => "maleelfhaughty", "femalehighelf" => "femaleelfthaughty", "maledunmer" => "maledarkelf", "femaledunmer" => "femaledarkelf", "maledarkelf" => "maledarkelf", "femaledarkelf" => "femaledarkelf", "maleoldpeoplerace" => "maleoldkindly", "femaleoldpeoplerace" => "femaleoldkindly", "malewoodelf" => "bosmermaleeventoned", "femalewoodelf" => "bosmerfemaleeventoned");
$GLOBALS['enforce_short_responses'] = false;
$GLOBALS['use_llm_fallback'] = false;
$GLOBALS['enforce_single_json'] = false;
$GLOBALS['CHIM_NO_EXAMPLES'] = false;

// Context Builder Configuration - Minimal setup for narrator and translation only
$GLOBALS['minai_context'] = array(
    // Basic character context
    'physical_description' => false,
    'equipment' => false,
    'tattoos' => false,
    'arousal' => false,
    'fertility' => false,
    'following' => false,
    'survival' => false,
    'player_status' => true,
    'bounty' => false,
    'mind_influence' => false,
    'dynamic_state' => true,
    'career' => false,
    'dirt_and_blood' => false,
    'level' => false,
    'family_status' => false,
    'party_membership' => false,
    'combat' => false,
    'vitals' => false,

    // Core context builders (essential for character)
    'personality' => true,
    'interaction' => true,
    'player_background' => true,
    'current_task' => false,
    
    // Environmental context builders (minimal)
    'day_night_state' => false,
    'weather' => false,
    'moon_phase' => false,
    'location' => true,
    'frostfall' => false,
    'character_state' => false,
    'nearby_characters' => false,
    'npc_relationships' => false,
    'third_party' => false,
    'nearby_buildings' => false,
    
    // Relationship context builders (disabled)
    'relationship' => false,
    'relative_power' => false,
    'devious_follower' => false,
    'submissive_lola' => false,
    'devious_narrator' => false,
    
    // NSFW context builders (disabled)
    'nsfw_reputation' => false,
    
    // System prompt settings
    'response_guidelines' => true,
    'action_enforcement' => false
);

// Inventory system configuration
$GLOBALS['inventory_items_limit'] = 5; // Number of Items to expose to LLM from an actor's inventory
$GLOBALS['use_item_relevancy_scoring'] = false; // Use relevancy scoring for items

// Minimal action prompts - only narrator and basic dialogue
$GLOBALS['action_prompts'] = Array(
    "self_narrator_normal" => "Respond as #player_name#, thinking privately to #player_object#self about the current situation and recent events. Stay in first person, capturing #player_possessive# genuine thoughts, emotions, and internal conflicts. Focus on #player_possessive# personal perspective, biases, and feelings rather than an objective summary of events. Keep the response introspective and true to how #player_name# would process and react internally.",

    "normal_scene" => "Your answer for #target# should reflect what #herika_name# would say in this situation. Express #herika_name#'s own thoughts, use vocabulary and speaking style that reflects #herika_name#'s personality. This response should feel authentic and progress the scene or conversation naturally. Review dialogue history to be able to avoid repeating or reformulating sentences from previous dialog lines.",

    // Diary prompts (keeping these as they're useful for character development)
    "player_diary" => "#player_name# regularly keeps a diary, which you are now tasked to update. Please write a several page story of #player_name#'s recent thoughts, feelings, and adventures. Respond as if you are writing this into a private diary, including completely unfiltered thoughts and feelings. WRITE AS IF YOU ARE #player_name# WRITING INTO A PRIVATE DIARY.",
    
    "follower_diary" => "#herika_name# regularly keeps a diary, which you are now tasked to update. Please write a several page story of #herika_name#'s  recent thoughts, feelings, and adventures. Respond as if you are writing this into a private diary, including completely unfiltered thoughts and feelings. WRITE AS IF YOU ARE #herika_name# WRITING INTO A PRIVATE DIARY."
);


// Roleplay translation settings
// TRANSLATION MODE: Converts casual player input into character-appropriate speech while preserving the original meaning
// ROLEPLAY MODE: Generates new character responses based on the current situation and conversation context
$GLOBALS['roleplay_settings'] = Array(
    "context_messages" => 10,
    
    // TRANSLATION MODE PROMPTS - Convert casual input to character voice
    "system_prompt" => "You are #PLAYER_NAME#. TRANSLATION MODE: Convert casual speech into how your character would say the same thing.",
    "system_prompt_explicit" => "You are #PLAYER_NAME# in an intimate scenario. TRANSLATION MODE: Convert casual speech into how your character would express the same meaning in this passionate situation.",
    "system_prompt_combat" => "You are #PLAYER_NAME# in combat. TRANSLATION MODE: Convert casual speech into how your character would express the same meaning in this urgent combat situation.",
    "translation_request" => "TRANSLATE this casual speech into your character's manner while keeping the same meaning: \"#ORIGINAL_INPUT#\"",
    "translation_request_explicit" => "TRANSLATE this casual speech to reflect the current intimate situation while maintaining your character's manner and the original meaning: \"#ORIGINAL_INPUT#\"",
    "translation_request_combat" => "TRANSLATE this casual speech into an appropriately tense and urgent manner while maintaining your character's style and the original meaning: \"#ORIGINAL_INPUT#\"",
    
    // ROLEPLAY MODE PROMPTS - Generate new character responses
    "roleplay_system_prompt" => "You are #PLAYER_NAME#. ROLEPLAY MODE: Generate natural character responses based on your personality and the situation.",
    "roleplay_system_prompt_explicit" => "You are #PLAYER_NAME# in an intimate scenario. ROLEPLAY MODE: Generate natural character responses that reflect both the passionate situation and your character's personality.",
    "roleplay_system_prompt_combat" => "You are #PLAYER_NAME# in combat. ROLEPLAY MODE: Generate natural character responses that reflect both the urgent combat situation and your character's personality.",
    "roleplay_request" => "ROLEPLAY as #PLAYER_NAME#. Respond naturally as your character would in this situation with a succinct line of dialogue in response to the ongoing conversation and situation.",
    "roleplay_request_explicit" => "ROLEPLAY as #PLAYER_NAME# in an intimate scenario. Respond naturally to the ongoing conversation and situation as your character would with a succinct line of dialogue.",
    "roleplay_request_combat" => "ROLEPLAY as #PLAYER_NAME# in combat. Respond naturally to the ongoing conversation and situation as your character would with a succinct line of dialogue.",
    "sections" => Array(
        "CHARACTER_BACKGROUND" => Array(
            "enabled" => true,
            "header" => "## YOUR DESCRIPTION AND PERSONALITY",
            "content" => "#PLAYER_BIOS#\n#HERIKA_PERS#\nPronouns: #PLAYER_SUBJECT#/#PLAYER_OBJECT#/#PLAYER_POSSESSIVE#\nCurrent State: #HERIKA_DYNAMIC#\nPhysical Description: #PHYSICAL_DESCRIPTION#\nMental State: #MIND_STATE#",
            "order" => 0
        ),
        "CHARACTER_STATUS" => Array(
            "enabled" => true,
            "header" => "## YOUR CURRENT STATUS",
            "content" => "#VITALS#\n#AROUSAL_STATUS#\n#SURVIVAL_STATUS#\n#CLOTHING_STATUS#\n#FERTILITY_STATUS#\n#TATTOO_STATUS#\n#BOUNTY_STATUS#",
            "order" => 1
        ),
        "NEARBY_ENTITIES" => Array(
            "enabled" => true,
            "header" => "## NEARBY ENTITIES",
            "content" => "Characters: #NEARBY_ACTORS#\nLocations: #NEARBY_LOCATIONS#",
            "order" => 2
        ),
        "RECENT_EVENTS" => Array(
            "enabled" => true,
            "header" => "## RECENT EVENTS",
            "content" => "#RECENT_EVENTS#",
            "order" => 3
        ),
        "INSTRUCTIONS" => Array(
            "enabled" => true,
            "header" => "## INSTRUCTIONS",
            "content" => "1. Correct any misheard names using the nearby names list\n2. Keep responses brief and true to the original meaning\n3. Do not add character name prefixes to your response\n4. Provide only the translated dialogue\n5. Emphasize recent events and dialogue in your response.",
            "order" => 4
        )
    )
);

// Metrics configuration
$GLOBALS['minai_metrics_enabled'] = true;                                     // Enable metrics collection
$GLOBALS['minai_metrics_sampling_rate'] = 1.0;                                // Sampling rate (0.0-1.0, where 1.0 = 100%)
$GLOBALS['minai_metrics_file'] = "/var/www/html/HerikaServer/log/minai_metrics.jsonl"; // Path to store metrics data
