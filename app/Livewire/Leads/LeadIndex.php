<?php

namespace App\Livewire\Leads;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use App\Models\Lead;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.tenant')]
#[Title('Daily Leads')]
class LeadIndex extends Component
{
    use WithPagination;

    #[Url]
    public $date = '';

    #[Url]
    public $status = '';

    public function mount()
    {
        if (empty($this->date)) {
            $this->date = now()->format('Y-m-d');
        }
    }

    public function updateStatus($leadId, $newStatus)
    {
        $lead = Lead::where('user_id', Auth::id())->findOrFail($leadId);
        $lead->update(['status' => $newStatus]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Lead status updated.'
        ]);
    }

    public function render()
    {
        $query = Lead::where('user_id', Auth::id())
            ->whereDate('created_at', $this->date);

        if ($this->status) {
            $query->where('status', $this->status);
        }

        $leads = $query->latest('last_activity_at')->paginate(20);

        // Stats for the selected day
        $stats = [
            'total' => Lead::where('user_id', Auth::id())->whereDate('created_at', $this->date)->count(),
            'new' => Lead::where('user_id', Auth::id())->whereDate('created_at', $this->date)->where('status', 'new')->count(),
            'converted' => Lead::where('user_id', Auth::id())->whereDate('created_at', $this->date)->where('status', 'converted')->count(),
        ];

        return view('livewire.leads.lead-index', [
            'leads' => $leads,
            'stats' => $stats
        ]);
    }
}
