# MinAI Bridge Simple

## The Problem

I was overcomplicating the bridge. The original MinAI works by:

1. HerikaServer includes `preprocessing.php`
2. `preprocessing.php` calls `interceptRoleplayInput()` function
3. That function does all the AI processing

## The Simple Solution

This bridge:

1. **Replaces** the `interceptRoleplayInput()` function 
2. **Forwards** requests to the JavaScript backend via HTTP
3. **Applies** responses back to HerikaServer globals
4. **That's it!**

## Installation

```bash
# Copy to HerikaServer
cp -r minai_bridge_simple /var/www/html/HerikaServer/ext/
cp -r minai_js /var/www/html/HerikaServer/ext/

# Set permissions  
chown -R www-data:www-data /var/www/html/HerikaServer/ext/minai_bridge_simple
chown -R www-data:www-data /var/www/html/HerikaServer/ext/minai_js

# Restart HerikaServer
sudo systemctl restart apache2
```

## How It Works

1. **HerikaServer** loads the plugin and calls `preprocessing.php`
2. **prerequest.php** starts the JavaScript backend if needed
3. **preprocessing.php** replaces the roleplay function with a simple HTTP call
4. **JavaScript backend** processes the request with clean AI
5. **Response** gets applied back to HerikaServer globals
6. **HerikaServer** sends response to Skyrim

## Testing

1. Start backend manually:
   ```bash
   cd /var/www/html/HerikaServer/ext/minai_js
   npm install
   npm start
   ```

2. Test the bridge:
   ```bash
   curl http://localhost:8080/health
   ```

3. Check HerikaServer error logs for MinAI Bridge messages

## Expected Behavior

- ✅ Backend starts automatically when HerikaServer loads
- ✅ Player input gets processed for translation/narrator
- ✅ Direct MinAI requests work (dungeonmaster, etc.)
- ✅ Responses appear in game
- ✅ No PHP function conflicts

This approach is much simpler than the complex bridge I tried before!