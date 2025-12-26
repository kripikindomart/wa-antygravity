<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * @group Messages
 *
 * APIs for sending and managing WhatsApp messages
 */
class MessageController extends Controller
{
    /**
     * List messages
     *
     * Get a list of sent messages for the authenticated user.
     *
     * @authenticated
     * @queryParam device_id integer Filter by device ID. Example: 1
     * @queryParam status string Filter by status (pending, sent, failed). Example: sent
     * @queryParam per_page integer Number of items per page (default: 50). Example: 20
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "to": "+628123456789",
     *       "message": "Hello World",
     *       "status": "sent",
     *       "sent_at": "2024-01-01T00:00:00.000000Z"
     *     }
     *   ]
     * }
     */
    public function index(Request $request)
    {
        $query = $request->user()->messages()->with('device:id,name');

        if ($request->device_id) {
            $query->where('device_id', $request->device_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $messages = $query->latest()->paginate($request->per_page ?? 50);

        return response()->json([
            'success' => true,
            'data' => $messages->items(),
            'meta' => [
                'current_page' => $messages->currentPage(),
                'total' => $messages->total(),
            ],
        ]);
    }

    /**
     * Send a message
     *
     * Send a WhatsApp message to a recipient.
     *
     * @authenticated
     * @bodyParam device_id integer required The ID of the connected device to send from. Example: 1
     * @bodyParam to string required The recipient phone number (international format). Example: +628123456789
     * @bodyParam message string required The message text. Example: Hello, this is a test message!
     * @response 200 {
     *   "success": true,
     *   "message": "Message sent successfully",
     *   "data": {
     *     "id": 1,
     *     "to": "+628123456789",
     *     "status": "sent",
     *     "wa_message_id": "ABC123"
     *   }
     * }
     * @response 400 {
     *   "success": false,
     *   "error": "Device is not connected"
     * }
     */
    public function store(Request $request)
    {
        $request->validate([
            'device_id' => 'required|exists:devices,id',
            'to' => 'required|string|min:10',
            'message' => 'required|string|min:1',
        ]);

        $device = $request->user()->devices()->findOrFail($request->device_id);

        if ($device->status !== 'connected') {
            return response()->json([
                'success' => false,
                'error' => 'Device is not connected',
            ], 400);
        }

        // Create message record
        $message = Message::create([
            'user_id' => $request->user()->id,
            'device_id' => $device->id,
            'to' => $request->to,
            'message' => $request->message,
            'type' => 'text',
            'status' => 'pending',
        ]);

        // Send via Node.js service
        try {
            $nodeUrl = config('services.whatsapp.url', 'http://127.0.0.1:3001');
            $response = Http::timeout(30)->post("{$nodeUrl}/messages/send", [
                'sessionId' => $device->token,
                'to' => $request->to,
                'message' => $request->message,
            ]);

            if ($response->successful()) {
                $message->update([
                    'status' => 'sent',
                    'wa_message_id' => $response->json('messageId'),
                    'sent_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Message sent successfully',
                    'data' => [
                        'id' => $message->id,
                        'to' => $message->to,
                        'status' => 'sent',
                        'wa_message_id' => $message->wa_message_id,
                    ],
                ]);
            } else {
                $message->update([
                    'status' => 'failed',
                    'error_message' => $response->json('error') ?? $response->body(),
                ]);

                return response()->json([
                    'success' => false,
                    'error' => 'Failed to send message: ' . ($response->json('error') ?? 'Unknown error'),
                ], 500);
            }
        } catch (\Exception $e) {
            $message->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to connect to WhatsApp service: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send bulk messages
     *
     * Send the same message to multiple recipients. Messages are queued and sent with delays to avoid being banned.
     *
     * @authenticated
     * @bodyParam device_id integer required The ID of the connected device. Example: 1
     * @bodyParam recipients array required Array of phone numbers. Example: ["+628123456789", "+628987654321"]
     * @bodyParam message string required The message text. Example: Hello everyone!
     * @bodyParam delay_seconds integer Delay between messages (5-60 seconds, default: 10). Example: 15
     * @response 202 {
     *   "success": true,
     *   "message": "Bulk message queued",
     *   "total_recipients": 5
     * }
     */
    public function sendBulk(Request $request)
    {
        $request->validate([
            'device_id' => 'required|exists:devices,id',
            'recipients' => 'required|array|min:1',
            'recipients.*' => 'required|string|min:10',
            'message' => 'required|string|min:1',
            'delay_seconds' => 'nullable|integer|min:5|max:60',
        ]);

        $device = $request->user()->devices()->findOrFail($request->device_id);

        if ($device->status !== 'connected') {
            return response()->json([
                'success' => false,
                'error' => 'Device is not connected',
            ], 400);
        }

        $delay = $request->delay_seconds ?? 10;

        // Create message records
        foreach ($request->recipients as $recipient) {
            Message::create([
                'user_id' => $request->user()->id,
                'device_id' => $device->id,
                'to' => $recipient,
                'message' => $request->message,
                'type' => 'text',
                'status' => 'pending',
            ]);
        }

        // In production, dispatch to a queue job
        // SendBulkMessages::dispatch($device, $request->recipients, $request->message, $delay);

        return response()->json([
            'success' => true,
            'message' => 'Bulk message queued',
            'total_recipients' => count($request->recipients),
        ], 202);
    }

    /**
     * Get message status
     *
     * @authenticated
     * @urlParam id integer required The message ID. Example: 1
     */
    public function show(Request $request, $id)
    {
        $message = $request->user()->messages()->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $message,
        ]);
    }
}
