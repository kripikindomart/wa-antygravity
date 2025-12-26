<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * @group Devices
 *
 * APIs for managing WhatsApp devices
 */
class DeviceController extends Controller
{
    /**
     * List all devices
     *
     * Get a list of all WhatsApp devices for the authenticated user.
     *
     * @authenticated
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Customer Support",
     *       "phone_number": "6281234567890",
     *       "status": "connected",
     *       "created_at": "2024-01-01T00:00:00.000000Z"
     *     }
     *   ]
     * }
     */
    public function index(Request $request)
    {
        $devices = $request->user()->devices()->get(['id', 'name', 'phone_number', 'status', 'created_at']);

        return response()->json([
            'success' => true,
            'data' => $devices,
        ]);
    }

    /**
     * Create a new device
     *
     * Create a new WhatsApp device. After creation, use the QR endpoint to get the QR code for connecting.
     *
     * @authenticated
     * @bodyParam name string required The name/label for this device. Example: Customer Support
     * @response 201 {
     *   "success": true,
     *   "message": "Device created successfully",
     *   "data": {
     *     "id": 1,
     *     "token": "abc123-uuid",
     *     "name": "Customer Support",
     *     "status": "init"
     *   }
     * }
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:2|max:50',
        ]);

        $device = $request->user()->devices()->create([
            'name' => $request->name,
            'status' => 'init',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Device created successfully',
            'data' => [
                'id' => $device->id,
                'token' => $device->token,
                'name' => $device->name,
                'status' => $device->status,
            ],
        ], 201);
    }

    /**
     * Get device details
     *
     * Get details of a specific device.
     *
     * @authenticated
     * @urlParam id integer required The device ID. Example: 1
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "name": "Customer Support",
     *     "phone_number": "6281234567890",
     *     "status": "connected",
     *     "last_connected_at": "2024-01-01T00:00:00.000000Z"
     *   }
     * }
     */
    public function show(Request $request, $id)
    {
        $device = $request->user()->devices()->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $device,
        ]);
    }

    /**
     * Get QR code for device
     *
     * Initialize a WhatsApp session and get the QR code for scanning.
     *
     * @authenticated
     * @urlParam id integer required The device ID. Example: 1
     * @response 200 {
     *   "success": true,
     *   "qr": "data:image/png;base64,..."
     * }
     * @response 200 {
     *   "success": true,
     *   "connected": true,
     *   "phone_number": "6281234567890"
     * }
     */
    public function getQr(Request $request, $id)
    {
        $device = $request->user()->devices()->findOrFail($id);

        try {
            $nodeUrl = config('services.whatsapp.url', 'http://127.0.0.1:3001');

            // Initialize session if not exists
            Http::timeout(10)->post("{$nodeUrl}/sessions/add", [
                'sessionId' => $device->token,
            ]);

            // Wait a moment for QR to generate
            sleep(2);

            // Get QR code
            $response = Http::timeout(10)->get("{$nodeUrl}/sessions/{$device->token}/qr");

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['connected']) && $data['connected']) {
                    $device->update([
                        'status' => 'connected',
                        'phone_number' => $data['phoneNumber'] ?? null,
                    ]);

                    return response()->json([
                        'success' => true,
                        'connected' => true,
                        'phone_number' => $data['phoneNumber'],
                    ]);
                }

                if (isset($data['qr'])) {
                    $device->update(['status' => 'scanning']);

                    return response()->json([
                        'success' => true,
                        'qr' => $data['qr'],
                    ]);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'QR code not available yet. Try again.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to connect to WhatsApp service: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a device
     *
     * Disconnect and delete a WhatsApp device.
     *
     * @authenticated
     * @urlParam id integer required The device ID. Example: 1
     * @response 200 {
     *   "success": true,
     *   "message": "Device deleted successfully"
     * }
     */
    public function destroy(Request $request, $id)
    {
        $device = $request->user()->devices()->findOrFail($id);

        try {
            $nodeUrl = config('services.whatsapp.url', 'http://127.0.0.1:3001');
            Http::timeout(10)->delete("{$nodeUrl}/sessions/{$device->token}");
        } catch (\Exception $e) {
            // Continue even if Node.js is unreachable
        }

        $device->delete();

        return response()->json([
            'success' => true,
            'message' => 'Device deleted successfully',
        ]);
    }
}
