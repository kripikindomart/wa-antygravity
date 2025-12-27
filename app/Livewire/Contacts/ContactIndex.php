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
use Illuminate\Support\Facades\Http;

#[Layout('components.layouts.tenant')]
#[Title('Contacts')]
class ContactIndex extends Component
{
    use WithPagination;

    public bool $showModal = false;
    public bool $isEditing = false;
    public ?int $editingContactId = null;
    public string $activeTab = 'groups'; // 'groups' or 'uncategorized' (or 'all')
    public array $selectedContacts = []; // For bulk actions
    public bool $selectAll = false; // Select all checkbox state

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

    // Grabber Properties
    public bool $showGrabModal = false;
    public int $grabStep = 1;
    public string $grabMode = 'group';
    public ?int $selectedDeviceId = null;
    public array $waGroups = [];
    public array $selectedWaGroupIds = [];
    public ?int $targetLocalGroupId = null;
    public bool $isGrabbing = false;

    public function mount($groupId = null)
    {
        if ($groupId) {
            $this->filterGroup = $groupId;
        }
    }

    #[Computed]
    public function activeGroup()
    {
        if ($this->filterGroup) {
            return Auth::user()->contactGroups()->find($this->filterGroup);
        }
        return null;
    }

    #[Computed]
    public function groups()
    {
        return Auth::user()->contactGroups()->withCount('contacts')->orderBy('name')->get();
    }

    // Confirm Bulk Delete
    public function confirmBulkDelete()
    {
        if (empty($this->selectedContacts))
            return;

        $count = count($this->selectedContacts);
        $this->dispatch(
            'show-delete-confirmation',
            action: 'delete-selected-confirmed',
            title: "Delete $count Contacts?",
            text: "You are about to delete $count selected contacts. This cannot be undone."
        );
    }

    #[\Livewire\Attributes\On('delete-selected-confirmed')]
    public function deleteSelectedConfirmed()
    {
        if (empty($this->selectedContacts))
            return;

        Auth::user()->contacts()->whereIn('id', $this->selectedContacts)->delete();
        $this->selectedContacts = [];
        $this->selectAll = false;
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Selected contacts deleted successfully.']);
    }

    public ?int $contactToDelete = null;

    public function confirmDelete($contactId)
    {
        $this->contactToDelete = $contactId;
        $this->dispatch(
            'show-delete-confirmation',
            action: 'delete-contact-confirmed',
            title: 'Delete Contact?',
            text: 'This contact will be permanently deleted.'
        );
    }

    #[\Livewire\Attributes\On('delete-contact-confirmed')]
    public function deleteContactConfirmed()
    {
        if ($this->contactToDelete) {
            Auth::user()->contacts()->where('id', $this->contactToDelete)->delete();
            $this->contactToDelete = null;
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Contact deleted successfully.']);
        }
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

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterGroup()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        $this->resetForm();
        if ($this->filterGroup) {
            $this->contact_group_id = $this->filterGroup;
        }
        $this->showModal = true;
    }

    public function openGrabModal()
    {
        $this->resetGrabber();
        if ($this->filterGroup) {
            $this->targetLocalGroupId = $this->filterGroup;
        }
        $this->showGrabModal = true;
    }

    public function closeGrabModal()
    {
        $this->showGrabModal = false;
        $this->resetGrabber();
    }

    public function resetGrabber()
    {
        $this->grabStep = 1;
        $this->grabMode = 'group';
        $this->selectedDeviceId = null;
        $this->waGroups = [];
        $this->selectedWaGroupIds = [];
        $this->targetLocalGroupId = null;
        $this->isGrabbing = false;
    }

    #[Computed]
    public function devices()
    {
        return Auth::user()->devices()->where('status', 'connected')->get();
    }

