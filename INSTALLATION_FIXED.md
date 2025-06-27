# MinAI Bridge - Fixed Installation Guide

## 🔧 **What Was Fixed**

The original bridge was missing critical MinAI integration logic. Here's what I fixed:

### ✅ **Fixed Issues**
1. **Request Detection** - Now properly detects when MinAI should activate
2. **Player Input Processing** - Handles `inputtext` requests for translation/narrator
3. **Request Type Mapping** - Converts HerikaServer requests to backend format
4. **Response Integration** - Properly applies responses back to HerikaServer
5. **Missing Functions** - Added mock functions that MinAI expects
6. **Casual Speech Detection** - Identifies text that needs translation

### 🎯 **How It Now Works**

```
Skyrim Mod → HerikaServer → Bridge Plugin → JavaScript Backend → Clean Response
```

**Request Flow:**
1. **HerikaServer** receives request from Skyrim mod
2. **Bridge preprocessing.php** checks if MinAI should handle it
3. **Bridge** converts request to JavaScript backend format
4. **JavaScript backend** processes with clean AI
5. **Bridge** applies response back to HerikaServer globals
6. **HerikaServer** sends response to Skyrim mod

## 🚀 **Installation Steps (Updated)**

### 1. **Copy Files to Server**

```bash
# On your server with HerikaServer installed
cd /var/www/html/HerikaServer/ext/

# Copy both directories
cp -r /path/to/minai_bridge ./
cp -r /path/to/minai_js ./

# Set proper permissions
chown -R www-data:www-data minai_bridge minai_js
chmod -R 755 minai_bridge minai_js
```

### 2. **Ensure Node.js is Available**

```bash
# Check Node.js
node --version  # Should be 16+
npm --version

# If not installed, install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs
```

### 3. **Test Backend Manually First**

```bash
# Navigate to JavaScript backend
cd /var/www/html/HerikaServer/ext/minai_js

# Install dependencies
npm install

# Start backend manually to test
node server.js &

# Test if it's working
curl http://localhost:8080/health

# Should return: {"status":"healthy","timestamp":"...","features":["narrator","translation","dungeonmaster"]}
```

### 4. **Test Bridge Integration**

```bash
# Test the bridge logic
php /var/www/html/HerikaServer/ext/minai_bridge/test_bridge.php
```

### 5. **Configure Backend**

Open in browser: `http://your-server:8080/config`

- Add your OpenRouter/OpenAI API key
- Configure custom voice models
- Set custom prompts

### 6. **Restart HerikaServer**

```bash
# Restart web server
sudo systemctl restart apache2
# OR
sudo systemctl restart nginx
```

## 🔍 **Troubleshooting Guide**

### **Backend Won't Start Automatically**

If the backend doesn't start automatically:

```bash
# Check if backend is running
curl http://localhost:8080/health

# If not running, start manually
cd /var/www/html/HerikaServer/ext/minai_js
nohup node server.js > logs/server.log 2>&1 &

# Check logs
tail -f logs/server.log
```

### **No MinAI Processing in Game**

1. **Check Bridge Logs:**
   ```bash
   tail -f /var/www/html/HerikaServer/ext/minai_bridge/logs/bridge.log
   ```

2. **Test Request Processing:**
   ```bash
   # Test with a sample request
   php -r "
   \$GLOBALS['PLAYER_NAME'] = 'TestPlayer';
   \$GLOBALS['gameRequest'] = ['inputtext', 'TestPlayer', '', 'hey what\\'s up dude', ''];
   require '/var/www/html/HerikaServer/ext/minai_bridge/preprocessing.php';
   echo 'Result: ' . \$GLOBALS['gameRequest'][3];
   "
   ```

3. **Check Backend Connectivity:**
   ```bash
   # Test backend from PHP
   php -r "
   \$result = file_get_contents('http://localhost:8080/health');
   echo 'Backend response: ' . \$result;
   "
   ```

### **Requests Not Being Processed**

Check the bridge logic:

```bash
# Test if request should be handled
php -r "
require '/var/www/html/HerikaServer/ext/minai_bridge/preprocessing.php';
\$test = ['inputtext', 'Player', '', 'lol this is cool', ''];
echo 'Should handle: ' . (minai_bridge_should_handle_request(\$test) ? 'YES' : 'NO');
"
```

### **Manual Backend Startup**

If automatic startup fails, use the manual starter:

Visit: `http://your-server/HerikaServer/ext/minai_bridge/manual_start.php`

## 📋 **Key Fixes Applied**

### 1. **Request Detection Logic**

**Before (Broken):**
```php
// Only handled specific minai_* requests
$handledTypes = ['minai_translate', 'minai_narrator', ...];
```

**After (Fixed):**
```php
// Handles player input when conditions are met
if (in_array($requestType, $playerInputTypes)) {
    if (IsEnabled($GLOBALS["PLAYER_NAME"], 'isRoleplaying')) {
        return true; // Translation mode
    }
    if (minai_bridge_needs_translation($message)) {
        return true; // Casual speech detected
    }
}
```

### 2. **Request Type Mapping**

**Before (Broken):**
```php
// Sent requests as-is
$response = forward_request($gameRequest);
```

**After (Fixed):**
```php
// Maps HerikaServer requests to backend format
$typeMapping = [
    'inputtext' => 'minai_translate',
    'minai_narrator_talk' => 'inputtext',
    // ... proper mapping
];
```

### 3. **Response Integration**

**Before (Broken):**
```php
// Simple response copy
$GLOBALS["gameRequest"][3] = $response['dialogue'];
```

**After (Fixed):**
```php
// Proper response handling by type
switch ($response['action']) {
    case 'translated_speech':
        $GLOBALS["gameRequest"][0] = "inputtext";
        $GLOBALS["gameRequest"][3] = $playerName . ": " . $response['dialogue'];
        $GLOBALS["FORCED_TTS"] = true;
        // ... proper voice assignment
}
```

## 🎉 **Expected Results**

When working correctly:

1. ✅ **Translation**: Typing "hey what's up" becomes character-appropriate dialogue
2. ✅ **Narrator**: Player actions trigger internal thoughts
3. ✅ **Dungeonmaster**: DM commands create events NPCs recognize
4. ✅ **No PHP Errors**: Clean processing with JavaScript backend
5. ✅ **Custom Prompts**: Actually work and are used by the AI
6. ✅ **Custom Voices**: Any voice model names work

## 📊 **Status Monitoring**

- **Bridge Dashboard**: `/HerikaServer/ext/minai_bridge/index.html`
- **Backend Config**: `http://localhost:8080/config`
- **Bridge Logs**: `minai_bridge/logs/bridge.log`
- **Backend Logs**: `minai_js/logs/minai.log`

---

**The bridge should now properly integrate with HerikaServer and process MinAI requests correctly! 🎯**