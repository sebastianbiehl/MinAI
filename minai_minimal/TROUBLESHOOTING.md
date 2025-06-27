# MinAI Minimal Troubleshooting Guide

## Common Path Errors

### "require_once: No such file or directory"

This error usually occurs when files are looking for dependencies in the wrong location.

**Quick Fix:**
1. Access the debug page: `http://your-server/HerikaServer/ext/minai_minimal/debug_paths.php`
2. Check which files are missing
3. Verify HerikaServer integration

### Missing HerikaServer Dependencies

**Error**: `failed opening required conf.php`

**Solution**: Ensure MinAI is properly installed in HerikaServer:
```bash
# Your minai_minimal should be at:
/var/www/html/HerikaServer/ext/minai_minimal/

# HerikaServer config should exist at:
/var/www/html/HerikaServer/conf/conf.php
```

### File Permission Issues

**Error**: `Permission denied` or files not writable

**Solution**:
```bash
# Fix permissions
sudo chown -R www-data:www-data /var/www/html/HerikaServer/ext/minai_minimal/
sudo chmod -R 755 /var/www/html/HerikaServer/ext/minai_minimal/

# Make logs writable
sudo chmod 777 /var/www/html/HerikaServer/log/
```

### Database Connection Errors

**Error**: Database connection failed

**Solution**: 
1. Verify HerikaServer database is configured
2. Check `/var/www/html/HerikaServer/conf/conf.php` for database settings
3. Ensure MinAI database tables are created

## Installation Verification

### 1. Check File Structure
```bash
ls -la /var/www/html/HerikaServer/ext/minai_minimal/
```
Should show:
- `config.php`
- `manifest.json` 
- `index.html`
- `simple_config.html`
- `api/` directory
- `utils/` directory

### 2. Test Basic Access
Navigate to: `http://your-server/HerikaServer/ext/minai_minimal/`

Should show the MinAI configuration page.

### 3. Test Configuration API
Navigate to: `http://your-server/HerikaServer/ext/minai_minimal/api/simple_config.php`

Should return JSON configuration data.

### 4. Test Debug Page
Navigate to: `http://your-server/HerikaServer/ext/minai_minimal/debug_paths.php`

Should show green checkmarks for most items.

## Feature-Specific Issues

### Translation Not Working

1. **Check LLM Connection**: Verify OpenRouter API settings in HerikaServer
2. **Check Logs**: Look at `/var/www/html/HerikaServer/log/minai_context_sent_to_llm.log`
3. **Test Request**: Send a `minai_translate` request manually

### Narrator Not Working

1. **Check Voice Settings**: Verify narrator voice is configured
2. **Check TTS**: Ensure text-to-speech system is working
3. **Test Request**: Send a `minai_narrator` request manually

### Configuration Not Saving

1. **Check Permissions**: Ensure `user_config.json` can be written
2. **Check API**: Test the simple_config.php endpoint directly
3. **Browser Console**: Check for JavaScript errors

## Manual Installation Steps

If automated installation fails:

```bash
# 1. Create directory
sudo mkdir -p /var/www/html/HerikaServer/ext/minai_minimal

# 2. Copy files
sudo cp -r /path/to/minai_minimal/* /var/www/html/HerikaServer/ext/minai_minimal/

# 3. Fix ownership
sudo chown -R www-data:www-data /var/www/html/HerikaServer/ext/minai_minimal

# 4. Fix permissions
sudo chmod -R 755 /var/www/html/HerikaServer/ext/minai_minimal

# 5. Create config if missing
sudo cp /var/www/html/HerikaServer/ext/minai_minimal/config.base.php /var/www/html/HerikaServer/ext/minai_minimal/config.php
```

## Log Files to Check

1. **PHP Error Log**: Usually at `/var/log/apache2/error.log` or `/var/log/nginx/error.log`
2. **MinAI Logs**: `/var/www/html/HerikaServer/log/minai_minimal.log`
3. **HerikaServer Logs**: `/var/www/html/HerikaServer/log/`

## Getting Help

1. **Run Debug Script**: Always start with `debug_paths.php`
2. **Check Logs**: Look at error logs for specific error messages
3. **Test Minimal Case**: Try just loading the index page first
4. **Verify HerikaServer**: Ensure base HerikaServer is working

## Clean Reinstall

If all else fails:
```bash
# Remove current installation
sudo rm -rf /var/www/html/HerikaServer/ext/minai_minimal

# Copy fresh minimal version
sudo cp -r /path/to/minai_minimal /var/www/html/HerikaServer/ext/

# Fix permissions
sudo chown -R www-data:www-data /var/www/html/HerikaServer/ext/minai_minimal
sudo chmod -R 755 /var/www/html/HerikaServer/ext/minai_minimal
```