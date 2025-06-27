const fs = require('fs');
const path = require('path');

const LOG_DIR = path.join(__dirname, '..', 'logs');
const LOG_FILE = path.join(LOG_DIR, 'minai.log');

// Ensure log directory exists
if (!fs.existsSync(LOG_DIR)) {
    fs.mkdirSync(LOG_DIR, { recursive: true });
}

class Logger {
    constructor() {
        this.logLevel = process.env.LOG_LEVEL || 'info';
    }

    formatMessage(level, message, data = null) {
        const timestamp = new Date().toISOString();
        let logMessage = `[${timestamp}] [${level.toUpperCase()}] ${message}`;
        
        if (data) {
            logMessage += ` ${JSON.stringify(data)}`;
        }
        
        return logMessage;
    }

    writeToFile(message) {
        try {
            fs.appendFileSync(LOG_FILE, message + '\n');
        } catch (error) {
            console.error('Failed to write to log file:', error);
        }
    }

    log(message, data = null) {
        const formattedMessage = this.formatMessage('info', message, data);
        console.log(formattedMessage);
        this.writeToFile(formattedMessage);
    }

    error(message, data = null) {
        const formattedMessage = this.formatMessage('error', message, data);
        console.error(formattedMessage);
        this.writeToFile(formattedMessage);
    }

    warn(message, data = null) {
        const formattedMessage = this.formatMessage('warn', message, data);
        console.warn(formattedMessage);
        this.writeToFile(formattedMessage);
    }

    debug(message, data = null) {
        if (this.logLevel === 'debug') {
            const formattedMessage = this.formatMessage('debug', message, data);
            console.log(formattedMessage);
            this.writeToFile(formattedMessage);
        }
    }

    // Clear old logs (keep last 100 entries)
    rotateLogs() {
        try {
            if (fs.existsSync(LOG_FILE)) {
                const logs = fs.readFileSync(LOG_FILE, 'utf8').split('\n');
                if (logs.length > 100) {
                    const recentLogs = logs.slice(-100);
                    fs.writeFileSync(LOG_FILE, recentLogs.join('\n'));
                }
            }
        } catch (error) {
            console.error('Failed to rotate logs:', error);
        }
    }
}

module.exports = new Logger();