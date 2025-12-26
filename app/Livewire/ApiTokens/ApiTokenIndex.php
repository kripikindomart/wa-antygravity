<?php

namespace App\Livewire\ApiTokens;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.tenant')]
#[Title('API Tokens')]
class ApiTokenIndex extends Component
{
    public bool $showModal = false;
    public string $tokenName = '';
    public ?string $newToken = null;

    protected $rules = [
        'tokenName' => 'required|min:2|max:50',
    ];

    #[Computed]
    public function tokens()
    {
        return Auth::user()->tokens()->latest()->get();
    }

    public function openModal()
    {
        $this->tokenName = '';
        $this->newToken = null;
        $this->showModal = true;
        $this->resetValidation();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->tokenName = '';
        $this->newToken = null;
    }

    public function createToken()
    {
        $this->validate();

        $token = Auth::user()->createToken($this->tokenName);
        $this->newToken = $token->plainTextToken;

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'API token created! Make sure to copy it now.',
        ]);
    }

    public function deleteToken($tokenId)
    {
        $token = Auth::user()->tokens()->findOrFail($tokenId);
        $token->delete();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Token deleted.',
        ]);
    }

    public function render()
    {
        return view('livewire.api-tokens.api-token-index');
    }
}
