<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.tenant')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    #[Computed]
    public function stats()
    {
        $user = Auth::user();

        return [
            'devices' => [
                'total' => $user->devices()->count(),
                'connected' => $user->devices()->where('status', 'connected')->count(),
            ],
            'messages' => [
                'total' => $user->messages()->count(),
                'sent' => $user->messages()->where('status', 'sent')->count(),
                'failed' => $user->messages()->where('status', 'failed')->count(),
                'today' => $user->messages()->whereDate('created_at', today())->count(),
            ],
            'contacts' => [
                'total' => $user->contacts()->count(),
            ],
            'campaigns' => [
                'total' => $user->campaigns()->count(),
                'running' => $user->campaigns()->where('status', 'running')->count(),
            ],
        ];
    }

    #[Computed]
    public function recentMessages()
    {
        return Auth::user()->messages()
            ->with('device')
            ->latest()
            ->take(5)
            ->get();
    }

    #[Computed]
    public function devices()
    {
        return Auth::user()->devices()->latest()->take(4)->get();
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
