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

    protected $listeners = ['refresh' => '$refresh'];

    public function boot()
    {
        // Poll every 3 seconds to check status of running campaigns
        $this->dispatch('poll-status');
    }

    public string $name = '';
    public string $message = '';
    public ?int $device_id = null;
    public array $selectedContacts = [];
    public int $delay_seconds = 10;
    public ?int $selectedTemplateId = null;

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

    #[Computed]
    public function templates()
    {
        return Auth::user()->messageTemplates()->orderByDesc('is_favorite')->orderBy('name')->get();
    }

    public function processNextBatch($campaignId)
    {
        $campaign = Campaign::with('device')->find($campaignId);

        if (!$campaign || $campaign->status !== 'running') {
            return ['status' => 'stopped'];
        }

        // Get next pending recipient
        $recipient = $campaign->recipients()->where('status', 'pending')->first();

        if (!$recipient) {
            $campaign->update(['status' => 'completed', 'completed_at' => now()]);
            return ['status' => 'completed'];
        }

        $device = $campaign->device;
        if (!$device || $device->status !== 'connected') {
            return ['status' => 'error', 'message' => 'Device disconnected'];
        }

        // Send logic (Single Recipient)
        try {
            // Prepare Message
            $template = $campaign->message;
            $message = str_replace('[name]', $recipient->name, $template);
            $message = str_replace('[phone]', $recipient->phone_number, $message);

            if ($recipient->custom_data) {
                foreach ($recipient->custom_data as $key => $value) {
                    $message = str_replace('[' . $key . ']', $value, $message);
                }
            }

            $nodeUrl = config('services.whatsapp.url', 'http://127.0.0.1:3001');
            $response = Http::timeout(30)->post("{$nodeUrl}/messages/send", [
                'sessionId' => $device->token,
                'to' => $recipient->phone_number,
                'message' => $message,
            ]);

            if ($response->successful()) {
                $recipient->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'wa_message_id' => $response->json('messageId')
                ]);
                $campaign->increment('sent_count');

                // Log to Message History
                Message::create([
                    'user_id' => Auth::id(),
                    'device_id' => $device->id,
                    'campaign_id' => $campaign->id,
                    'to' => $recipient->phone_number,
                    'message' => $message,
                    'type' => 'text',
                    'status' => 'sent',
                    'wa_message_id' => $response->json('messageId'),
                ]);

                return [
                    'status' => 'processing',
                    'sent' => true,
                    'sent_count' => $campaign->sent_count,
                    'failed_count' => $campaign->failed_count,
                    'progress' => $campaign->progress_percentage,
                    'delay_seconds' => $campaign->delay_seconds
                ];
            } else {
                throw new \Exception($response->body());
            }

        } catch (\Exception $e) {
            $recipient->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            $campaign->increment('failed_count');

            if ($campaign->error_mode === 'stop') {
                $campaign->update(['status' => 'paused']);
                return ['status' => 'stopped', 'error' => $e->getMessage()];
            }

            return [
                'status' => 'processing',
                'sent' => false,
                'error' => $e->getMessage(),
                'sent_count' => $campaign->sent_count,
                'failed_count' => $campaign->failed_count,
                'progress' => $campaign->progress_percentage
            ];
        }
    }

    public function loadTemplate($templateId)
    {
        if (!$templateId)
            return;
        $template = Auth::user()->messageTemplates()->find($templateId);
        if ($template) {
            $this->message = $template->content;
        }
    }

    public function updatedSelectedTemplateId($value)
    {
        $this->loadTemplate($value);
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

        if ($campaign->status === 'draft') {
            $campaign->update(['status' => 'running', 'started_at' => now()]);
        }

        // Use dispatchSync to run immediately without queue worker
        try {
            \App\Jobs\ProcessCampaignJob::dispatchSync($campaign->id);

            $this->dispatch('notify', ['type' => 'success', 'message' => 'Campaign processing started!']);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Error starting campaign: ' . $e->getMessage()]);
        }
    }

    public function forceProcess($id)
    {
        $this->startCampaign($id);
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
