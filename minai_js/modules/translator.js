const aiApi = require('../utils/aiApi');
const config = require('../config/config');
const logger = require('../utils/logger');

class Translator {
    async processTranslationRequest(request) {
        try {
            if (!config.isFeatureEnabled('translation')) {
                logger.log('Translation is disabled');
                return null;
            }

            const { type, speaker, target, message, additionalData } = request;
            
            logger.log(`Processing translation request: ${type}`, { speaker, target });

            if (!message || message.trim() === '') {
                return {
                    action: 'error',
                    target: speaker || 'Player',
                    dialogue: 'No text provided for translation',
                    tts: null
                };
            }

            // Check if message needs translation
            if (!this.needsTranslation(message)) {
                logger.log('Message does not need translation');
                return {
                    action: 'translated_speech',
                    target: target || speaker || 'Player',
                    dialogue: message, // Return original message
                    tts: {
                        voice: config.getVoiceConfig().player_voice,
                        text: message
                    }
                };
            }

            // Get custom prompt if configured
            const customPrompt = config.getCustomPrompt('translation');
            
            // Generate translated response
            const translatedText = await aiApi.generateTranslation(message, customPrompt);
            
            if (!translatedText) {
                throw new Error('No translation generated from AI');
            }

            // Get voice configuration
            const voiceConfig = config.getVoiceConfig();
            
            const response = {
                action: 'translated_speech',
                target: target || speaker || 'Player',
                dialogue: translatedText,
                tts: {
                    voice: voiceConfig.player_voice,
                    text: translatedText
                },
                original: message // Include original for reference
            };

            logger.log('Translation completed successfully');
            return response;

        } catch (error) {
            logger.error('Error in translation processing:', error);
            return {
                action: 'error',
                target: request.target || request.speaker || 'Player',
                dialogue: `Translation error: ${error.message}`,
                tts: null
            };
        }
    }

    // Determine if text needs translation from casual to character speech
    needsTranslation(text) {
        const casualMarkers = [
            // Modern slang and expressions
            'lol', 'lmao', 'wtf', 'omg', 'tbh', 'ngl', 'fr', 'imo', 'imho',
            'yeah', 'yep', 'nah', 'nope', 'gonna', 'wanna', 'gotta',
            'dunno', 'kinda', 'sorta', 'prob', 'def',
            
            // Modern contractions (some are fine, but excessive use indicates casual speech)
            "i'm", "you're", "we're", "they're", "it's", "that's", "what's",
            "don't", "won't", "can't", "shouldn't", "wouldn't", "couldn't",
            
            // Very casual phrases
            'sup', 'hey', 'yo', 'dude', 'bro', 'guys', 
            'cool', 'awesome', 'sweet', 'nice', 'sick',
            'whatever', 'anyways', 'like', 'totally',
            
            // Question markers that indicate very casual speech
            'right?', 'you know?', 'know what i mean?'
        ];

        const lowerText = text.toLowerCase();
        
        // Check for casual markers
        const casualCount = casualMarkers.filter(marker => 
            lowerText.includes(marker)
        ).length;
        
        // Check for excessive punctuation or caps
        const excessivePunctuation = /[!]{2,}|[?]{2,}/.test(text);
        const excessiveCaps = /[A-Z]{3,}/.test(text) && text !== text.toUpperCase();
        
        // Check for very short responses that might be too casual
        const veryShort = text.trim().split(' ').length <= 2 && 
                         ['k', 'ok', 'sure', 'fine', 'yes', 'no', 'maybe'].includes(lowerText.trim());
        
        // Needs translation if it has casual markers or other informal indicators
        return casualCount > 0 || excessivePunctuation || excessiveCaps || veryShort;
    }

    // Get context-aware translation prompt
    getContextualTranslationPrompt(context = {}) {
        const basePrompt = config.getCustomPrompt('translation') || aiApi.getDefaultTranslationPrompt();
        
        // Add context-specific instructions
        let contextualPrompt = basePrompt;
        
        if (context.inCombat) {
            contextualPrompt += " The character is in combat, so the dialogue should be urgent and battle-appropriate.";
        }
        
        if (context.isIntimate) {
            contextualPrompt += " The character is in an intimate or private setting, so the dialogue should be more personal and emotional.";
        }
        
        if (context.target) {
            contextualPrompt += ` The character is speaking to ${context.target}.`;
        }
        
        return contextualPrompt;
    }

    // Helper method to detect context from the game state
    detectContext(request) {
        const context = {};
        
        // Simple context detection based on request data
        if (request.additionalData) {
            try {
                const data = typeof request.additionalData === 'string' 
                    ? JSON.parse(request.additionalData) 
                    : request.additionalData;
                    
                context.inCombat = data.inCombat || false;
                context.isIntimate = data.isIntimate || false;
                context.location = data.location || '';
            } catch (e) {
                // Ignore parsing errors
            }
        }
        
        context.target = request.target;
        
        return context;
    }

    // Method to handle batch translation requests (if needed)
    async translateMultiple(messages) {
        const results = [];
        
        for (const message of messages) {
            try {
                const result = await this.processTranslationRequest({
                    type: 'minai_translate',
                    speaker: 'Player',
                    target: '',
                    message: message,
                    additionalData: ''
                });
                results.push(result);
            } catch (error) {
                logger.error('Error in batch translation:', error);
                results.push({
                    action: 'error',
                    dialogue: `Translation failed: ${error.message}`,
                    original: message
                });
            }
        }
        
        return results;
    }
}

module.exports = new Translator();