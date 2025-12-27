const fs = require('fs');

class SimpleStore {
    constructor(filePath) {
        this.filePath = filePath;
        this.contacts = {};
        this.load();
    }

    load() {
        if (fs.existsSync(this.filePath)) {
            try {
                const data = fs.readFileSync(this.filePath, 'utf8');
                this.contacts = JSON.parse(data);
            } catch (e) {
                console.error('Failed to load store:', e);
                this.contacts = {};
            }
        }
    }

    save() {
        try {
            fs.writeFileSync(this.filePath, JSON.stringify(this.contacts, null, 2));
        } catch (e) {
            console.error('Failed to save store:', e);
        }
    }

    bind(ev) {
        ev.on('contacts.upsert', (contacts) => {
            // contacts is an array
            for (const contact of contacts) {
                const id = contact.id;
                if (!id) continue;
                
                // Merge existing
                this.contacts[id] = {
                    ...(this.contacts[id] || {}),
                    ...contact
                };
            }
            this.save();
        });

        ev.on('contacts.update', (updates) => {
            for (const update of updates) {
                const id = update.id;
                if (!id) continue;

                this.contacts[id] = {
                    ...(this.contacts[id] || {}),
                    ...update
                };
            }
            this.save();
        });

        // Capture pushName from messages
        ev.on('messages.upsert', ({ messages, type }) => {
            if(type !== 'notify') return;
            
            let changed = false;
            for (const msg of messages) {
                if (msg.key.fromMe) continue;
                
                const jid = msg.key.remoteJid;
                const pushName = msg.pushName;
                
                if (jid && pushName) {
                    const existing = this.contacts[jid] || {};
                    if (existing.notify !== pushName) {
                        this.contacts[jid] = {
                            ...existing,
                            id: jid,
                            notify: pushName
                        };
                        changed = true;
                    }
                }
            }
            if (changed) this.save();
        });
    }

    getName(jid) {
        const c = this.contacts[jid];
        // Priority: name (phonebook), notify (pushName), verifiedName (business), short jid
        return c?.name || c?.notify || c?.verifiedName || null;
    }
}

module.exports = SimpleStore;
