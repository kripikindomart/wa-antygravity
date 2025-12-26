<?php

namespace App\Livewire\Devices;

use App\Models\Device;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.tenant')]
#[Title('Devices')]
class DeviceIndex extends Component
{
    public bool $showAddModal = false;
    public bool $showQrModal = false;
    public ?string $qrCode = null;

    // Form properties
    public string $deviceName = '';
    public string $webhookUrl = '';

    // State
    public bool $isEditing = false;
    public ?int $editingDeviceId = null;

    public ?int $activeDeviceId = null;
    public ?string $activeDeviceToken = null;
    public string $deviceStatus = 'init';

    protected function rules()
    {
        return [
            'deviceName' => 'required|min:3|max:50',
            'webhookUrl' => 'nullable|url|max:255',
        ];
    }

    #[Computed]
    public function devices()
    {
        return Auth::user()->devices()->latest()->get();
    }

    public function openAddModal()
    {
        $this->resetForm();
        $this->showAddModal = true;
    }

    public function closeAddModal()
    {
        $this->showAddModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->deviceName = '';
        $this->webhookUrl = '';
        $this->isEditing = false;
        $this->editingDeviceId = null;
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate();

        if ($this->isEditing) {
            $this->updateDevice();
        } else {
            $this->createDevice();
        }
    }

