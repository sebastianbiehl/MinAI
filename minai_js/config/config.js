const fs = require('fs');
const path = require('path');

const CONFIG_FILE = path.join(__dirname, 'user_config.json');

// Default configuration
const defaultConfig = {
    // API Configuration
    api: {
        provider: 'openrouter',
        openrouter: {
            url: 'https://openrouter.ai/api/v1/chat/completions',
            model: 'google/gemma-2-9b-it:free',
            api_key: ''
        },
        openai: {
            url: 'https://api.openai.com/v1/chat/completions',
            model: 'gpt-3.5-turbo',
            api_key: ''
        }
    },

    // Voice Configuration
    voice: {
        narrator_voice: 'dragon',
        player_voice: 'femaleeventoned'
    },

    // Feature Configuration
    features: {
        self_narrator: true,
        translation: true,
        dungeonmaster: true,
        nsfw_enabled: false
    },

    // Custom Prompts
    prompts: {
        narrator_prompt: '',
        translation_prompt: '',
        dungeonmaster_prompt: ''
    },

    // Character Configuration
    character: {
        player_name: 'Player',
        player_race: 'Nord',
        player_gender: 'Female'
    },

    // Context Settings
    context: {
        max_context_messages: 10,
        include_location: true,
        include_time: true
    }
};

class Config {
    constructor() {
        this.config = this.loadConfig();
    }

    loadConfig() {
        try {
            if (fs.existsSync(CONFIG_FILE)) {
                const userConfig = JSON.parse(fs.readFileSync(CONFIG_FILE, 'utf8'));
                return this.mergeConfig(defaultConfig, userConfig);
            }
        } catch (error) {
            console.error('Error loading config:', error);
        }
        return { ...defaultConfig };
    }

    mergeConfig(defaultConf, userConf) {
        const merged = { ...defaultConf };
        
        for (const [key, value] of Object.entries(userConf)) {
            if (typeof value === 'object' && !Array.isArray(value) && value !== null) {
                merged[key] = this.mergeConfig(defaultConf[key] || {}, value);
            } else {
                merged[key] = value;
            }
        }
        
        return merged;
    }

    saveConfig() {
        try {
            fs.writeFileSync(CONFIG_FILE, JSON.stringify(this.config, null, 2));
            return true;
        } catch (error) {
            console.error('Error saving config:', error);
            return false;
        }
    }

    getConfig() {
        return { ...this.config };
    }

    updateConfig(updates) {
        this.config = this.mergeConfig(this.config, updates);
        return this.saveConfig();
    }

    get(path) {
        const keys = path.split('.');
        let current = this.config;
        
        for (const key of keys) {
            if (current[key] === undefined) {
                return undefined;
            }
            current = current[key];
        }
        
        return current;
    }

    set(path, value) {
        const keys = path.split('.');
        let current = this.config;
        
        for (let i = 0; i < keys.length - 1; i++) {
            const key = keys[i];
            if (typeof current[key] !== 'object') {
                current[key] = {};
            }
            current = current[key];
        }
        
        current[keys[keys.length - 1]] = value;
        return this.saveConfig();
    }

    // Helper methods for specific configurations
    getApiConfig() {
        const provider = this.get('api.provider');
        return this.get(`api.${provider}`);
    }

    getVoiceConfig() {
        return this.get('voice');
    }

    getFeaturesConfig() {
        return this.get('features');
    }

    getPromptsConfig() {
        return this.get('prompts');
    }

    isFeatureEnabled(feature) {
        return this.get(`features.${feature}`) === true;
    }

    getCustomPrompt(type) {
        const customPrompt = this.get(`prompts.${type}_prompt`);
        return customPrompt && customPrompt.trim() !== '' ? customPrompt : null;
    }
}

module.exports = new Config();