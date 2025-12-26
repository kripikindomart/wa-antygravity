const {
    makeWASocket,
    useMultiFileAuthState,
    DisconnectReason,
    fetchLatestBaileysVersion,
    makeCacheableSignalKeyStore,
    isJidBroadcast,
} = require('@whiskeysockets/baileys');
const pino = require('pino');
const fs = require('fs');
const path = require('path');
const axios = require('axios');
const QRCode = require('qrcode');

// Store active sessions
const sessions = new Map();
const qrCodes = new Map();

const SESSION_DIR = process.env.SESSION_DIR || './sessions';

// Ensure session directory exists
if (!fs.existsSync(SESSION_DIR)) {
    fs.mkdirSync(SESSION_DIR, { recursive: true });
}

const logger = pino({ level: 'silent' });

/**
 * Send webhook to Laravel
 */
async function sendWebhook(event, data) {
    try {
        const webhookUrl = process.env.LARAVEL_WEBHOOK_URL;
        if (!webhookUrl) return;

        await axios.post(webhookUrl, {
            event,
            data,
            timestamp: new Date().toISOString(),
        }, {
            headers: {
                'Content-Type': 'application/json',
                'X-API-Key': process.env.LARAVEL_API_KEY,
            },
            timeout: 10000,
        });
    } catch (error) {
        console.error('Webhook error:', error.message);
    }
}

/**
 * Create a new WhatsApp session
 */
async function createSession(sessionId) {
    const sessionPath = path.join(SESSION_DIR, sessionId);
    
    const { state, saveCreds } = await useMultiFileAuthState(sessionPath);
    const { version } = await fetchLatestBaileysVersion();

    const socket = makeWASocket({
        version,
        logger,
        printQRInTerminal: false,
        auth: {
            creds: state.creds,
            keys: makeCacheableSignalKeyStore(state.keys, logger),
        },
        generateHighQualityLinkPreview: true,
        getMessage: async () => undefined,
    });

    // Handle connection updates
    socket.ev.on('connection.update', async (update) => {
        const { connection, lastDisconnect, qr } = update;

        if (qr) {
            // Generate QR code as data URL
            const qrDataUrl = await QRCode.toDataURL(qr);
            qrCodes.set(sessionId, qrDataUrl);
            
            await sendWebhook('qr.update', {
                sessionId,
                qr: qrDataUrl,
            });
        }

        if (connection === 'close') {
            const statusCode = lastDisconnect?.error?.output?.statusCode;
            const shouldReconnect = statusCode !== DisconnectReason.loggedOut;
            
            console.log(`Session ${sessionId} closed. Reconnect: ${shouldReconnect}`);
            
            if (shouldReconnect) {
                // Attempt to reconnect
                setTimeout(() => createSession(sessionId), 3000);
            } else {
                // Session logged out, clean up
                sessions.delete(sessionId);
                qrCodes.delete(sessionId);
                
                await sendWebhook('connection.update', {
                    sessionId,
                    status: 'disconnected',
                    reason: 'logged_out',
                });
            }
        }

        if (connection === 'open') {
            console.log(`Session ${sessionId} connected!`);
            qrCodes.delete(sessionId);
            
            const phoneNumber = socket.user?.id?.split(':')[0] || socket.user?.id?.split('@')[0];
            
            await sendWebhook('connection.update', {
                sessionId,
                status: 'connected',
                phoneNumber,
                name: socket.user?.name,
            });
        }
    });

    // Handle incoming messages
    socket.ev.on('messages.upsert', async ({ messages, type }) => {
        console.log(`[DEBUG] Received ${messages.length} messages. Type: ${type}`);
        
        if (type !== 'notify') return;

        for (const msg of messages) {
            console.log('[DEBUG] Processing message:', JSON.stringify(msg.key));
            
            if (msg.key.fromMe || isJidBroadcast(msg.key.remoteJid)) {
                console.log('[DEBUG] Skipped: fromMe or Broadcast');
                continue;
            }

            const messageContent = msg.message?.conversation || 
                                   msg.message?.extendedTextMessage?.text ||
                                   msg.message?.imageMessage?.caption ||
                                   '';
            
            // Prioritize remoteJidAlt (e.g. phone number) over remoteJid (which might be LID)
            const fromJid = msg.key.remoteJidAlt || msg.key.remoteJid;
            
            console.log(`[DEBUG] Content: ${messageContent} From: ${fromJid} (Original: ${msg.key.remoteJid})`);

            await sendWebhook('message.received', {
                sessionId,
                from: fromJid,
                fromName: msg.pushName,
                message: messageContent,
                type: Object.keys(msg.message || {})[0] || 'unknown',
                messageId: msg.key.id,
                timestamp: msg.messageTimestamp,
            });
            
            console.log('[DEBUG] Webhook sent.');
        }
    });

    // Save credentials on update
    socket.ev.on('creds.update', saveCreds);

    sessions.set(sessionId, socket);
    return socket;
}

