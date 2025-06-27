#!/bin/bash

echo "🔍 MinAI Bridge Troubleshooting Script"
echo "======================================"

# Check if we're in the right location
echo
echo "📍 Current location check:"
pwd
ls -la

# Check if the directories exist
echo
echo "📁 Directory structure check:"
if [ -d "minai_bridge" ]; then
    echo "✅ minai_bridge directory found"
    ls -la minai_bridge/
else
    echo "❌ minai_bridge directory not found"
fi

if [ -d "minai_js" ]; then
    echo "✅ minai_js directory found"
    ls -la minai_js/
else
    echo "❌ minai_js directory not found"
fi

# Check Node.js installation
echo
echo "🟢 Node.js installation check:"
if command -v node &> /dev/null; then
    echo "✅ Node.js found: $(node --version)"
else
    echo "❌ Node.js not found - please install from https://nodejs.org/"
    exit 1
fi

if command -v npm &> /dev/null; then
    echo "✅ npm found: $(npm --version)"
else
    echo "❌ npm not found"
    exit 1
fi

# Check if port 8080 is available
echo
echo "🔌 Port 8080 check:"
if lsof -Pi :8080 -sTCP:LISTEN -t >/dev/null ; then
    echo "⚠️  Port 8080 is already in use:"
    lsof -Pi :8080 -sTCP:LISTEN
else
    echo "✅ Port 8080 is available"
fi

# Try to install dependencies if minai_js exists
if [ -d "minai_js" ]; then
    echo
    echo "📦 Installing JavaScript dependencies:"
    cd minai_js
    
    if [ ! -d "node_modules" ]; then
        echo "Installing npm packages..."
        npm install
        if [ $? -eq 0 ]; then
            echo "✅ Dependencies installed successfully"
        else
            echo "❌ Failed to install dependencies"
            exit 1
        fi
    else
        echo "✅ Dependencies already installed"
    fi
    
    # Try to start the server
    echo
    echo "🚀 Attempting to start the backend server:"
    echo "Starting server on port 8080..."
    
    # Start server in background and capture PID
    node server.js &
    SERVER_PID=$!
    echo "Server started with PID: $SERVER_PID"
    
    # Wait a moment for startup
    sleep 3
    
    # Test if server is responding
    echo "Testing server response..."
    if curl -s http://localhost:8080/health > /dev/null; then
        echo "✅ Server is responding!"
        echo "🎉 Backend is working correctly"
        echo
        echo "📋 Next steps:"
        echo "1. The backend is now running on http://localhost:8080"
        echo "2. Access configuration at: http://localhost:8080/config"
        echo "3. Check the bridge plugin in HerikaServer"
        echo
        echo "To stop the server later, run: kill $SERVER_PID"
    else
        echo "❌ Server is not responding"
        echo "Check the console output above for errors"
    fi
    
    cd ..
else
    echo "❌ Cannot test - minai_js directory not found"
fi

echo
echo "🔍 For more detailed debugging, run the PHP debug script:"
echo "   php minai_bridge/debug.php"