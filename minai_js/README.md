# MinAI JavaScript - Clean & Simple AI for Skyrim

A clean, lightweight JavaScript implementation of MinAI that provides three core features:
- **Self Narrator**: Generate internal player thoughts and reactions
- **Translation**: Convert casual speech to character-appropriate dialogue  
- **Dungeonmaster**: Create events that NPCs treat as canonical

## 🚀 Quick Start

1. **Install Node.js** (if not already installed)
   - Download from [nodejs.org](https://nodejs.org/)

2. **Install Dependencies**
   ```bash
   cd minai_js
   npm install
   ```

3. **Configure Your API**
   - Start the server: `npm start`
   - Open [http://localhost:8080/config](http://localhost:8080/config)
   - Add your OpenRouter or OpenAI API key
   - Customize your settings

4. **Update Your Skyrim Mod**
   - Point your MinAI Skyrim mod to `http://localhost:8080`
   - The mod will work exactly the same way as before

## 🎯 Features

### Self Narrator
- Generates first-person internal thoughts for your character
- Responds to player actions, dialogue, and game events
- Customizable voice and prompts

### Translation System
- Automatically detects casual speech patterns
- Converts "hey what's up" to character-appropriate dialogue
- Maintains original meaning while fitting the fantasy setting

### Dungeonmaster
- Create narrative events that affect the entire game world
- NPCs will treat dungeonmaster events as if they actually happened
- Perfect for adding custom story elements

## ⚙️ Configuration

The web interface at `/config` allows you to customize:

- **AI Provider**: OpenRouter (recommended) or OpenAI
- **Voice Models**: Any voice model names you want
- **Custom Prompts**: Override default AI prompts
- **Character Info**: Player name, race, gender
- **Feature Toggles**: Enable/disable individual features

## 🔧 API Compatibility

This JavaScript version is **fully compatible** with the existing MinAI Skyrim mod. It accepts the same `gameRequest` format and returns responses in the expected format.

### Request Format
```javascript
{
  gameRequest: [
    "request_type",    // e.g., "minai_translate", "inputtext"
    "speaker_name",    // Who is speaking
    "target_name",     // Who is being spoken to  
    "message_content", // The actual message
    "additional_data"  // Extra context
  ]
}
```

### Response Format
```javascript
{
  action: "response_type",
  target: "target_actor",
  dialogue: "ai_generated_text",
  tts: {
    voice: "voice_model",
    text: "text_for_speech"
  }
}
```

## 📁 File Structure

```
minai_js/
├── server.js              # Main HTTP server
├── config/
│   └── config.js          # Configuration management
├── modules/
│   ├── narrator.js        # Self-narrator functionality
│   ├── translator.js      # Translation system
│   └── dungeonmaster.js   # Dungeonmaster events
├── utils/
│   ├── logger.js          # Logging system
│   └── aiApi.js           # AI API integration
├── public/
│   └── config.html        # Web configuration interface
└── logs/                  # Log files
```

## 🆚 Differences from PHP Version

### ✅ What's Improved
- **No PHP errors or conflicts** - Clean codebase from scratch
- **Simple configuration** - Web interface instead of complex files
- **Better logging** - Clear, readable logs
- **Faster startup** - No database dependencies
- **Easy maintenance** - Modern JavaScript, well-documented

### ⚠️ What's Removed
- Complex database operations
- Extensive caching systems
- Advanced context builders
- Metrics collection
- Most of the bloat and unused features

### 🔄 What's the Same
- **Full Skyrim mod compatibility**
- **Same request/response format**
- **Same core functionality** (narrator, translation, dungeonmaster)
- **Voice integration**
- **Custom prompts**

## 🚨 Migration from PHP Version

1. **Stop the old PHP server**
2. **Start the JavaScript server**: `npm start`
3. **Update your mod configuration** to point to `http://localhost:8080`
4. **Copy your settings** using the web interface at `/config`

No changes to the Skyrim mod are required!

## 🛠️ Development

### Running in Development Mode
```bash
npm run dev  # Automatically restarts on file changes
```

### API Endpoints
- `POST /` - Main endpoint (compatible with HerikaServer)
- `GET /config` - Configuration web interface
- `GET /api/config` - Get current configuration (JSON)
- `POST /api/config` - Update configuration
- `GET /health` - Health check

### Environment Variables
- `PORT` - Server port (default: 8080)
- `LOG_LEVEL` - Logging level (info, debug, error)

## 📊 Monitoring

- **Logs**: Check `logs/minai.log` for detailed operation logs
- **Health Check**: Visit `/health` for server status
- **Real-time**: Console output shows all requests and responses

## 🔑 API Keys

### OpenRouter (Recommended)
- Free tier available: `google/gemma-2-9b-it:free`
- Sign up at [openrouter.ai](https://openrouter.ai)
- More models and better pricing than OpenAI

### OpenAI
- Requires paid account
- Higher quality responses
- More expensive per request

## 🐛 Troubleshooting

### Common Issues

**Server won't start**
- Check if port 8080 is already in use
- Run `npm install` to ensure dependencies are installed

**API errors**
- Verify your API key is correct
- Check your internet connection
- Try the "Test API Connection" button in `/config`

**Skyrim mod can't connect**
- Ensure the server is running on the right port
- Check firewall settings
- Verify the mod is pointing to the correct URL

**No responses generated**
- Check the logs in `logs/minai.log`
- Verify the feature is enabled in `/config`
- Test the API connection

## 📝 License

MIT License - Feel free to modify and distribute.

## 🤝 Contributing

This is a clean, simple implementation focused on the core features. If you find bugs or want to add features, please:

1. Keep it simple and focused
2. Maintain compatibility with the Skyrim mod
3. Document your changes clearly

---

**Enjoy your enhanced Skyrim experience with clean, reliable AI! 🏰✨**