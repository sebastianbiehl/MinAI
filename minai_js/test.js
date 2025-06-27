#!/usr/bin/env node

/**
 * Simple test script for MinAI JavaScript server
 * Tests the core functionality without requiring the Skyrim mod
 */

const axios = require('axios');

const SERVER_URL = 'http://localhost:8080';

// Test configuration
const testConfig = {
    api: {
        provider: 'openrouter',
        openrouter: {
            api_key: 'test-key', // Replace with real key for actual testing
            model: 'google/gemma-2-9b-it:free'
        }
    },
    features: {
        self_narrator: true,
        translation: true,
        dungeonmaster: true
    }
};

async function delay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

async function testHealth() {
    console.log('🔍 Testing health endpoint...');
    try {
        const response = await axios.get(`${SERVER_URL}/health`);
        console.log('✅ Health check passed:', response.data);
        return true;
    } catch (error) {
        console.log('❌ Health check failed:', error.message);
        return false;
    }
}

async function testConfig() {
    console.log('⚙️ Testing configuration...');
    try {
        // Test getting config
        const getResponse = await axios.get(`${SERVER_URL}/api/config`);
        console.log('✅ Get config passed');
        
        // Test updating config
        const postResponse = await axios.post(`${SERVER_URL}/api/config`, testConfig);
        console.log('✅ Update config passed:', postResponse.data);
        return true;
    } catch (error) {
        console.log('❌ Config test failed:', error.message);
        return false;
    }
}

async function testNarrator() {
    console.log('🧠 Testing narrator functionality...');
    try {
        const gameRequest = [
            'inputtext',
            'Player',
            '',
            'I just defeated a dragon',
            ''
        ];
        
        const response = await axios.post(SERVER_URL, { gameRequest });
        console.log('✅ Narrator test response:', response.data);
        return response.data.action && response.data.dialogue;
    } catch (error) {
        console.log('❌ Narrator test failed:', error.message);
        return false;
    }
}

async function testTranslation() {
    console.log('🗣️ Testing translation functionality...');
    try {
        const gameRequest = [
            'minai_translate',
            'Player',
            'Lydia',
            'hey what\'s up dude',
            ''
        ];
        
        const response = await axios.post(SERVER_URL, { gameRequest });
        console.log('✅ Translation test response:', response.data);
        return response.data.action && response.data.dialogue;
    } catch (error) {
        console.log('❌ Translation test failed:', error.message);
        return false;
    }
}

async function testDungeonmaster() {
    console.log('🎲 Testing dungeonmaster functionality...');
    try {
        const gameRequest = [
            'minai_dungeon_master',
            'DungeonMaster',
            'all',
            'A mysterious merchant appears in the marketplace',
            ''
        ];
        
        const response = await axios.post(SERVER_URL, { gameRequest });
        console.log('✅ Dungeonmaster test response:', response.data);
        return response.data.action && response.data.dialogue;
    } catch (error) {
        console.log('❌ Dungeonmaster test failed:', error.message);
        return false;
    }
}

async function testFastCommand() {
    console.log('⚡ Testing fast command handling...');
    try {
        const gameRequest = [
            'addnpc',
            'TestNPC',
            '',
            '',
            ''
        ];
        
        const response = await axios.post(SERVER_URL, { gameRequest });
        console.log('✅ Fast command test response:', response.data);
        return response.data.success;
    } catch (error) {
        console.log('❌ Fast command test failed:', error.message);
        return false;
    }
}

async function runTests() {
    console.log('🚀 Starting MinAI JavaScript Tests');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    console.log();
    
    const results = [];
    
    // Test health endpoint
    results.push(await testHealth());
    await delay(500);
    
    // Test configuration
    results.push(await testConfig());
    await delay(500);
    
    // Test fast command (should work without API)
    results.push(await testFastCommand());
    await delay(500);
    
    console.log();
    console.log('📝 AI-dependent tests (require valid API key):');
    console.log('   To test with real AI, update the API key in test.js');
    console.log();
    
    // These tests would require a real API key
    console.log('🧠 Narrator test (skipped - requires API key)');
    console.log('🗣️ Translation test (skipped - requires API key)');
    console.log('🎲 Dungeonmaster test (skipped - requires API key)');
    
    console.log();
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    
    const passed = results.filter(r => r).length;
    const total = results.length;
    
    if (passed === total) {
        console.log(`🎉 All basic tests passed (${passed}/${total})`);
        console.log('✅ Server is ready for Skyrim mod integration!');
    } else {
        console.log(`⚠️ Some tests failed (${passed}/${total} passed)`);
        console.log('❌ Check the server logs for details');
    }
    
    console.log();
    console.log('📋 Next steps:');
    console.log('1. Add your AI API key in the web config: http://localhost:8080/config');
    console.log('2. Point your Skyrim mod to: http://localhost:8080');
    console.log('3. Enjoy your enhanced Skyrim experience!');
}

// Check if server is running
axios.get(`${SERVER_URL}/health`)
    .then(() => {
        runTests();
    })
    .catch(() => {
        console.log('❌ Server is not running on http://localhost:8080');
        console.log('📋 Start the server first with: npm start');
        console.log('   Or run: ./start.sh');
    });

module.exports = { runTests };