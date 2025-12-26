<?php

namespace App\Livewire\Contacts;

use App\Models\Contact;
use App\Models\ContactGroup;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.tenant')]
#[Title('Contacts')]
class ContactIndex extends Component
{
    use WithPagination;

    public bool $showModal = false;
    public bool $isEditing = false;
    public ?int $editingContactId = null;

    public string $name = '';
    public string $phone_number = '';
    public string $email = '';
    public ?int $contact_group_id = null;

    public string $search = '';
    public ?int $filterGroup = null;

    protected $rules = [
        'name' => 'required|min:2|max:100',
        'phone_number' => 'required|min:10|max:20',
        'email' => 'nullable|email',
        'contact_group_id' => 'nullable|exists:contact_groups,id',
    ];

    #[Computed]
    public function groups()
    {
        return Auth::user()->contactGroups()->orderBy('name')->get();
    }

    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->isEditing = false;
        $this->editingContactId = null;
        $this->name = '';
        $this->phone_number = '';
        $this->email = '';
        $this->contact_group_id = null;
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'phone_number' => $this->phone_number,
            'email' => $this->email ?: null,
            'contact_group_id' => $this->contact_group_id ?: null,
        ];

        if ($this->isEditing && $this->editingContactId) {
            $contact = Auth::user()->contacts()->findOrFail($this->editingContactId);
            $contact->update($data);
            $message = 'Contact updated successfully!';
        } else {
            Auth::user()->contacts()->create($data);
            $message = 'Contact created successfully!';
        }

        $this->closeModal();
        $this->dispatch('notify', ['type' => 'success', 'message' => $message]);
    }

    public function edit($contactId)
    {
        $contact = Auth::user()->contacts()->findOrFail($contactId);

        $this->isEditing = true;
        $this->editingContactId = $contact->id;
        $this->name = $contact->name;
        $this->phone_number = $contact->phone_number;
        $this->email = $contact->email ?? '';
        $this->contact_group_id = $contact->contact_group_id;
        $this->showModal = true;
    }

    public ?int $contactToDelete = null;

    public function confirmDelete($contactId)
    {
        $this->contactToDelete = $contactId;
        $this->dispatch(
            'show-delete-confirmation',
            action: 'delete-contact-confirmed',
            title: 'Delete Contact?',
            text: 'Are you sure you want to delete this contact?'
        );
    }

    #[\Livewire\Attributes\On('delete-contact-confirmed')]
    public function deleteContactConfirmed()
    {
        if ($this->contactToDelete) {
            $contact = Auth::user()->contacts()->findOrFail($this->contactToDelete);
            $contact->delete();
            $this->contactToDelete = null;
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Contact deleted.']);
        }
    }

    // Deprecated direct delete, kept just in case but view will use confirmDelete
    public function delete($contactId)
    {
        $this->confirmDelete($contactId);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterGroup()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Auth::user()->contacts()
            ->with('group')
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('phone_number', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterGroup, function ($q) {
                $q->where('contact_group_id', $this->filterGroup);
            })
            ->latest();

        return view('livewire.contacts.contact-index', [
            'contacts' => $query->paginate(15),
        ]);
    }
}
