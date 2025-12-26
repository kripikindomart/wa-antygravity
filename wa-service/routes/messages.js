const express = require('express');
const router = express.Router();
const whatsapp = require('../services/whatsapp');

/**
 * POST /messages/send
 * Send a text message
 */
router.post('/send', async (req, res) => {
    try {
        const { sessionId, to, message } = req.body;
        
        if (!sessionId || !to || !message) {
            return res.status(400).json({
                error: 'sessionId, to, and message are required',
            });
        }

        if (!whatsapp.isConnected(sessionId)) {
            return res.status(400).json({
                error: 'Session not connected',
            });
        }

        const result = await whatsapp.sendMessage(sessionId, to, message);
        
        res.json({
            success: true,
            messageId: result.key.id,
            to: result.key.remoteJid,
        });
    } catch (error) {
        console.error('Error sending message:', error);
        res.status(500).json({ error: error.message });
    }
});

/**
 * POST /messages/send-image
 * Send an image message
 */
router.post('/send-image', async (req, res) => {
    try {
        const { sessionId, to, imageUrl, caption } = req.body;
        
        if (!sessionId || !to || !imageUrl) {
            return res.status(400).json({
                error: 'sessionId, to, and imageUrl are required',
            });
        }

        if (!whatsapp.isConnected(sessionId)) {
            return res.status(400).json({
                error: 'Session not connected',
            });
        }

        const result = await whatsapp.sendImage(sessionId, to, imageUrl, caption || '');
        
        res.json({
            success: true,
            messageId: result.key.id,
            to: result.key.remoteJid,
        });
    } catch (error) {
        console.error('Error sending image:', error);
        res.status(500).json({ error: error.message });
    }
});

/**
 * POST /messages/send-document
 * Send a document
 */
router.post('/send-document', async (req, res) => {
    try {
        const { sessionId, to, documentUrl, fileName, caption } = req.body;
        
        if (!sessionId || !to || !documentUrl) {
            return res.status(400).json({
                error: 'sessionId, to, and documentUrl are required',
            });
        }

        if (!whatsapp.isConnected(sessionId)) {
            return res.status(400).json({
                error: 'Session not connected',
            });
        }

        const result = await whatsapp.sendDocument(
            sessionId,
            to,
            documentUrl,
            fileName || 'document',
            caption || ''
        );
        
        res.json({
            success: true,
            messageId: result.key.id,
            to: result.key.remoteJid,
        });
    } catch (error) {
        console.error('Error sending document:', error);
        res.status(500).json({ error: error.message });
    }
});

/**
 * POST /messages/send-bulk
 * Send bulk messages with delay
 */
router.post('/send-bulk', async (req, res) => {
    try {
        const { sessionId, recipients, message, delayMs = 5000 } = req.body;
        
        if (!sessionId || !recipients || !message) {
            return res.status(400).json({
                error: 'sessionId, recipients (array), and message are required',
            });
        }

        if (!whatsapp.isConnected(sessionId)) {
            return res.status(400).json({
                error: 'Session not connected',
            });
        }

        // Start sending in background
        const results = [];
        
        (async () => {
            for (const recipient of recipients) {
                try {
                    const result = await whatsapp.sendMessage(sessionId, recipient, message);
                    results.push({
                        to: recipient,
                        success: true,
                        messageId: result.key.id,
                    });
                } catch (err) {
                    results.push({
                        to: recipient,
                        success: false,
                        error: err.message,
                    });
                }
                
                // Wait between messages
                if (delayMs > 0) {
                    await new Promise(resolve => setTimeout(resolve, delayMs));
                }
            }
        })();

        res.json({
            success: true,
            message: `Sending ${recipients.length} messages in background`,
            totalRecipients: recipients.length,
        });
    } catch (error) {
        console.error('Error sending bulk:', error);
        res.status(500).json({ error: error.message });
    }
});

module.exports = router;
