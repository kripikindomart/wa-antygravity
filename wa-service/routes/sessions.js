const express = require('express');
const router = express.Router();
const whatsapp = require('../services/whatsapp');

/**
 * POST /sessions/add
 * Create a new WhatsApp session
 */
router.post('/add', async (req, res) => {
    try {
        const { sessionId } = req.body;
        
        if (!sessionId) {
            return res.status(400).json({ error: 'sessionId is required' });
        }

        await whatsapp.createSession(sessionId);
        
        res.json({
            success: true,
            message: 'Session created. Scan QR code to connect.',
            sessionId,
        });
    } catch (error) {
        console.error('Error creating session:', error);
        res.status(500).json({ error: error.message });
    }
});

/**
 * GET /sessions/:sessionId/qr
 * Get QR code for a session
 */
router.get('/:sessionId/qr', async (req, res) => {
    try {
        const { sessionId } = req.params;
        
        // Ensure session exists
        await whatsapp.getSession(sessionId);
        
        // Wait a bit for QR to generate
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        const qrCode = whatsapp.getQRCode(sessionId);
        
        if (!qrCode) {
            const info = whatsapp.getSessionInfo(sessionId);
            if (info?.connected) {
                return res.json({
                    success: true,
                    connected: true,
                    message: 'Session already connected',
                    ...info,
                });
            }
            return res.json({
                success: false,
                message: 'QR code not available yet. Try again in a few seconds.',
            });
        }

        res.json({
            success: true,
            qr: qrCode,
        });
    } catch (error) {
        console.error('Error getting QR:', error);
        res.status(500).json({ error: error.message });
    }
});

/**
 * GET /sessions/:sessionId/status
 * Get session status
 */
router.get('/:sessionId/status', (req, res) => {
    try {
        const { sessionId } = req.params;
        const info = whatsapp.getSessionInfo(sessionId);
        
        if (!info) {
            return res.json({
                success: false,
                sessionId,
                status: 'not_found',
            });
        }

        res.json({
            success: true,
            ...info,
        });
    } catch (error) {
        console.error('Error getting status:', error);
        res.status(500).json({ error: error.message });
    }
});

/**
 * DELETE /sessions/:sessionId
 * Delete/logout a session
 */
router.delete('/:sessionId', async (req, res) => {
    try {
        const { sessionId } = req.params;
        
        await whatsapp.deleteSession(sessionId);
        
        res.json({
            success: true,
            message: 'Session deleted',
        });
    } catch (error) {
        console.error('Error deleting session:', error);
        res.status(500).json({ error: error.message });
    }
});

/**
 * GET /sessions
 * Get all active sessions
 */
router.get('/', (req, res) => {
    try {
        const sessions = whatsapp.getAllSessions();
        res.json({
            success: true,
            sessions,
        });
    } catch (error) {
        console.error('Error getting sessions:', error);
        res.status(500).json({ error: error.message });
    }
});

module.exports = router;