    public function createDevice()
    {
        $device = Auth::user()->devices()->create([
            'name' => $this->deviceName,
            'webhook_url' => $this->webhookUrl ?: null,
            'status' => 'init',
        ]);

        $this->closeAddModal();

        // Automatically open QR modal for the new device
        $this->startScanning($device->id);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Device created! Scan QR code to connect.',
        ]);
    }

    public function editDevice($deviceId)
    {
        $device = Auth::user()->devices()->findOrFail($deviceId);

        $this->editingDeviceId = $device->id;
        $this->deviceName = $device->name;
        $this->webhookUrl = $device->webhook_url ?? '';
        $this->isEditing = true;

        $this->showAddModal = true;
    }

    public function updateDevice()
    {
        $device = Auth::user()->devices()->findOrFail($this->editingDeviceId);

        $device->update([
            'name' => $this->deviceName,
            'webhook_url' => $this->webhookUrl ?: null,
        ]);

        $this->closeAddModal();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Device updated successfully.',
        ]);
    }

    public function startScanning($deviceId)
    {
        $device = Auth::user()->devices()->findOrFail($deviceId);

        $this->activeDeviceId = $device->id;
        $this->activeDeviceToken = $device->token;
        $this->qrCode = null;
        $this->deviceStatus = 'scanning';
        $this->showQrModal = true;

        // Initialize session on Node.js service
        try {
            $nodeUrl = config('services.whatsapp.url', 'http://127.0.0.1:3001');
            Http::timeout(10)->post("{$nodeUrl}/sessions/add", [
                'sessionId' => $device->token,
            ]);

            $device->update(['status' => 'scanning']);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to connect to WhatsApp service: ' . $e->getMessage(),
            ]);
        }
    }

    public function pollQrCode()
    {
        if (!$this->activeDeviceToken) {
            return;
        }

        try {
            $nodeUrl = config('services.whatsapp.url', 'http://127.0.0.1:3001');
            $response = Http::timeout(5)->get("{$nodeUrl}/sessions/{$this->activeDeviceToken}/qr");

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['connected']) && $data['connected']) {
                    // Device is connected!
                    $this->deviceStatus = 'connected';
                    $this->qrCode = null;

                    // Update device in database
                    if ($this->activeDeviceId) {
                        $device = Device::find($this->activeDeviceId);
                        if ($device) {
                            $device->update([
                                'status' => 'connected',
                                'phone_number' => $data['phoneNumber'] ?? null,
                                'last_connected_at' => now(),
                            ]);
                        }
                    }

                    $this->dispatch('notify', [
                        'type' => 'success',
                        'message' => 'WhatsApp connected successfully!',
                    ]);

                    // Close modal after short delay
                    $this->closeQrModal();
                } elseif (isset($data['qr'])) {
                    $this->qrCode = $data['qr'];
                    $this->deviceStatus = 'scanning';
                }
            }
        } catch (\Exception $e) {
            // Silently fail on polling errors
        }
    }

    public function closeQrModal()
    {
        $this->showQrModal = false;
        $this->qrCode = null;
        $this->activeDeviceId = null;
        $this->activeDeviceToken = null;
        $this->deviceStatus = 'init';
    }

    public function disconnectDevice($deviceId)
    {
        $device = Auth::user()->devices()->findOrFail($deviceId);

        try {
            $nodeUrl = config('services.whatsapp.url', 'http://127.0.0.1:3001');
            Http::timeout(10)->delete("{$nodeUrl}/sessions/{$device->token}");
        } catch (\Exception $e) {
            // Continue even if Node.js service is unreachable
        }

        $device->update(['status' => 'disconnected']);

        $this->dispatch('notify', [
            'type' => 'info',
            'message' => 'Device disconnected.',
        ]);
    }

    public function deleteDevice($deviceId)
    {
        $device = Auth::user()->devices()->findOrFail($deviceId);

        // Try to disconnect from Node.js service first
        try {
            $nodeUrl = config('services.whatsapp.url', 'http://127.0.0.1:3001');
            Http::timeout(10)->delete("{$nodeUrl}/sessions/{$device->token}");
        } catch (\Exception $e) {
            // Continue even if Node.js service is unreachable
        }

        $device->delete();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Device deleted.',
        ]);
    }

    /**
     * Sync device status from Node.js service (on-demand, not auto-polling)
     */
    public function syncDeviceStatus($deviceId)
    {
        $device = Auth::user()->devices()->findOrFail($deviceId);

        try {
            $nodeUrl = config('services.whatsapp.url', 'http://127.0.0.1:3001');
            $response = Http::timeout(5)->get("{$nodeUrl}/sessions/{$device->token}/status");

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['connected']) && $data['connected']) {
                    $device->update([
                        'status' => 'connected',
                        'phone_number' => $data['phoneNumber'] ?? $device->phone_number,
                        'last_connected_at' => now(),
                    ]);

                    $this->dispatch('notify', [
                        'type' => 'success',
                        'message' => "Device '{$device->name}' is connected.",
                    ]);
                } elseif (isset($data['status']) && $data['status'] === 'not_found') {
                    // Session not active in Node.js, needs reconnect
                    $device->update(['status' => 'disconnected']);

                    $this->dispatch('notify', [
                        'type' => 'warning',
                        'message' => "Device '{$device->name}' needs reconnection. Click Connect to scan QR.",
                    ]);
                } else {
                    $device->update(['status' => 'disconnected']);
                }
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'WhatsApp service unavailable.',
            ]);
        }
    }

    /**
     * Sync all devices status
     */
    public function syncAllDevices()
    {
        $devices = Auth::user()->devices()->get();
        $connected = 0;
        $disconnected = 0;

        foreach ($devices as $device) {
            try {
                $nodeUrl = config('services.whatsapp.url', 'http://127.0.0.1:3001');
                $response = Http::timeout(3)->get("{$nodeUrl}/sessions/{$device->token}/status");

                if ($response->successful()) {
                    $data = $response->json();

                    if (isset($data['connected']) && $data['connected']) {
                        $device->update([
                            'status' => 'connected',
                            'phone_number' => $data['phoneNumber'] ?? $device->phone_number,
                            'last_connected_at' => now(),
                        ]);
                        $connected++;
                    } else {
                        $device->update(['status' => 'disconnected']);
                        $disconnected++;
                    }
                } else {
                    $device->update(['status' => 'disconnected']);
                    $disconnected++;
                }
            } catch (\Exception $e) {
                $device->update(['status' => 'disconnected']);
                $disconnected++;
            }
        }

        $this->dispatch('notify', [
            'type' => 'info',
            'message' => "Sync complete: {$connected} connected, {$disconnected} disconnected.",
        ]);
    }

    public function reconnectDevice($deviceId)
    {
        $this->startScanning($deviceId);
    }

    public function render()
    {
        return view('livewire.devices.device-index');
    }
}
