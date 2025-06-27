const aiApi = require('../utils/aiApi');
const config = require('../config/config');
const logger = require('../utils/logger');

class DungeonMaster {
    async processDungeonMasterRequest(request) {
        try {
            if (!config.isFeatureEnabled('dungeonmaster')) {
                logger.log('Dungeonmaster is disabled');
                return null;
            }

            const { type, speaker, target, message, additionalData } = request;
            
            logger.log(`Processing dungeonmaster request: ${type}`, { speaker, target });

            if (!message || message.trim() === '') {
                return {
                    action: 'error',
                    target: 'all',
                    dialogue: 'No scenario provided for dungeonmaster',
                    tts: null
                };
            }

            // Get custom prompt if configured
            const customPrompt = config.getCustomPrompt('dungeonmaster');
            
            // Process the scenario to add context
            const enhancedScenario = this.enhanceScenario(message, {
                speaker,
                target,
                additionalData
            });

            // Generate dungeonmaster response
            const dmResponse = await aiApi.generateDungeonMasterResponse(enhancedScenario, customPrompt);
            
            if (!dmResponse) {
                throw new Error('No response generated from AI');
            }

            // Format the response for game world integration
            const formattedResponse = this.formatDungeonMasterResponse(dmResponse);
            
            const response = {
                action: 'dungeonmaster_event',
                target: 'all', // DM events affect all NPCs
                dialogue: formattedResponse,
                tts: {
                    voice: 'narrator', // Use narrator voice for DM events
                    text: formattedResponse
                },
                broadcast: true, // Flag to indicate this should be broadcast to all NPCs
                scenario: message // Original scenario for reference
            };

            logger.log('Dungeonmaster response generated successfully');
            return response;

        } catch (error) {
            logger.error('Error in dungeonmaster processing:', error);
            return {
                action: 'error',
                target: 'all',
                dialogue: `Dungeonmaster error: ${error.message}`,
                tts: null
            };
        }
    }

    // Enhance the scenario with additional context
    enhanceScenario(scenario, context) {
        let enhancedScenario = scenario;
        
        // Add character context if available
        const characterConfig = config.get('character');
        if (characterConfig) {
            const playerInfo = `The player character is ${characterConfig.player_name}, a ${characterConfig.player_gender} ${characterConfig.player_race}.`;
            enhancedScenario = `${playerInfo} ${enhancedScenario}`;
        }
        
        // Add speaker context
        if (context.speaker && context.speaker !== 'Player') {
            enhancedScenario += ` (This involves ${context.speaker})`;
        }
        
        // Add target context
        if (context.target && context.target !== context.speaker && context.target !== 'Player') {
            enhancedScenario += ` (This affects ${context.target})`;
        }
        
        // Add location/time context if available
        if (context.additionalData) {
            try {
                const data = typeof context.additionalData === 'string' 
                    ? JSON.parse(context.additionalData) 
                    : context.additionalData;
                    
                if (data.location) {
                    enhancedScenario += ` (Location: ${data.location})`;
                }
                
                if (data.time) {
                    enhancedScenario += ` (Time: ${data.time})`;
                }
            } catch (e) {
                // Ignore parsing errors
            }
        }
        
        return enhancedScenario;
    }

    // Format the DM response to be game-world appropriate
    formatDungeonMasterResponse(response) {
        // Ensure the response is written in a way that NPCs will treat as canonical
        let formatted = response.trim();
        
        // Remove any quotes or meta-commentary
        formatted = formatted.replace(/^"(.*)"$/, '$1');
        formatted = formatted.replace(/\(OOC:.*?\)/gi, '');
        formatted = formatted.replace(/\*.*?\*/g, '');
        
        // Ensure it's written in third person or as an omniscient narrator
        if (formatted.toLowerCase().startsWith('i ') || formatted.toLowerCase().includes(' i ')) {
            // Convert first person to third person if needed
            const playerName = config.get('character.player_name') || 'the player';
            formatted = formatted.replace(/\bi\b/gi, playerName);
            formatted = formatted.replace(/\bme\b/gi, playerName);
            formatted = formatted.replace(/\bmy\b/gi, `${playerName}'s`);
        }
        
        // Ensure it starts with a capital letter and ends with proper punctuation
        if (formatted.length > 0) {
            formatted = formatted.charAt(0).toUpperCase() + formatted.slice(1);
            if (!/[.!?]$/.test(formatted)) {
                formatted += '.';
            }
        }
        
        return formatted;
    }

    // Generate world state changes based on DM events
    generateWorldStateChanges(dmEvent) {
        const changes = {
            globalEffects: [],
            characterEffects: [],
            locationEffects: [],
            itemEffects: []
        };
        
        // Analyze the DM event for potential world changes
        const lowerEvent = dmEvent.toLowerCase();
        
        // Weather/environmental changes
        if (lowerEvent.includes('rain') || lowerEvent.includes('storm')) {
            changes.globalEffects.push('weather_change_rainy');
        }
        if (lowerEvent.includes('sunny') || lowerEvent.includes('clear')) {
            changes.globalEffects.push('weather_change_clear');
        }
        
        // Time changes
        if (lowerEvent.includes('night') || lowerEvent.includes('evening')) {
            changes.globalEffects.push('time_change_night');
        }
        if (lowerEvent.includes('morning') || lowerEvent.includes('dawn')) {
            changes.globalEffects.push('time_change_morning');
        }
        
        // Character state changes
        if (lowerEvent.includes('injured') || lowerEvent.includes('hurt')) {
            changes.characterEffects.push('player_injured');
        }
        if (lowerEvent.includes('healed') || lowerEvent.includes('recovered')) {
            changes.characterEffects.push('player_healed');
        }
        
        // Location changes
        if (lowerEvent.includes('arrived') || lowerEvent.includes('entered')) {
            changes.locationEffects.push('location_entered');
        }
        if (lowerEvent.includes('left') || lowerEvent.includes('departed')) {
            changes.locationEffects.push('location_exited');
        }
        
        return changes;
    }

    // Method to handle complex DM scenarios with multiple parts
    async processComplexScenario(scenario) {
        // Split complex scenarios into manageable parts
        const parts = scenario.split(/[.!?]+/).filter(part => part.trim().length > 0);
        
        if (parts.length <= 1) {
            // Simple scenario, process normally
            return await this.processDungeonMasterRequest({
                type: 'minai_dungeon_master',
                speaker: 'DungeonMaster',
                target: 'all',
                message: scenario,
                additionalData: ''
            });
        }
        
        // Complex scenario, process in parts and combine
        const responses = [];
        
        for (const part of parts) {
            try {
                const response = await this.processDungeonMasterRequest({
                    type: 'minai_dungeon_master',
                    speaker: 'DungeonMaster',
                    target: 'all',
                    message: part.trim(),
                    additionalData: ''
                });
                
                if (response && response.dialogue) {
                    responses.push(response.dialogue);
                }
            } catch (error) {
                logger.error('Error processing scenario part:', error);
            }
        }
        
        // Combine responses
        const combinedResponse = responses.join(' ');
        
        return {
            action: 'dungeonmaster_event',
            target: 'all',
            dialogue: combinedResponse,
            tts: {
                voice: 'narrator',
                text: combinedResponse
            },
            broadcast: true,
            scenario: scenario
        };
    }
}

module.exports = new DungeonMaster();