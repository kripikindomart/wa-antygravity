<?php

namespace App\Livewire\AutoReplies;

use App\Models\AutoReply;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.tenant')]
#[Title('Auto Replies')]
class AutoReplyIndex extends Component
{
    public bool $showModal = false;
    public bool $isEditing = false;
    public ?int $editingId = null;

    public string $name = '';
    public string $keywords = '';
    public string $match_type = 'contains';
    public string $reply_message = '';
    public ?int $device_id = null;
    public bool $is_active = true;
    public int $priority = 0;

    protected $rules = [
        'name' => 'required|min:2|max:100',
        'keywords' => 'required',
        'match_type' => 'required|in:exact,contains,starts_with,regex',
        'reply_message' => 'required|min:1',
        'device_id' => 'nullable|exists:devices,id',
        'is_active' => 'boolean',
        'priority' => 'integer|min:0',
    ];

    #[Computed]
    public function autoReplyRules()
    {
        return Auth::user()->autoReplies()->with('device')->orderByDesc('priority')->get();
    }

    #[Computed]
    public function devices()
    {
        return Auth::user()->devices()->where('status', 'connected')->get();
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
        $this->keywords = '';
        $this->match_type = 'contains';
        $this->reply_message = '';
        $this->device_id = null;
        $this->is_active = true;
        $this->priority = 0;
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate();

        // Convert keywords string to array
        $keywordsArray = array_map('trim', explode(',', $this->keywords));
        $keywordsArray = array_filter($keywordsArray);

        $data = [
            'name' => $this->name,
            'keywords' => $keywordsArray,
            'match_type' => $this->match_type,
            'reply_message' => $this->reply_message,
            'device_id' => $this->device_id ?: null,
            'is_active' => $this->is_active,
            'priority' => $this->priority,
        ];

        if ($this->isEditing && $this->editingId) {
            $rule = Auth::user()->autoReplies()->findOrFail($this->editingId);
            $rule->update($data);
            $message = 'Auto-reply rule updated!';
        } else {
            Auth::user()->autoReplies()->create($data);
            $message = 'Auto-reply rule created!';
        }

        $this->closeModal();
        $this->dispatch('notify', ['type' => 'success', 'message' => $message]);
    }

    public function edit($id)
    {
        $rule = Auth::user()->autoReplies()->findOrFail($id);

        $this->isEditing = true;
        $this->editingId = $rule->id;
        $this->name = $rule->name;
        $this->keywords = implode(', ', $rule->keywords);
        $this->match_type = $rule->match_type;
        $this->reply_message = $rule->reply_message;
        $this->device_id = $rule->device_id;
        $this->is_active = $rule->is_active;
        $this->priority = $rule->priority;
        $this->showModal = true;
    }

    public function toggleActive($id)
    {
        $rule = Auth::user()->autoReplies()->findOrFail($id);
        $rule->update(['is_active' => !$rule->is_active]);

        $status = $rule->is_active ? 'activated' : 'deactivated';
        $this->dispatch('notify', ['type' => 'success', 'message' => "Rule {$status}."]);
    }

    public ?int $replyToDelete = null;

    public function confirmDelete($replyId)
    {
        $this->replyToDelete = $replyId;
        $this->dispatch(
            'show-delete-confirmation',
            action: 'delete-reply-confirmed',
            title: 'Delete Auto-Reply?',
            text: 'This rule will be permanently deleted.'
        );
    }

    #[\Livewire\Attributes\On('delete-reply-confirmed')]
    public function deleteReplyConfirmed()
    {
        if ($this->replyToDelete) {
            $rule = Auth::user()->autoReplies()->findOrFail($this->replyToDelete);
            $rule->delete();
            $this->replyToDelete = null;
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Rule deleted.']);
        }
    }

    public function delete($id)
    {
        $this->confirmDelete($id);
    }

    public function render()
    {
        return view('livewire.auto-replies.auto-reply-index');
    }
}
