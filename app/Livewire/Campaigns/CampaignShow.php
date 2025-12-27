<?php

namespace App\Livewire\Campaigns;

use App\Models\Campaign;
use App\Models\CampaignRecipient;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.tenant')]
#[Title('Campaign Monitor')]
class CampaignShow extends Component
{
    use WithPagination;

    public Campaign $campaign;
    public string $filterStatus = 'all';

    public function mount(Campaign $campaign)
    {
        $this->campaign = $campaign;
    }

    public function getRecipientsProperty()
    {
        return $this->campaign->recipients()
            ->when($this->filterStatus !== 'all', function ($q) {
                $q->where('status', $this->filterStatus);
            })
            ->latest()
            ->paginate(50);
    }

    public function pause()
    {
        $this->campaign->update(['status' => 'paused']);
        $this->dispatch('notify', ['type' => 'info', 'message' => 'Campaign paused.']);
    }

    public function resume()
    {
        $this->campaign->update(['status' => 'running']);
        \App\Jobs\ProcessCampaignJob::dispatch($this->campaign->id);
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Campaign resumed.']);
    }

    public function retryFailed()
    {
        $count = $this->campaign->recipients()->where('status', 'failed')->update(['status' => 'pending']);

        if ($count > 0) {
            $this->campaign->update(['status' => 'running']);
            \App\Jobs\ProcessCampaignJob::dispatch($this->campaign->id);
            $this->dispatch('notify', ['type' => 'success', 'message' => "Retrying {$count} failed messages."]);
        } else {
            $this->dispatch('notify', ['type' => 'info', 'message' => 'No failed messages to retry.']);
        }
    }

    public function render()
    {
        return view('livewire.campaigns.campaign-show', [
            'recipients' => $this->recipients,
            'stats' => [
                'total' => $this->campaign->recipients()->count(),
                'sent' => $this->campaign->recipients()->where('status', 'sent')->count(),
                'failed' => $this->campaign->recipients()->where('status', 'failed')->count(),
                'pending' => $this->campaign->recipients()->where('status', 'pending')->count(),
                'processing' => $this->campaign->recipients()->where('status', 'processing')->count(),
            ]
        ]);
    }
}
