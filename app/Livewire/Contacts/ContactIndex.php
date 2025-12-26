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

    // Grabber Properties
    public bool $showGrabModal = false;
    public int $grabStep = 1; // 1: Select Device & Mode, 2: Select Group (if applicable), 3: Processing
    public string $grabMode = 'group'; // 'all' or 'group'
    public ?int $selectedDeviceId = null;
    public array $waGroups = [];
    public ?string $selectedWaGroupId = null;
    public ?int $targetLocalGroupId = null;
    public bool $isGrabbing = false;

    public function openGrabModal()
    {
        $this->resetGrabber();
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
        $this->selectedWaGroupId = null;
        $this->targetLocalGroupId = null;
        $this->isGrabbing = false;
    }

    #[Computed]
    public function devices()
    {
        return Auth::user()->devices()->where('status', 'connected')->get();
    }

    public function updatedSelectedDeviceId()
    {
        if ($this->selectedDeviceId && $this->grabMode === 'group') {
            $this->fetchWaGroups();
        }
    }

    public function updatedGrabMode()
    {
        if ($this->selectedDeviceId && $this->grabMode === 'group') {
            $this->fetchWaGroups();
        }
    }

    public function fetchWaGroups()
    {
        if (!$this->selectedDeviceId)
            return;

        $device = Auth::user()->devices()->find($this->selectedDeviceId);
        if (!$device)
            return;

        try {
            $nodeUrl = config('services.whatsapp.url');
            $response = \Illuminate\Support\Facades\Http::timeout(15)->get("{$nodeUrl}/sessions/{$device->token}/groups");

            if ($response->successful()) {
                $this->waGroups = $response->json()['groups'] ?? [];
            } else {
                $this->dispatch('notify', ['type' => 'error', 'message' => 'Failed to fetch groups from WhatsApp.']);
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
            'selectedWaGroupId' => 'required_if:grabMode,group',
        ]);

        $this->isGrabbing = true;

        try {
            $device = Auth::user()->devices()->find($this->selectedDeviceId);
            $nodeUrl = config('services.whatsapp.url');
            $contactsToSave = [];

            if ($this->grabMode === 'group') {
                // Fetch group participants
                $response = \Illuminate\Support\Facades\Http::timeout(30)->get("{$nodeUrl}/sessions/{$device->token}/groups/{$this->selectedWaGroupId}");

                if ($response->successful()) {
                    $metadata = $response->json()['metadata'] ?? [];
                    $participants = $metadata['participants'] ?? [];

                    foreach ($participants as $participant) {
                        $jid = $participant['id'];
                        // Skip if it's the sender himself
                        if (str_contains($jid, $device->body))
                            continue;

                        $number = explode('@', $jid)[0];
                        $contactsToSave[] = [
                            'name' => 'WA User ' . $number, // Default name, maybe fetch deeper if needed
                            'number' => $number,
                        ];
                    }
                }
            } else {
                // Grab All logic (future implementation or if endpoint exists)
                // For now, limited to Group Grabber as per immediate requirement availability
            }

            $count = 0;
            foreach ($contactsToSave as $c) {
                // Check if exists
                if (!Auth::user()->contacts()->where('phone_number', $c['number'])->exists()) {
                    Auth::user()->contacts()->create([
                        'name' => $c['name'],
                        'phone_number' => $c['number'],
                        'contact_group_id' => $this->targetLocalGroupId,
                    ]);
                    $count++;
                }
            }

            $this->dispatch('notify', ['type' => 'success', 'message' => "Successfully grabbed {$count} new contacts."]);
            $this->closeGrabModal();

        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Error during grabbing: ' . $e->getMessage()]);
        } finally {
            $this->isGrabbing = false;
        }
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
