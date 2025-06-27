const axios = require('axios');
const config = require('../config/config');
const logger = require('./logger');

class AIApi {
    async callAI(prompt, systemPrompt = null, options = {}) {
        const apiConfig = config.getApiConfig();
        
        if (!apiConfig.api_key) {
            throw new Error('API key not configured');
        }

        const messages = [];
        
        if (systemPrompt) {
            messages.push({
                role: 'system',
                content: systemPrompt
            });
        }
        
        messages.push({
            role: 'user',
            content: prompt
        });

        const requestData = {
            model: apiConfig.model,
            messages: messages,
            temperature: options.temperature || 0.7,
            max_tokens: options.max_tokens || 500,
            top_p: options.top_p || 0.9
        };

        try {
            logger.debug('Calling AI API:', { 
                url: apiConfig.url, 
                model: apiConfig.model,
                promptLength: prompt.length 
            });

            const response = await axios.post(apiConfig.url, requestData, {
                headers: {
                    'Authorization': `Bearer ${apiConfig.api_key}`,
                    'Content-Type': 'application/json',
                    'HTTP-Referer': 'https://minai-js.local',
                    'X-Title': 'MinAI JavaScript'
                },
                timeout: 30000
            });

            if (response.data && response.data.choices && response.data.choices[0]) {
                const aiResponse = response.data.choices[0].message.content.trim();
                logger.debug('AI Response received:', { 
                    responseLength: aiResponse.length,
                    tokensUsed: response.data.usage?.total_tokens || 'unknown'
                });
                return aiResponse;
            } else {
                throw new Error('Invalid response format from AI API');
            }

        } catch (error) {
            if (error.response) {
                logger.error('AI API Error:', {
                    status: error.response.status,
                    statusText: error.response.statusText,
                    data: error.response.data
                });
                throw new Error(`AI API Error: ${error.response.status} - ${error.response.statusText}`);
            } else if (error.request) {
                logger.error('AI API Network Error:', error.message);
                throw new Error('Network error calling AI API');
            } else {
                logger.error('AI API Setup Error:', error.message);
                throw new Error('Error setting up AI API request');
            }
        }
    }

    // Specific method for narrator responses
    async generateNarratorResponse(situation, customPrompt = null) {
        const systemPrompt = customPrompt || this.getDefaultNarratorPrompt();
        
        const prompt = `Current situation: ${situation}

Generate a first-person internal thought response as the player character. Focus on immediate thoughts, feelings, and reactions to the current situation.`;

        return await this.callAI(prompt, systemPrompt, { 
            temperature: 0.8,
            max_tokens: 300 
        });
    }

    // Specific method for translation responses
    async generateTranslation(originalText, customPrompt = null) {
        const systemPrompt = customPrompt || this.getDefaultTranslationPrompt();
        
        const prompt = `Original text: "${originalText}"

Translate this casual speech into character-appropriate dialogue while maintaining the same meaning and intent.`;

        return await this.callAI(prompt, systemPrompt, { 
            temperature: 0.6,
            max_tokens: 200 
        });
    }

    // Specific method for dungeonmaster responses
    async generateDungeonMasterResponse(scenario, customPrompt = null) {
        const systemPrompt = customPrompt || this.getDefaultDungeonMasterPrompt();
        
        const prompt = `Scenario: ${scenario}

Generate a response that all NPCs in the game will treat as if it actually happened. Describe the event or situation in a way that becomes part of the game world.`;

        return await this.callAI(prompt, systemPrompt, { 
            temperature: 0.7,
            max_tokens: 400 
        });
    }

    getDefaultNarratorPrompt() {
        return `You are the internal voice of a player character in Skyrim. Generate first-person thoughts and reactions that reflect the character's immediate mental state. Be immersive, personal, and focus on what the character is thinking and feeling in the moment. Keep responses concise but meaningful.`;
    }

    getDefaultTranslationPrompt() {
        return `You are a dialogue translator for a fantasy RPG character. Convert casual, modern speech into character-appropriate dialogue that fits the fantasy setting while preserving the original meaning and intent. The translation should sound natural for the character and setting.`;
    }

    getDefaultDungeonMasterPrompt() {
        return `You are a dungeon master in a Skyrim roleplay session. Generate narrative events and descriptions that NPCs will treat as canonical events in the game world. Your responses should describe situations, events, or changes that become part of the shared game reality.`;
    }
}

module.exports = new AIApi();