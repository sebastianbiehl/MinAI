#!/bin/bash
# Fix all path references in minai_minimal

echo "Fixing MinAI minimal path references..."

# Fix plugin path references from minai_plugin to minai_minimal
find minai_minimal -name "*.php" -exec sed -i '' 's|/ext/minai_plugin|/ext/minai_minimal|g' {} \;
find minai_minimal -name "*.html" -exec sed -i '' 's|/ext/minai_plugin|/ext/minai_minimal|g' {} \;

# Fix any hardcoded minai_plugin references
find minai_minimal -name "*.php" -exec sed -i '' 's|minai_plugin|minai_minimal|g' {} \;

echo "✅ Fixed plugin path references"

# Fix require_once paths that might be broken
# These are the most common problematic patterns
cd minai_minimal

# Create config.php if it doesn't exist
if [ ! -f "config.php" ]; then
    cp config.base.php config.php
    echo "✅ Created config.php from config.base.php"
fi

echo "✅ Path fixes complete!"
echo ""
echo "You may need to check these common issues:"
echo "1. HerikaServer path in web server configuration"
echo "2. Database connection settings"
echo "3. Log file permissions"