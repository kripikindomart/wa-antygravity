require("dotenv").config();
const express = require("express");
const cors = require("cors");
const fs = require("fs");
const path = require("path");
const sessionRoutes = require("./routes/sessions");
const messageRoutes = require("./routes/messages");
const {
    createSession,
    isConnected,
    getSessionInfo,
} = require("./services/whatsapp");

const app = express();
const PORT = process.env.PORT || 3001;
const SESSION_DIR = process.env.SESSION_DIR || "./sessions";

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Routes
app.use("/sessions", sessionRoutes);
app.use("/messages", messageRoutes);

// Health check
app.get("/health", (req, res) => {
    res.json({ status: "ok", timestamp: new Date().toISOString() });
});

// Error handler
app.use((err, req, res, next) => {
    console.error(err.stack);
    res.status(500).json({ error: "Something went wrong!" });
});

/**
 * Auto-reconnect existing sessions on startup
 */
async function autoReconnectSessions() {
    console.log("🔄 Checking for existing sessions to reconnect...");

    if (!fs.existsSync(SESSION_DIR)) {
        console.log("📁 No sessions directory found");
        return;
    }

    const sessionDirs = fs.readdirSync(SESSION_DIR).filter((file) => {
        const fullPath = path.join(SESSION_DIR, file);
        return fs.statSync(fullPath).isDirectory();
    });

    if (sessionDirs.length === 0) {
        console.log("📭 No saved sessions found");
        return;
    }

    console.log(`📦 Found ${sessionDirs.length} saved session(s)`);

    for (const sessionId of sessionDirs) {
        try {
            console.log(`🔌 Reconnecting session: ${sessionId}`);
            await createSession(sessionId);

            // Wait a bit for connection to establish
            await new Promise((resolve) => setTimeout(resolve, 3000));

            const info = getSessionInfo(sessionId);
            if (info && info.connected) {
                console.log(
                    `✅ Session ${sessionId} reconnected successfully (${info.phoneNumber})`
                );
            } else {
                console.log(
                    `⏳ Session ${sessionId} initialized, waiting for connection...`
                );
            }
        } catch (error) {
            console.error(
                `❌ Failed to reconnect session ${sessionId}:`,
                error.message
            );
        }
    }
}

// Start server and auto-reconnect
app.listen(PORT, async () => {
    console.log(`🚀 WhatsApp Gateway Service running on port ${PORT}`);
    console.log(
        `📡 Webhook URL: ${process.env.LARAVEL_WEBHOOK_URL || "Not configured"}`
    );

    // Auto-reconnect saved sessions
    await autoReconnectSessions();

    console.log("✨ Service ready!");
});
