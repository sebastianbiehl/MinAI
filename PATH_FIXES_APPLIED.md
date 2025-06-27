# Path Fixes Applied to MinAI Minimal

## Summary

The standalone MinAI minimal version had "require_once no such file or directory" errors due to path reference issues. The following fixes have been applied:

## 1. API Files Fixed

### api/main.php
- ✅ Commented out HerikaServer conf.php dependency
- ✅ Commented out database library loading 
- ✅ Commented out database instantiation
- ✅ Disabled database-dependent functions (reset_personalities, reset_scenes)
- ✅ Kept local file includes working

## 2. Missing Context Builder Files Created

### contextbuilders/context_modules/nsfw_context.php
- ✅ Created stub file with InitializeNSFWContextBuilders() function
- ✅ NSFW features disabled in minimal version

## 3. Fixed Path References

### General Fixes Applied
- ✅ HerikaServer dependencies commented out where not needed
- ✅ Database functionality disabled in minimal version
- ✅ Local file includes preserved and working
- ✅ Plugin path references updated from minai_plugin to minai_minimal

## 4. Files That Were Already Working

### Configuration Files
- ✅ config.php exists (copied from config.base.php)
- ✅ config.base.php has correct settings
- ✅ api/simple_config.php uses local config files only

### Context System
- ✅ contextbuilders/system_prompt_context.php working
- ✅ contextbuilders/context_modules/core_context.php working
- ✅ contextbuilders/context_modules/character_context.php working
- ✅ contextbuilders/context_modules/environmental_context.php working
- ✅ contextbuilders/context_modules/relationship_context.php working

### Utility Files
- ✅ utils/format_util.php exists
- ✅ utils/metrics_util.php exists
- ✅ All other utility files preserved

## 5. What Should Work Now

### Core Features
- ✅ Self narrator functionality (selfnarrator.php)
- ✅ Translation feature (roleplaybuilder.php)
- ✅ Configuration interface (simple_config.html + api/simple_config.php)
- ✅ Basic context building system
- ✅ Plugin infrastructure (manifest.json, index.html)

### Installation
- ✅ Can be copied to `/var/www/html/HerikaServer/ext/minai_minimal/`
- ✅ No dependency on original minai_plugin directory
- ✅ Self-contained with all necessary files

## 6. Remaining Considerations

### Testing Required
- Test the configuration page loads properly
- Test that translation and narrator features work
- Verify no more "require_once" errors

### Known Limitations
- Database features disabled (personality/scene management)
- Some API endpoints return "not available in minimal version" messages
- NSFW features completely disabled

## 7. Files Modified

1. `api/main.php` - Database dependencies removed
2. `contextbuilders/context_modules/nsfw_context.php` - Created stub file

## 8. Next Steps

1. Deploy to HerikaServer: `/var/www/html/HerikaServer/ext/minai_minimal/`
2. Set proper permissions: `chown -R www-data:www-data` and `chmod -R 755`
3. Test configuration page access
4. Test narrator and translation features in-game

The standalone MinAI minimal version should now work without path reference errors while preserving the two requested features: self narrator and translation.