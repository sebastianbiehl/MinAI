#!/bin/bash

# Fix permissions for MinAI minimal plugin

echo "Fixing permissions for MinAI minimal plugin..."

# Set directory permissions
echo "Setting directory permissions..."
sudo chown -R www-data:www-data /var/www/html/HerikaServer/ext/minai_minimal/
sudo chmod -R 755 /var/www/html/HerikaServer/ext/minai_minimal/

# Make config files writable
echo "Making configuration files writable..."
sudo chmod 664 /var/www/html/HerikaServer/ext/minai_minimal/config.php 2>/dev/null || echo "config.php not found (will be created)"
sudo chmod 664 /var/www/html/HerikaServer/ext/minai_minimal/user_config.json 2>/dev/null || echo "user_config.json not found (will be created)"

# Make log directory writable
echo "Making log directory writable..."
sudo chmod 777 /var/www/html/HerikaServer/log/ 2>/dev/null || echo "Log directory not found"

# Create user_config.json if it doesn't exist
echo "Creating user_config.json if needed..."
if [ ! -f /var/www/html/HerikaServer/ext/minai_minimal/user_config.json ]; then
    sudo touch /var/www/html/HerikaServer/ext/minai_minimal/user_config.json
    sudo chown www-data:www-data /var/www/html/HerikaServer/ext/minai_minimal/user_config.json
    sudo chmod 664 /var/www/html/HerikaServer/ext/minai_minimal/user_config.json
    echo '{}' | sudo tee /var/www/html/HerikaServer/ext/minai_minimal/user_config.json > /dev/null
fi

# Create config.php if it doesn't exist
echo "Creating config.php if needed..."
if [ ! -f /var/www/html/HerikaServer/ext/minai_minimal/config.php ]; then
    sudo cp /var/www/html/HerikaServer/ext/minai_minimal/config.base.php /var/www/html/HerikaServer/ext/minai_minimal/config.php
    sudo chown www-data:www-data /var/www/html/HerikaServer/ext/minai_minimal/config.php
    sudo chmod 664 /var/www/html/HerikaServer/ext/minai_minimal/config.php
fi

echo "✅ Permissions fixed!"
echo ""
echo "Summary:"
echo "- Plugin directory: 755 (www-data:www-data)"
echo "- Configuration files: 664 (www-data:www-data)"
echo "- Log directory: 777"
echo ""
echo "The plugin should now be able to save configuration and write log files."