    public function fetchWaGroups()
    {
        $this->validate([
            'selectedDeviceId' => 'required'
        ]);

        $this->waGroups = [];
        $this->selectedWaGroupIds = [];

        if (!$this->selectedDeviceId)
            return;

        $device = Auth::user()->devices()->find($this->selectedDeviceId);
        if (!$device)
            return;

        try {
            $nodeUrl = config('services.whatsapp.url');
            $response = Http::timeout(15)->get("{$nodeUrl}/sessions/{$device->token}/groups");

            if ($response->successful()) {
                $this->waGroups = $response->json()['groups'] ?? [];
            } else {
                $this->dispatch('notify', ['type' => 'error', 'message' => 'Failed to fetch groups.']);
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Node service unreachable.']);
        }
    }

    public function startGrabbing()
    {
        $this->validate([
            'selectedDeviceId' => 'required',
            'targetLocalGroupId' => 'nullable|exists:contact_groups,id',
            'selectedWaGroupIds' => 'required|array|min:1',
        ]);

        $this->isGrabbing = true;

        try {
            $device = Auth::user()->devices()->find($this->selectedDeviceId);
            $nodeUrl = config('services.whatsapp.url');
            $uniqueContacts = [];

            if ($device) {
                foreach ($this->selectedWaGroupIds as $waGroupId) {
                    $response = Http::timeout(30)->get("{$nodeUrl}/sessions/{$device->token}/groups/{$waGroupId}");
                    if ($response->successful()) {
                        $data = $response->json();
                        // dd($data); // DEBUG REMOVED
                        $participants = $data['participants'] ?? ($data['metadata']['participants'] ?? []);
                        foreach ($participants as $participant) {
                            // Struct: ['id' => '...lid', 'phoneNumber' => '...@s.whatsapp.net', 'admin' => ... ]
                            // We prefer 'phoneNumber' if available to get the actual WA number, not the LID.
                            $jid = '';
                            if (is_array($participant)) {
                                $jid = $participant['phoneNumber'] ?? ($participant['id'] ?? '');
                            } else {
                                $jid = $participant;
                            }

                            $number = explode('@', $jid)[0];
                            if (empty($number) || str_contains($jid, '-'))
                                continue;

                            // Determine Name (prioritize synced name/notify)
                            $name = $number;
                            if (is_array($participant)) {
                                $name = $participant['name'] ?? ($participant['notify'] ?? $number);
                            }

                            if (!isset($uniqueContacts[$number])) {
                                $uniqueContacts[$number] = ['name' => $name, 'phone_number' => $number];
                            }
                        }
                    }
                }

                $count = 0;
                foreach ($uniqueContacts as $contact) {
                    // Check if contact exists IN THE TARGET GROUP specifically
                    // allowing the same number to be in multiple groups
                    $exists = Auth::user()->contacts()
                        ->where('phone_number', $contact['phone_number'])
                        ->where('contact_group_id', $this->targetLocalGroupId)
                        ->exists();

                    if (!$exists) {
                        Auth::user()->contacts()->create([
                            'name' => $contact['name'],
                            'phone_number' => $contact['phone_number'],
                            'contact_group_id' => $this->targetLocalGroupId,
                        ]);
                        $count++;
                    }
                }
                $this->dispatch('notify', ['type' => 'success', 'message' => "Successfully grabbed {$count} contacts."]);
                $this->closeGrabModal();
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
        $this->isGrabbing = false;
    }


    // Create Group Logic
    public bool $showGroupModal = false;
    public string $newGroupName = '';

    public function openCreateGroupModal()
    {
        $this->newGroupName = '';
        $this->showGroupModal = true;
    }

    public function closeGroupModal()
    {
        $this->showGroupModal = false;
        $this->newGroupName = '';
    }

    public function saveGroup()
    {
        $this->validate([
            'newGroupName' => 'required|min:2|max:50|unique:contact_groups,name,NULL,id,user_id,' . Auth::id()
        ]);

        Auth::user()->contactGroups()->create([
            'name' => $this->newGroupName
        ]);

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Group created successfully!']);
        $this->closeGroupModal();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedContacts = $this->getFilteredContactsQuery()->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedContacts = [];
        }
    }

    public function deleteSelected()
    {
        $this->confirmBulkDelete();
    }

    private function getFilteredContactsQuery()
    {
        return Auth::user()->contacts()
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
            // If tab is 'uncategorized' and no specific group filter is set
            ->when($this->activeTab === 'uncategorized' && !$this->filterGroup, function ($q) {
                $q->whereNull('contact_group_id');
            })
            ->latest();
    }

    public function render()
    {
        // Groups query
        $groups = Auth::user()->contactGroups()
            ->withCount('contacts')
            ->orderBy('name')
            ->get();

        return view('livewire.contacts.contact-index', [
            'contacts' => $this->getFilteredContactsQuery()->paginate(15),
            'groups' => $groups,
        ]);
    }
}
