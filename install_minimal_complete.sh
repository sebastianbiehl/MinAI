#!/bin/bash
# Install MinAI Minimal Complete Version

echo "=========================================="
echo "  MinAI Minimal Installation Script"
echo "=========================================="
echo ""

# Check if we're in the right directory
if [ ! -d "minai_minimal" ]; then
    echo "❌ Error: minai_minimal directory not found!"
    echo "Please run this script from the MinAI root directory."
    exit 1
fi

echo "🔍 Found MinAI minimal directory"

# Ask user for installation preference
echo ""
echo "Choose installation option:"
echo "1) Replace current minai_plugin (recommended)"
echo "2) Install side-by-side as minai_minimal"
echo "3) Just show what would be installed"
echo ""
read -p "Enter choice (1-3): " choice

case $choice in
    1)
        echo ""
        echo "📦 Installing MinAI Minimal (replacing current plugin)..."
        
        # Backup existing plugin if it exists
        if [ -d "minai_plugin" ]; then
            timestamp=$(date +"%Y%m%d_%H%M%S")
            backup_dir="minai_plugin_backup_$timestamp"
            echo "💾 Backing up current plugin to: $backup_dir"
            mv minai_plugin "$backup_dir"
        fi
        
        # Install minimal version
        echo "📋 Installing minimal version..."
        cp -r minai_minimal minai_plugin
        
        echo ""
        echo "✅ Installation complete!"
        echo ""
        echo "📍 Plugin installed at: minai_plugin/"
        echo "🌐 Access via: http://your-server/HerikaServer/ext/minai_plugin/"
        ;;
        
    2)
        echo ""
        echo "📦 Installing MinAI Minimal (side-by-side)..."
        echo "📋 Minimal version ready at: minai_minimal/"
        echo ""
        echo "✅ Installation complete!"
        echo ""
        echo "📍 Plugin available at: minai_minimal/"
        echo "🌐 Access via: http://your-server/HerikaServer/ext/minai_minimal/"
        ;;
        
    3)
        echo ""
        echo "📋 MinAI Minimal Contents:"
        echo ""
        find minai_minimal -type f -name "*.php" -o -name "*.html" -o -name "*.json" | sort
        echo ""
        echo "Total files: $(find minai_minimal -type f | wc -l)"
        echo "Total size: $(du -sh minai_minimal | cut -f1)"
        exit 0
        ;;
        
    *)
        echo "❌ Invalid choice. Exiting."
        exit 1
        ;;
esac

echo ""
echo "=========================================="
echo "           Configuration"
echo "=========================================="
echo ""
echo "🎯 Features included:"
echo "   🧠 Self Narrator - Internal player thoughts"
echo "   🗣️ Translation - Casual to character speech"
echo ""
echo "⚙️ To configure:"
echo "   1. Navigate to the plugin page"
echo "   2. Click 'Configuration'"
echo "   3. Enable desired features"
echo "   4. Set voice preferences"
echo ""
echo "📚 For help, see: README_MINIMAL.md"
echo ""
echo "🎉 Enjoy your clean, minimal MinAI experience!"