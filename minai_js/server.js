const express = require('express');
const cors = require('cors');
const bodyParser = require('body-parser');
const path = require('path');
const fs = require('fs');

// Import core modules
const narrator = require('./modules/narrator');
const translator = require('./modules/translator');
const dungeonmaster = require('./modules/dungeonmaster');
const config = require('./config/config');
const logger = require('./utils/logger');

const app = express();
const PORT = process.env.PORT || 8080;

// Middleware
app.use(cors());
app.use(bodyParser.json({ limit: '10mb' }));
app.use(bodyParser.urlencoded({ extended: true, limit: '10mb' }));
app.use(express.static('public'));

// Logging middleware
app.use((req, res, next) => {
    logger.log(`${req.method} ${req.path}`);
    next();
});

// Main endpoint that mimics HerikaServer's main.php
app.post('/', async (req, res) => {
    try {
        // Extract request data from the request body
        let requestType, speaker, target, message, additionalData;
        
        if (req.body.gameRequest) {
            // Original format: gameRequest array
            const gameRequest = req.body.gameRequest;
            requestType = gameRequest[0] || '';
            speaker = gameRequest[1] || '';
            target = gameRequest[2] || '';
            message = gameRequest[3] || '';
            additionalData = gameRequest[4] || '';
        } else if (req.body.type) {
            // Bridge format: structured object
            requestType = req.body.type || '';
            speaker = req.body.speaker || '';
            target = req.body.target || '';
            message = req.body.message || '';
            additionalData = req.body.extra || '';
        } else if (Array.isArray(req.body)) {
            // Direct array format
            requestType = req.body[0] || '';
            speaker = req.body[1] || '';
            target = req.body[2] || '';
            message = req.body[3] || '';
            additionalData = req.body[4] || '';
        } else {
            throw new Error('Invalid request format');
        }

        logger.log('Received request:', { requestType, speaker, target, message: message.substring(0, 100) });

        // Skip fast commands that don't need processing
        const fastCommands = [
            'addnpc', '_quest', 'setconf', 'request', '_speech', 
            'infoloc', 'infonpc', 'infonpc_close', 'infoaction', 
            'status_msg', 'delete_event', 'itemfound', '_questdata', 
            '_uquest', 'location', '_questreset'
        ];

        if (fastCommands.includes(requestType)) {
            logger.log(`Skipping fast command: ${requestType}`);
            return res.json({ success: true, message: 'Fast command processed' });
        }

        let response = null;

        // Route requests to appropriate modules
        switch (requestType) {
            case 'inputtext':
            case 'minai_roleplay':
            case 'minai_diary':
            case 'minai_diary_player':
                // Self-narrator functionality
                response = await narrator.processNarratorRequest({
                    type: requestType,
                    speaker,
                    target,
                    message,
                    additionalData
                });
                break;

            case 'minai_translate':
                // Translation functionality
                response = await translator.processTranslationRequest({
                    type: requestType,
                    speaker,
                    target,
                    message,
                    additionalData
                });
                break;

            case 'minai_dungeon_master':
                // Dungeonmaster functionality
                response = await dungeonmaster.processDungeonMasterRequest({
                    type: requestType,
                    speaker,
                    target,
                    message,
                    additionalData
                });
                break;

            case 'minai_combatendvictory':
            case 'minai_bleedoutself':
                // Combat/event narrator
                response = await narrator.processCombatEvent({
                    type: requestType,
                    speaker,
                    target,
                    message,
                    additionalData
                });
                break;

            default:
                logger.log(`Unknown request type: ${requestType}`);
                response = {
                    action: 'info',
                    target: speaker,
                    dialogue: `Unknown request type: ${requestType}`,
                    tts: null
                };
        }

        // Ensure response has the expected format
        if (!response) {
            response = {
                action: 'info',
                target: speaker,
                dialogue: 'No response generated',
                tts: null
            };
        }

        logger.log('Sending response:', response);
        res.json(response);

    } catch (error) {
        logger.error('Error processing request:', error);
        res.status(500).json({
            action: 'error',
            target: '',
            dialogue: 'Internal server error',
            tts: null
        });
    }
});

// Configuration API endpoints
app.get('/api/config', (req, res) => {
    res.json(config.getConfig());
});

app.post('/api/config', (req, res) => {
    try {
        config.updateConfig(req.body);
        res.json({ success: true, message: 'Configuration updated' });
    } catch (error) {
        logger.error('Error updating config:', error);
        res.status(500).json({ success: false, message: error.message });
    }
});

// Configuration web interface
app.get('/config', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'config.html'));
});

// Health check endpoint
app.get('/health', (req, res) => {
    res.json({
        status: 'healthy',
        timestamp: new Date().toISOString(),
        features: ['narrator', 'translation', 'dungeonmaster']
    });
});

// Index endpoint for compatibility
app.get('/api/main.php', (req, res) => {
    if (req.query.endpoint === 'index_payload') {
        res.json({
            nsfw: config.getConfig().nsfw_enabled || false,
            features: ['narrator', 'translation', 'dungeonmaster']
        });
    } else {
        res.json({ message: 'MinAI JavaScript Server' });
    }
});

// Start server
app.listen(PORT, () => {
    logger.log(`MinAI JavaScript server running on port ${PORT}`);
    logger.log(`Configuration: http://localhost:${PORT}/config`);
    logger.log(`Health check: http://localhost:${PORT}/health`);
    logger.log('Features: Self-Narrator, Translation, Dungeonmaster');
});

module.exports = app;