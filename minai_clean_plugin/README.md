# MinAI Clean Plugin

This is a clean, bug-free fork of MinAI that includes only the core features:

## Features Included

### 1. Self Narrator
- Transforms the narrator into your character's inner voice
- First-person perspective for immersive roleplay
- Configurable prompts for different scenarios
- Voice and text input support

### 2. Roleplay/Translation
- **Translation Mode**: Converts casual player input into character-appropriate speech
- **Roleplay Mode**: Generates new character responses based on current situation
- Scene-aware responses (normal, combat, explicit)
- Mind influence integration (drunk, gagged, etc.)
- Comprehensive character context system

### 3. DungeonMaster
- Send direct prompts to NPCs to tell them what has happened
- Target specific NPCs or broadcast to The Narrator
- Voice and text input options
- Real-time NPC responses to your directed events

## Installation

1. Extract to your HerikaServer `ext/` directory
2. Configure keybinds in MinAI MCM menu
3. Enable features through the web configuration interface

## Requirements

- HerikaServer (CHIM)
- MinAI Skyrim mod
- AIFF for voice functionality
- UIExtensions for text input menus

## Configuration

Access the configuration through the HerikaServer web interface:
- Self Narrator toggle and custom prompts
- Roleplay/Translation system settings
- DungeonMaster keybind configuration

## Clean Architecture

This fork removes:
- Excessive NSFW integrations
- Buggy experimental features
- Complex mod dependencies
- Confusing configuration options

Focuses on:
- Core functionality
- Clean, readable code
- Reliable operation
- Easy customization