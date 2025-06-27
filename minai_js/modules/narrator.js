const aiApi = require('../utils/aiApi');
const config = require('../config/config');
const logger = require('../utils/logger');

class Narrator {
    async processNarratorRequest(request) {
        try {
            if (!config.isFeatureEnabled('self_narrator')) {
                logger.log('Self-narrator is disabled');
                return null;
            }

            const { type, speaker, target, message, additionalData } = request;
            
            logger.log(`Processing narrator request: ${type}`, { speaker, target });

            // Get custom prompt if configured
            const customPrompt = config.getCustomPrompt('narrator');
            
            let situation = '';
            let responseType = 'thought';

            switch (type) {
                case 'inputtext':
                case 'minai_roleplay':
                    situation = `The player just said or did: "${message}"`;
                    break;
                    
                case 'minai_diary':
                case 'minai_diary_player':
                    situation = `The player is reflecting on recent events: "${message}"`;
                    responseType = 'reflection';
                    break;
                    
                default:
                    situation = `Current situation: ${message || 'The player is experiencing something in the game'}`;
            }

            // Add speaker context if available
            if (speaker && speaker !== 'Player' && speaker !== '') {
                situation += ` (involving ${speaker})`;
            }

            // Add target context if available
            if (target && target !== speaker && target !== '') {
                situation += ` (directed at ${target})`;
            }

            // Generate narrator response
            const narratorResponse = await aiApi.generateNarratorResponse(situation, customPrompt);
            
            if (!narratorResponse) {
                throw new Error('No response generated from AI');
            }

            // Get voice configuration
            const voiceConfig = config.getVoiceConfig();
            
            const response = {
                action: responseType === 'reflection' ? 'diary_entry' : 'narrator_thought',
                target: speaker || 'Player',
                dialogue: narratorResponse,
                tts: {
                    voice: voiceConfig.narrator_voice,
                    text: narratorResponse
                }
            };

            logger.log('Narrator response generated successfully');
            return response;

        } catch (error) {
            logger.error('Error in narrator processing:', error);
            return {
                action: 'error',
                target: request.speaker || 'Player',
                dialogue: 'Unable to generate narrator response',
                tts: null
            };
        }
    }

    async processCombatEvent(request) {
        try {
            if (!config.isFeatureEnabled('self_narrator')) {
                return null;
            }

            const { type, speaker, target, message } = request;
            
            logger.log(`Processing combat event: ${type}`, { speaker, target });

            let situation = '';
            
            switch (type) {
                case 'minai_combatendvictory':
                    situation = `The player has just won a combat encounter. ${message || 'Victory is achieved.'}`;
                    break;
                    
                case 'minai_bleedoutself':
                    situation = `The player is bleeding out or near death. ${message || 'The situation is dire.'}`;
                    break;
                    
                default:
                    situation = `Combat event: ${message || 'Something happened in combat'}`;
            }

            // Get custom prompt or use combat-specific prompt
            const customPrompt = config.getCustomPrompt('narrator') || this.getCombatNarratorPrompt();
            
            const narratorResponse = await aiApi.generateNarratorResponse(situation, customPrompt);
            
            const voiceConfig = config.getVoiceConfig();
            
            const response = {
                action: 'combat_narrator',
                target: speaker || 'Player',
                dialogue: narratorResponse,
                tts: {
                    voice: voiceConfig.narrator_voice,
                    text: narratorResponse
                }
            };

            logger.log('Combat narrator response generated successfully');
            return response;

        } catch (error) {
            logger.error('Error in combat narrator processing:', error);
            return {
                action: 'error',
                target: request.speaker || 'Player',
                dialogue: 'Unable to generate combat narrator response',
                tts: null
            };
        }
    }

    getCombatNarratorPrompt() {
        return `You are the internal voice of a player character during or after combat in Skyrim. Generate intense, adrenaline-filled first-person thoughts that reflect the immediacy of battle, victory, or defeat. Focus on physical sensations, tactical thoughts, and emotional responses to combat situations. Keep responses visceral and in-the-moment.`;
    }

    // Helper method to determine if the situation is appropriate for narrator
    shouldGenerateNarratorResponse(request) {
        const { type, message } = request;
        
        // Skip empty messages
        if (!message || message.trim() === '') {
            return false;
        }
        
        // Skip very short messages that don't need narration
        if (message.length < 3) {
            return false;
        }
        
        // Skip system messages
        if (message.startsWith('[') && message.endsWith(']')) {
            return false;
        }
        
        return true;
    }
}

module.exports = new Narrator();