# MinAI Minimal - Complete Standalone Version

This is a complete, standalone minimal version of MinAI containing **only** the narrator and translation features with all necessary infrastructure.

## ✅ Features Included

### 🧠 Self Narrator
- Generates internal player thoughts and reactions to situations
- Triggered by `minai_narrator` or `self_narrator` requests
- Configurable narrator voice (default: dragon)

### 🗣️ Translation  
- Converts casual player input to character-appropriate speech
- Triggered by `minai_translate` or `minai_roleplay` requests
- Auto-detection of casual speech patterns
- Context-aware translation with character background

## 📁 Complete File Structure

This minimal version includes all essential files:

```
minai_minimal/
├── manifest.json              # Plugin registration
├── index.html                 # Main plugin page
├── simple_config.html         # Configuration interface
├── config.base.php           # Core configuration & prompts
├── roleplaybuilder.php       # Translation functionality  
├── selfnarrator.php          # Narrator functionality
├── prerequest.php            # Request preprocessing
├── preprocessing.php         # Main processing logic
├── postrequest.php           # Response postprocessing
├── utils/
│   ├── llm_utils.php         # LLM communication
│   ├── profile_utils.php     # Profile management
│   └── [other utilities]     # Format, metrics, etc.
├── api/
│   ├── main.php              # Core API endpoint
│   ├── simple_config.php     # Configuration API
│   ├── config.php            # Main config API
│   └── [other APIs]          # Diary, logs, etc.
├── contextbuilders/          # Minimal context builders
└── [other essential files]   # Globals, utils, etc.
```

## 🚀 Installation

### Option 1: Replace Current Plugin
```bash
# Backup your current plugin
mv minai_plugin minai_plugin_backup

# Install minimal version  
cp -r minai_minimal minai_plugin

# Update HerikaServer to include the plugin
# (Add to your HerikaServer configuration)
```

### Option 2: Side-by-Side Installation
```bash
# Keep both versions
# Access via: /HerikaServer/ext/minai_minimal/
```

## ⚙️ Configuration

1. **Access Configuration**: Navigate to plugin page → "Configuration"
2. **Configure Features**:
   - ✅ Enable/disable self narrator
   - 🎵 Select narrator voice
   - ✅ Enable/disable translation  
   - 🎵 Select player voice
   - 🔍 Auto-detect casual speech
   - 📝 Context message count

## 🎯 Usage

### Self Narrator
- Send `minai_narrator` requests to generate internal thoughts
- Example: Player encounters dragon → AI generates fear/excitement thoughts

### Translation
- Send `minai_translate` requests to convert casual speech
- Example: "hey what's up" → "Greetings, friend. How fare you this day?"
- Auto-detection converts casual input automatically

## 🔧 Technical Details

### Translation Prompts
- **System**: `"You are #PLAYER_NAME#. TRANSLATION MODE: Convert casual speech into how your character would say the same thing."`
- **Request**: `"TRANSLATE this casual speech into your character's manner while keeping the same meaning: \"#ORIGINAL_INPUT#\""`

### Context Building
- Character background and personality
- Current player status (simplified)
- Recent events and nearby entities
- Translation-specific instructions

### Voice Integration
- Proper TTS triggering for both features
- Voice type selection and fallbacks
- Narrator voice separation from player voice

## 📋 What Was Removed

- ❌ All NSFW/sexual content (20,000+ lines removed)
- ❌ Equipment/arousal/fertility systems
- ❌ Devious devices integration
- ❌ Tattoo and scene management
- ❌ Complex context builders
- ❌ Sexual personality systems

## 🔍 Troubleshooting

### Common Issues
1. **Configuration not saving**: Check file permissions on `user_config.json`
2. **Translation not working**: Verify LLM connection in diagnostics
3. **Voice not playing**: Check TTS configuration and voice model selection
4. **Missing features**: This is minimal - many features were intentionally removed

### Log Files
- Main log: `/var/www/html/HerikaServer/log/minai_minimal.log`
- LLM context: `/var/www/html/HerikaServer/log/minai_context_sent_to_llm.log`
- LLM output: `/var/www/html/HerikaServer/log/minai_output_from_llm.log`

## 🎉 Benefits of Minimal Version

- **Lightweight**: ~90% smaller than full version
- **Clean**: No unwanted features or complexity
- **Focused**: Only narrator and translation
- **Stable**: Fewer components = fewer bugs
- **Maintainable**: Easy to understand and modify

This minimal version provides a clean, focused experience with just the features you need!