# MinAI Bridge Plugin

A bridge plugin that automatically manages a clean JavaScript backend for MinAI functionality in HerikaServer.

## 🎯 What This Is

**MinAI Bridge** is a HerikaServer plugin that:
- **Automatically starts** the JavaScript MinAI backend when HerikaServer loads
- **Forwards requests** from HerikaServer to the clean JavaScript implementation  
- **Handles responses** back to HerikaServer in the expected format
- **Eliminates PHP conflicts** by using JavaScript for AI processing

## 🚀 Installation

1. **Copy the bridge plugin** to your HerikaServer extensions directory:
   ```bash
   cp -r minai_bridge /var/www/html/HerikaServer/ext/
   ```

2. **Copy the JavaScript backend** alongside it:
   ```bash
   cp -r minai_js /var/www/html/HerikaServer/ext/
   ```

3. **Ensure Node.js is installed** on your server:
   ```bash
   # Check if Node.js is available
   node --version
   npm --version
   ```

4. **Restart HerikaServer** - The bridge will auto-configure on first load

## 🔧 How It Works

### Automatic Flow
```
Skyrim Mod → HerikaServer → MinAI Bridge (PHP) → JavaScript Backend → Response
```

### Bridge Components
- **`prerequest.php`** - Checks if JavaScript backend is running, starts it if needed
- **`preprocessing.php`** - Forwards MinAI requests to JavaScript backend
- **`postrequest.php`** - Handles cleanup after processing
- **`index.html`** - Status dashboard and configuration access

### Auto-Start Process
1. When HerikaServer loads, it includes `prerequest.php`
2. Bridge checks if JavaScript backend is running on port 8080
3. If not running, bridge automatically starts it via Node.js
4. Backend installs dependencies if needed (runs `npm install`)
5. Backend starts and begins handling requests

## 🎛️ Configuration

### Bridge Dashboard
- Access at: `/HerikaServer/ext/minai_bridge/index.html`
- Shows system status, logs, and configuration links
- No manual configuration needed - it's all automatic!

### Backend Configuration  
- Automatically opens at: `http://localhost:8080/config`
- Configure AI API keys, prompts, voice models, etc.
- Same clean interface as the standalone JavaScript version

## 📋 Features

All the same MinAI features, but cleaner:

### ✅ What Works (Same as Before)
- **Self Narrator** - Internal player thoughts and reactions
- **Translation** - Casual speech → character dialogue
- **Dungeonmaster** - Events NPCs treat as canonical
- **Voice Integration** - Full TTS support with custom voice models
- **Custom Prompts** - Actually work now (unlike the PHP version)

### ✅ What's Improved  
- **No PHP function conflicts** - JavaScript backend is isolated
- **Automatic management** - No manual server starting
- **Better logging** - Clear, readable logs
- **Working custom prompts** - AI actually uses your custom prompts
- **Text input voice models** - Enter any voice name you want
- **Faster startup** - No database dependencies
- **Easy maintenance** - Modern JavaScript codebase

### ⚠️ What's Different
- **Backend runs on port 8080** - Make sure this port is available
- **Requires Node.js** - Must be installed on the server
- **Automatic dependency management** - `npm install` runs automatically

## 🔍 Monitoring

### Bridge Logs
- **Location**: `minai_bridge/logs/bridge.log`
- **Content**: Bridge startup, backend management, request forwarding
- **Access**: Via the web dashboard or directly

### Backend Logs
- **Location**: `minai_js/logs/minai.log`  
- **Content**: AI processing, API calls, detailed request handling
- **Access**: Via the backend configuration interface

## 🐛 Troubleshooting

### Common Issues

**Backend won't start automatically**
- Check that Node.js is installed: `node --version`
- Verify port 8080 is available: `netstat -an | grep 8080`
- Check bridge logs for startup errors

**Requests not being processed**
- Verify backend is running: visit `http://localhost:8080/health`
- Check that request types are supported (inputtext, minai_translate, etc.)
- Review bridge logs for forwarding errors

**Configuration not accessible**
- Ensure backend is running on port 8080
- Check firewall settings for localhost communication
- Try accessing directly: `http://localhost:8080/config`

**Node.js dependencies failing**
- Manually install: `cd /var/www/html/HerikaServer/ext/minai_js && npm install`
- Check Node.js version compatibility (requires Node 16+)
- Verify write permissions in the minai_js directory

### Manual Backend Management

If automatic management fails, you can manually manage the backend:

```bash
# Navigate to backend directory
cd /var/www/html/HerikaServer/ext/minai_js

# Install dependencies  
npm install

# Start backend manually
npm start
```

## 🔄 Migration from Old MinAI

### From PHP MinAI
1. **Disable old plugin** - Remove or rename the old minai_minimal/minai_plugin directory
2. **Install bridge** - Copy minai_bridge and minai_js to HerikaServer/ext/
3. **Restart HerikaServer** - Bridge auto-configures on first load
4. **Copy settings** - Use the web interface to reconfigure your preferences

### No Skyrim Mod Changes Needed
The bridge maintains full compatibility with existing MinAI Skyrim mod configurations.

## 📊 Status Dashboard

The bridge includes a beautiful status dashboard that shows:
- ✅ Backend server status
- ✅ HerikaServer plugin integration status  
- ✅ Configuration accessibility
- 📝 Recent logs and activity
- 🛠️ Direct links to backend configuration

## 🎉 Benefits

### For Users
- **No manual setup** - Everything just works automatically
- **No PHP errors** - Clean JavaScript backend eliminates conflicts
- **Better features** - Custom prompts actually work
- **Same experience** - All the MinAI functionality you're used to

### For Developers  
- **Clean codebase** - Modern JavaScript instead of complex PHP
- **Easy debugging** - Clear logs and error messages
- **Simple maintenance** - Well-structured, documented code
- **Extensible** - Easy to add new features

---

**🎭 Enjoy your enhanced Skyrim experience with clean, reliable AI - now with zero setup required!**