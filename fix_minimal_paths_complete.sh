#!/bin/bash

# Complete path fix script for minai_minimal standalone version

echo "Fixing all path references in minai_minimal..."

# Navigate to minai_minimal
cd /Users/sebastian.biehl/Projects/MinAI/minai_minimal

# 1. Fix API files that try to include HerikaServer conf.php
echo "Fixing API files..."

# For API files, we need to remove HerikaServer dependencies
# and make them work with local configuration

# Fix api/main.php - replace HerikaServer paths with local ones
sed -i '' 's|require_once($path . "conf".DIRECTORY_SEPARATOR."conf.php");|// HerikaServer conf not needed in minimal version|g' api/main.php
sed -i '' 's|require_once($path. "lib" .DIRECTORY_SEPARATOR."{$GLOBALS\["DBDRIVER"\]}.class.php");|// Database not needed in minimal version|g' api/main.php
sed -i '' 's|$GLOBALS\["db"\] = new sql();|// Database not needed in minimal version|g' api/main.php

# Similar fixes for other API files
for file in api/*.php; do
    if [ -f "$file" ]; then
        # Remove HerikaServer conf.php includes
        sed -i '' 's|require_once($path . "conf".DIRECTORY_SEPARATOR."conf.php");|// HerikaServer conf not needed in minimal version|g' "$file"
        sed -i '' 's|require_once($path. "lib" .DIRECTORY_SEPARATOR."{$GLOBALS\["DBDRIVER"\]}.class.php");|// Database not needed in minimal version|g' "$file"
        sed -i '' 's|$GLOBALS\["db"\] = new sql();|// Database not needed in minimal version|g' "$file"
        
        # Fix relative paths that go up to HerikaServer
        sed -i '' 's|$path = "..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR;|// Not needed in minimal version|g' "$file"
        
        # Fix hardcoded paths that reference minai_plugin
        sed -i '' 's|/var/www/html/HerikaServer/ext/minai_plugin|/var/www/html/HerikaServer/ext/minai_minimal|g' "$file"
    fi
done

# 2. Fix other files that reference conf.php
echo "Fixing other files with HerikaServer dependencies..."

for file in *.php; do
    if [ -f "$file" ]; then
        # Comment out HerikaServer conf.php includes
        sed -i '' 's|require_once.*conf/conf.php.*|// HerikaServer conf not needed in minimal version|g' "$file"
        sed -i '' 's|include.*conf/conf.php.*|// HerikaServer conf not needed in minimal version|g' "$file"
        
        # Fix minai_plugin references
        sed -i '' 's|minai_plugin|minai_minimal|g' "$file"
    fi
done

# 3. Fix context builder includes
echo "Fixing context builder includes..."

# Fix system_prompt_context.php includes
if [ -f "contextbuilders/system_prompt_context.php" ]; then
    # The context modules are missing, let's create stub versions
    mkdir -p contextbuilders/context_modules
    
    # Check if missing files exist and create stubs if needed
    if [ ! -f "contextbuilders/context_modules/character_context.php" ]; then
        cat > contextbuilders/context_modules/character_context.php << 'EOF'
<?php
// Character context builders for MinAI minimal

function InitializeCharacterContextBuilders() {
    // Minimal character context builders
}
EOF
    fi
    
    if [ ! -f "contextbuilders/context_modules/relationship_context.php" ]; then
        cat > contextbuilders/context_modules/relationship_context.php << 'EOF'
<?php
// Relationship context builders for MinAI minimal

function InitializeRelationshipContextBuilders() {
    // Minimal relationship context builders
}
EOF
    fi
    
    if [ ! -f "contextbuilders/context_modules/environmental_context.php" ]; then
        cat > contextbuilders/context_modules/environmental_context.php << 'EOF'
<?php
// Environmental context builders for MinAI minimal

function InitializeEnvironmentalContextBuilders() {
    // Minimal environmental context builders
}
EOF
    fi
    
    if [ ! -f "contextbuilders/context_modules/nsfw_context.php" ]; then
        cat > contextbuilders/context_modules/nsfw_context.php << 'EOF'
<?php
// NSFW context builders for MinAI minimal (disabled)

function InitializeNSFWContextBuilders() {
    // NSFW features disabled in minimal version
}
EOF
    fi
fi

# 4. Create missing utility files
echo "Creating missing utility files..."

if [ ! -f "utils/format_util.php" ]; then
    cat > utils/format_util.php << 'EOF'
<?php
// Format utility for MinAI minimal

class FormatUtil {
    public static function formatContext($context) {
        return trim($context);
    }
}
EOF
fi

if [ ! -f "utils/metrics_util.php" ]; then
    cat > utils/metrics_util.php << 'EOF'
<?php
// Metrics utility for MinAI minimal

function minai_start_timer($name, $parent = null) {
    // Simplified metrics for minimal version
}

function minai_stop_timer($name, $data = []) {
    // Simplified metrics for minimal version
}
EOF
fi

# 5. Fix any remaining path issues in utils
echo "Fixing utility file paths..."

for file in utils/*.php; do
    if [ -f "$file" ]; then
        # Fix any minai_plugin references
        sed -i '' 's|minai_plugin|minai_minimal|g' "$file"
    fi
done

# 6. Create a simplified config.php if it doesn't exist
if [ ! -f "config.php" ]; then
    echo "Creating config.php from config.base.php..."
    cp config.base.php config.php
fi

# 7. Fix manifest.json path references
if [ -f "manifest.json" ]; then
    sed -i '' 's|minai_plugin|minai_minimal|g' manifest.json
fi

echo "Path fixes complete!"
echo ""
echo "To test the fixes:"
echo "1. Run: php debug_paths.php"
echo "2. Check the output for any remaining errors"
echo "3. Test the configuration page"