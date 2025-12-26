<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Inbox;
use App\Models\AutoReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle incoming webhooks from Node.js WhatsApp service
     */
    public function handle(Request $request)
    {
        $event = $request->input('event');
        $data = $request->input('data');
        $timestamp = $request->input('timestamp');

        Log::info("WhatsApp Webhook received: {$event}", $data);

        return match ($event) {
            'connection.update' => $this->handleConnectionUpdate($data),
            'qr.update' => $this->handleQRUpdate($data),
            'message.received' => $this->handleIncomingMessage($data),
            default => response()->json(['status' => 'unknown_event']),
        };
    }

    /**
     * Handle connection status updates
     */
    protected function handleConnectionUpdate(array $data)
    {
        $sessionId = $data['sessionId'] ?? null;
        if (!$sessionId) {
            return response()->json(['error' => 'sessionId required'], 400);
        }

        $device = Device::where('token', $sessionId)->first();
        if (!$device) {
            return response()->json(['error' => 'device not found'], 404);
        }

        $status = $data['status'] ?? 'disconnected';

        $device->update([
            'status' => $status,
            'phone_number' => $data['phoneNumber'] ?? $device->phone_number,
            'last_connected_at' => $status === 'connected' ? now() : $device->last_connected_at,
        ]);

        // Broadcast event to frontend via Livewire (can be enhanced with Reverb later)
        // event(new DeviceStatusChanged($device));

        return response()->json(['status' => 'ok', 'device_status' => $status]);
    }

    /**
     * Handle QR code updates
     */
    protected function handleQRUpdate(array $data)
    {
        $sessionId = $data['sessionId'] ?? null;
        if (!$sessionId) {
            return response()->json(['error' => 'sessionId required'], 400);
        }

        $device = Device::where('token', $sessionId)->first();
        if (!$device) {
            return response()->json(['error' => 'device not found'], 404);
        }

        // Update device status to scanning
        $device->update(['status' => 'scanning']);

        // Store QR in cache for frontend polling
        cache()->put("qr_code_{$sessionId}", $data['qr'], now()->addMinutes(5));

        return response()->json(['status' => 'ok']);
    }

    /**
     * Handle incoming messages
     */
    protected function handleIncomingMessage(array $data)
    {
        Log::info('Incoming message data:', $data);

        $sessionId = $data['sessionId'] ?? null;
        if (!$sessionId) {
            Log::warning('Session ID missing in webhook data');
            return response()->json(['error' => 'sessionId required'], 400);
        }

        $device = Device::where('token', $sessionId)->first();
        if (!$device) {
            Log::warning("Device not found for session: {$sessionId}");
            return response()->json(['error' => 'device not found'], 404);
        }

        // Extract phone number from JID
        $fromNumber = preg_replace('/@.*/', '', $data['from'] ?? '');
        $messageContent = $data['message'] ?? '';

        Log::info("Message from {$fromNumber} on device {$device->name}: {$messageContent}");

        // Save to inbox
        $inbox = Inbox::create([
            'device_id' => $device->id,
            'from_number' => $fromNumber,
            'from_name' => $data['fromName'] ?? null,
            'message' => $messageContent,
            'type' => $this->mapMessageType($data['type'] ?? 'conversation'),
            'wa_message_id' => $data['messageId'] ?? null,
            'received_at' => now(),
        ]);

        // Forward to user's webhook if configured
        if ($device->webhook_url) {
            $this->forwardToWebhook($device->webhook_url, $data, $inbox);
        }

        // Check for auto-replies
        $this->processAutoReply($device, $fromNumber, $messageContent);

        return response()->json(['status' => 'ok', 'inbox_id' => $inbox->id]);
    }

    // ... mapMessageType ...

    // ... forwardToWebhook ...

    /**
     * Process auto-reply rules
     */
    protected function processAutoReply(Device $device, string $fromNumber, string $message)
    {
        if (empty($message)) {
            Log::info("Empty message from {$fromNumber}, skipping auto-reply");
            return;
        }

        Log::info("Processing auto-reply for message: '{$message}' from {$fromNumber}");

        // Get active auto-reply rules for this device or global (device_id = null)
        $rules = AutoReply::where('user_id', $device->user_id)
            ->where('is_active', true)
            ->where(function ($query) use ($device) {
                $query->whereNull('device_id')
                    ->orWhere('device_id', $device->id);
            })
            ->orderByDesc('priority')
            ->get();

        Log::info("Found " . $rules->count() . " active rules for user {$device->user_id}");

        foreach ($rules as $rule) {
            Log::info("Checking rule ID: {$rule->id} Name: {$rule->name} with Keywords: " . json_encode($rule->keywords));

            if ($rule->matchesMessage($message)) {
                Log::info("Rule matched! Sending reply...");
                // Send auto-reply via Node.js service
                $this->sendAutoReply($device, $fromNumber, $rule);
                break; // Only send first matching reply
            } else {
                Log::info("Rule did not match.");
            }
        }
    }

    /**
     * Send auto-reply message
     */
    protected function sendAutoReply(Device $device, string $to, AutoReply $rule)
    {
        try {
            $nodeServiceUrl = config('services.whatsapp.url', 'http://127.0.0.1:3001');

            $response = Http::timeout(30)->post("{$nodeServiceUrl}/messages/send", [
                'sessionId' => $device->token,
                'to' => $to,
                'message' => $rule->reply_message,
            ]);

            if ($response->successful()) {
                Log::info("Auto-reply sent successfully to {$to}");
                // Increment hit count (assumes column exists, or we log it)
                // TODO: Add column hit_count to auto_replies table
            } else {
                Log::error("Failed to send auto-reply to {$to}. Status: " . $response->status() . " Body: " . $response->body());
            }

        } catch (\Exception $e) {
            Log::error("Auto-reply exception: {$e->getMessage()}");
        }
    }
}
