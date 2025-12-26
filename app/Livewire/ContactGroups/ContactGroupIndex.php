<?php

namespace App\Livewire\ContactGroups;

use App\Models\ContactGroup;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ContactGroupIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $editMode = false;
    public $groupId;

    // Form properties
    public $name;
    public $description;

    protected $rules = [
        'name' => 'required|min:3|max:255',
        'description' => 'nullable|max:500',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->reset(['name', 'description', 'groupId']);
        $this->editMode = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $group = Auth::user()->contactGroups()->findOrFail($id);
        $this->groupId = $group->id;
        $this->name = $group->name;
        $this->description = $group->description;
        $this->editMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->editMode) {
            $group = Auth::user()->contactGroups()->findOrFail($this->groupId);
            $group->update([
                'name' => $this->name,
                'description' => $this->description,
            ]);
            $message = 'Contact group updated successfully.';
        } else {
            Auth::user()->contactGroups()->create([
                'name' => $this->name,
                'description' => $this->description,
                'color' => 'bg-emerald-500', // Default color
            ]);
            $message = 'Contact group created successfully.';
        }

        $this->showModal = false;
        $this->dispatch('notify', ['type' => 'success', 'message' => $message]);
    }

    public ?int $groupToDelete = null;

    public function confirmDelete($id)
    {
        $this->groupToDelete = $id;
        $this->dispatch(
            'show-delete-confirmation',
            action: 'delete-group-confirmed',
            title: 'Delete Contact Group?',
            text: 'This will delete the group. Contacts within the group will NOT be deleted.'
        );
    }

    #[\Livewire\Attributes\On('delete-group-confirmed')]
    public function deleteGroupConfirmed()
    {
        if ($this->groupToDelete) {
            $group = Auth::user()->contactGroups()->findOrFail($this->groupToDelete);
            $group->delete();
            $this->groupToDelete = null;
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Group deleted.']);
        }
    }

    public function render()
    {
        $groups = Auth::user()->contactGroups()
            ->where('name', 'like', '%' . $this->search . '%')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.contact-groups.contact-group-index', [
            'groups' => $groups,
        ])->layout('components.layouts.tenant', ['title' => 'Contact Groups']);
    }
}
