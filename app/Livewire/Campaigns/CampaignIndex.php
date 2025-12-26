<?php

namespace App\Livewire\Campaigns;

use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Message;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

#[Layout('components.layouts.tenant')]
#[Title('Campaigns')]
class CampaignIndex extends Component
{
    use WithPagination;

    public bool $showModal = false;
    public bool $isEditing = false;
    public ?int $editingId = null;

    public string $name = '';
    public string $message = '';
    public ?int $device_id = null;
    public array $selectedContacts = [];
    public int $delay_seconds = 10;

    protected $rules = [
        'name' => 'required|min:2|max:100',
        'message' => 'required|min:1',
        'device_id' => 'required|exists:devices,id',
        'selectedContacts' => 'required|array|min:1',
        'delay_seconds' => 'required|integer|min:5|max:60',
    ];

    protected $messages = [
        'selectedContacts.required' => 'Please select at least one contact.',
        'selectedContacts.min' => 'Please select at least one contact.',
    ];

    #[Computed]
    public function devices()
    {
        return Auth::user()->devices()->where('status', 'connected')->get();
    }

    #[Computed]
    public function contacts()
    {
        return Auth::user()->contacts()->orderBy('name')->get();
    }

    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->isEditing = false;
        $this->editingId = null;
        $this->name = '';
        $this->message = '';
        $this->device_id = null;
        $this->selectedContacts = [];
        $this->delay_seconds = 10;
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'message' => $this->message,
            'device_id' => $this->device_id,
            'target_contacts' => $this->selectedContacts,
            'total_recipients' => count($this->selectedContacts),
            'delay_seconds' => $this->delay_seconds,
            'status' => 'draft',
        ];

        if ($this->isEditing && $this->editingId) {
            $campaign = Auth::user()->campaigns()->findOrFail($this->editingId);
            $campaign->update($data);
            $message = 'Campaign updated!';
        } else {
            Auth::user()->campaigns()->create($data);
            $message = 'Campaign created!';
        }

        $this->closeModal();
        $this->dispatch('notify', ['type' => 'success', 'message' => $message]);
    }

    public function edit($id)
    {
        $campaign = Auth::user()->campaigns()->findOrFail($id);

        $this->isEditing = true;
        $this->editingId = $campaign->id;
        $this->name = $campaign->name;
        $this->message = $campaign->message;
        $this->device_id = $campaign->device_id;
        $this->selectedContacts = $campaign->target_contacts ?? [];
        $this->delay_seconds = $campaign->delay_seconds;
        $this->showModal = true;
    }

    public function startCampaign($id)
    {
        $campaign = Auth::user()->campaigns()->findOrFail($id);
        $device = $campaign->device;

        if (!$device || $device->status !== 'connected') {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Device is not connected.']);
            return;
        }

        $campaign->update([
            'status' => 'running',
            'started_at' => now(),
            'sent_count' => 0,
            'failed_count' => 0,
        ]);

        // Get contacts
        $contacts = Contact::whereIn('id', $campaign->target_contacts)->get();

        // Create messages and send in background
        foreach ($contacts as $index => $contact) {
            $msg = Message::create([
                'user_id' => Auth::id(),
                'device_id' => $device->id,
                'campaign_id' => $campaign->id,
                'to' => $contact->phone_number,
                'message' => $campaign->message,
                'type' => 'text',
                'status' => 'pending',
            ]);

            // For demo: send synchronously with delay
            // In production, use Laravel Jobs
            try {
                $nodeUrl = config('services.whatsapp.url', 'http://127.0.0.1:3001');
                $response = Http::timeout(30)->post("{$nodeUrl}/messages/send", [
                    'sessionId' => $device->token,
                    'to' => $contact->phone_number,
                    'message' => $campaign->message,
                ]);

                if ($response->successful()) {
                    $msg->update([
                        'status' => 'sent',
                        'wa_message_id' => $response->json('messageId'),
                        'sent_at' => now(),
                    ]);
                    $campaign->increment('sent_count');
                } else {
                    $msg->update(['status' => 'failed', 'error_message' => $response->body()]);
                    $campaign->increment('failed_count');
                }
            } catch (\Exception $e) {
                $msg->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
                $campaign->increment('failed_count');
            }

            // Delay between messages (only if not last)
            if ($index < $contacts->count() - 1) {
                sleep($campaign->delay_seconds);
            }
        }

        $campaign->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Campaign completed! Sent: {$campaign->sent_count}, Failed: {$campaign->failed_count}",
        ]);
    }

    public function pauseCampaign($id)
    {
        $campaign = Auth::user()->campaigns()->findOrFail($id);
        $campaign->update(['status' => 'paused']);
        $this->dispatch('notify', ['type' => 'info', 'message' => 'Campaign paused.']);
    }

    public ?int $campaignToDelete = null;

    public function confirmDelete($campaignId)
    {
        $this->campaignToDelete = $campaignId;
        $this->dispatch(
            'show-delete-confirmation',
            action: 'delete-campaign-confirmed',
            title: 'Delete Campaign?',
            text: 'This will delete the campaign and its history.'
        );
    }

    #[\Livewire\Attributes\On('delete-campaign-confirmed')]
    public function deleteCampaignConfirmed()
    {
        if ($this->campaignToDelete) {
            $campaign = Auth::user()->campaigns()->findOrFail($this->campaignToDelete);
            $campaign->delete();
            $this->campaignToDelete = null;
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Campaign deleted.']);
        }
    }

    public function delete($id)
    {
        $this->confirmDelete($id);
    }

    public function render()
    {
        $campaigns = Auth::user()->campaigns()
            ->with('device')
            ->latest()
            ->paginate(10);

        return view('livewire.campaigns.campaign-index', [
            'campaigns' => $campaigns,
        ]);
    }
}
