<?php

namespace App\Livewire\Messages;

use App\Models\Message;
use App\Models\Device;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

#[Layout('components.layouts.tenant')]
#[Title('Messages')]
class MessageIndex extends Component
{
    use WithPagination;

    public ?int $selectedDeviceId = null;
    public string $recipient = '';
    public string $messageText = '';
    public string $filterStatus = '';

    protected $rules = [
        'selectedDeviceId' => 'required|exists:devices,id',
        'recipient' => 'required|min:10',
        'messageText' => 'required|min:1',
    ];

    protected $messages = [
        'selectedDeviceId.required' => 'Please select a device.',
        'recipient.required' => 'Recipient phone number is required.',
        'messageText.required' => 'Message cannot be empty.',
    ];

    #[Computed]
    public function devices()
    {
        return Auth::user()->devices()->where('status', 'connected')->get();
    }

    public function sendMessage()
    {
        $this->validate();

        $device = Auth::user()->devices()->findOrFail($this->selectedDeviceId);

        // Create message record
        $message = Auth::user()->messages()->create([
            'device_id' => $device->id,
            'to' => $this->recipient,
            'message' => $this->messageText,
            'type' => 'text',
            'status' => 'pending',
        ]);

        // Send via Node.js service
        try {
            $nodeUrl = config('services.whatsapp.url', 'http://127.0.0.1:3001');
            $response = Http::timeout(30)->post("{$nodeUrl}/messages/send", [
                'sessionId' => $device->token,
                'to' => $this->recipient,
                'message' => $this->messageText,
            ]);

            if ($response->successful()) {
                $message->update([
                    'status' => 'sent',
                    'wa_message_id' => $response->json('messageId'),
                    'sent_at' => now(),
                ]);

                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => 'Message sent successfully!',
                ]);

                // Clear form
                $this->recipient = '';
                $this->messageText = '';
            } else {
                $message->update([
                    'status' => 'failed',
                    'error_message' => $response->body(),
                ]);

                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Failed to send message: ' . $response->json('error', 'Unknown error'),
                ]);
            }
        } catch (\Exception $e) {
            $message->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to send message: ' . $e->getMessage(),
            ]);
        }
    }

    public function retryMessage($messageId)
    {
        $message = Auth::user()->messages()->findOrFail($messageId);
        $device = $message->device;

        if (!$device || $device->status !== 'connected') {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Device is not connected.',
            ]);
            return;
        }

        $message->update(['status' => 'pending']);

        try {
            $nodeUrl = config('services.whatsapp.url', 'http://127.0.0.1:3001');
            $response = Http::timeout(30)->post("{$nodeUrl}/messages/send", [
                'sessionId' => $device->token,
                'to' => $message->to,
                'message' => $message->message,
            ]);

            if ($response->successful()) {
                $message->update([
                    'status' => 'sent',
                    'wa_message_id' => $response->json('messageId'),
                    'sent_at' => now(),
                    'error_message' => null,
                ]);

                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => 'Message resent successfully!',
                ]);
            } else {
                $message->update([
                    'status' => 'failed',
                    'error_message' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            $message->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    public function deleteMessage($messageId)
    {
        $message = Auth::user()->messages()->findOrFail($messageId);
        $message->delete();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Message deleted.',
        ]);
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Auth::user()->messages()
            ->with(['device'])
            ->when($this->filterStatus, function ($q) {
                $q->where('status', $this->filterStatus);
            })
            ->latest();

        return view('livewire.messages.message-index', [
            'messages' => $query->paginate(15),
        ]);
    }
}