/**
 * Get existing session or create new one
 */
async function getSession(sessionId) {
    if (sessions.has(sessionId)) {
        return sessions.get(sessionId);
    }
    return await createSession(sessionId);
}

/**
 * Get QR code for session
 */
function getQRCode(sessionId) {
    return qrCodes.get(sessionId);
}

/**
 * Check if session is connected
 */
function isConnected(sessionId) {
    const session = sessions.get(sessionId);
    return session?.user !== undefined;
}

/**
 * Get session info
 */
function getSessionInfo(sessionId) {
    const session = sessions.get(sessionId);
    if (!session) return null;

    return {
        sessionId,
        connected: session.user !== undefined,
        phoneNumber: session.user?.id?.split(':')[0] || session.user?.id?.split('@')[0],
        name: session.user?.name,
    };
}

/**
 * Send text message
 */
async function sendMessage(sessionId, to, message, options = {}) {
    const session = sessions.get(sessionId);
    if (!session) {
        throw new Error('Session not found');
    }

    // Format phone number for WhatsApp
    let jid = to.replace(/[^0-9]/g, '');
    if (!jid.endsWith('@s.whatsapp.net')) {
        jid = `${jid}@s.whatsapp.net`;
    }

    const messageContent = { text: message };
    
    const result = await session.sendMessage(jid, messageContent);
    return result;
}

/**
 * Send image message
 */
async function sendImage(sessionId, to, imagePath, caption = '') {
    const session = sessions.get(sessionId);
    if (!session) {
        throw new Error('Session not found');
    }

    let jid = to.replace(/[^0-9]/g, '');
    if (!jid.endsWith('@s.whatsapp.net')) {
        jid = `${jid}@s.whatsapp.net`;
    }

    const messageContent = {
        image: { url: imagePath },
        caption,
    };

    const result = await session.sendMessage(jid, messageContent);
    return result;
}

/**
 * Send document
 */
async function sendDocument(sessionId, to, filePath, fileName, caption = '') {
    const session = sessions.get(sessionId);
    if (!session) {
        throw new Error('Session not found');
    }

    let jid = to.replace(/[^0-9]/g, '');
    if (!jid.endsWith('@s.whatsapp.net')) {
        jid = `${jid}@s.whatsapp.net`;
    }

    const messageContent = {
        document: { url: filePath },
        fileName,
        caption,
    };

    const result = await session.sendMessage(jid, messageContent);
    return result;
}

/**
 * Delete/logout session
 */
async function deleteSession(sessionId) {
    const session = sessions.get(sessionId);
    if (session) {
        await session.logout();
        sessions.delete(sessionId);
    }
    
    qrCodes.delete(sessionId);
    
    // Delete session files
    const sessionPath = path.join(SESSION_DIR, sessionId);
    if (fs.existsSync(sessionPath)) {
        fs.rmSync(sessionPath, { recursive: true, force: true });
    }

    await sendWebhook('connection.update', {
        sessionId,
        status: 'disconnected',
        reason: 'manual_logout',
    });
}

/**
 * Get all active sessions
 */
function getAllSessions() {
    const result = [];
    for (const [sessionId, session] of sessions) {
        result.push({
            sessionId,
            connected: session.user !== undefined,
            phoneNumber: session.user?.id?.split(':')[0],
            name: session.user?.name,
        });
    }
    return result;
}

module.exports = {
    createSession,
    getSession,
    getQRCode,
    isConnected,
    getSessionInfo,
    sendMessage,
    sendImage,
    sendDocument,
    deleteSession,
    getAllSessions,
};
