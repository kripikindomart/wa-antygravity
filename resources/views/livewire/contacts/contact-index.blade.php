<div class="space-y-6 animate-fade-in-up">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            @if($this->activeGroup)
                <button wire:click="$set('filterGroup', null)"
                    class="group flex items-center gap-2 text-sm text-slate-500 hover:text-emerald-600 dark:text-slate-400 dark:hover:text-emerald-400 mb-1 transition-colors">
                    <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Groups
                </button>
                <h2 class="text-2xl font-bold text-slate-800 dark:text-white flex items-center gap-3">
                    {{ $this->activeGroup->name }}
                    <span
                        class="px-2.5 py-1 text-xs rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 font-medium">
                        {{ $this->activeGroup->contacts_count ?? 0 }} contacts
                    </span>
                </h2>
            @else
                <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Contacts</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">Manage your contact list</p>
            @endif
        </div>
        <div class="flex items-center gap-3">
            <button wire:click="openGrabModal"
                class="px-4 py-2.5 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-xl border border-slate-300 dark:border-slate-600 transition-all flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                    </path>
                </svg>
                <span>Grab from WA</span>
            </button>
            <button wire:click="openModal"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-medium rounded-xl shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/50 transition-all duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Contact
            </button>
        </div>
    </div>


    <!-- Tabs (Only if not filtering by specific group) -->
    @if(!$filterGroup)
        <div class="flex items-center gap-6 border-b border-slate-200 dark:border-slate-700 mb-6">
            <button wire:click="$set('activeTab', 'groups')"
                class="pb-3 text-sm font-medium border-b-2 transition-colors {{ $activeTab === 'groups' ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300' }}">
                Groups
            </button>
            <button wire:click="$set('activeTab', 'uncategorized')"
                class="pb-3 text-sm font-medium border-b-2 transition-colors {{ $activeTab === 'uncategorized' ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300' }}">
                Uncategorized Contacts
            </button>
        </div>
    @endif

    @if($activeTab === 'groups' && !$filterGroup)
        <!-- Groups Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 animate-fade-in-up">
            <!-- Create Group Card -->
            <button wire:click="openCreateGroupModal"
                class="bg-white dark:bg-slate-800 rounded-2xl p-5 border-2 border-dashed border-slate-300 dark:border-slate-600 hover:border-emerald-500 dark:hover:border-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-all flex flex-col items-center justify-center gap-3 group h-full min-h-[160px]">
                <div
                    class="w-14 h-14 rounded-full bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400 group-hover:scale-110 transition-transform">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
                <span class="font-semibold text-lg text-slate-700 dark:text-slate-200">Create New Group</span>
            </button>

            @forelse ($groups as $group)
                <div
                    class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/50 dark:border-slate-700/50 shadow-sm hover:shadow-md transition-all group relative">
                    <div class="flex items-start justify-between mb-4">
                        <a href="{{ route('contacts.group', $group->id) }}"
                            class="flex items-center gap-3 hover:opacity-80 transition-opacity flex-1">
                            <div
                                class="w-10 h-10 rounded-xl flex items-center justify-center {{ $group->color ?? 'bg-emerald-500' }} text-white font-bold text-lg shadow-lg opacity-90">
                                {{ substr($group->name, 0, 1) }}
                            </div>
                            <div>
                                <h3
                                    class="font-semibold text-slate-800 dark:text-white group-hover:text-emerald-500 transition-colors">
                                    {{ $group->name }}
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ $group->contacts_count ?? 0 }} members
                                </p>
                            </div>
                        </a>
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-300 mb-4 line-clamp-2 min-h-[2.5rem]">
                        {{ $group->description ?? 'No description provided.' }}
                    </p>
                    <div
                        class="flex items-center justify-between text-xs text-slate-400 border-t border-slate-100 dark:border-slate-700 pt-3">
                        <span>ID: {{ $group->id }}</span>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center text-slate-500">
                    No contact groups found.
                </div>
            @endforelse
        </div>
    @endif

    <!-- Contacts Table (Shown if Uncategorized Tab OR Specific Group Filter) -->
    @if($activeTab === 'uncategorized' || $filterGroup)
        <div class="glass-card rounded-2xl overflow-hidden shadow-sm border border-slate-100 dark:border-slate-700/50">
            <!-- Modern Toolbar -->
            <div
                class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex flex-col sm:flex-row gap-4 justify-between items-center bg-white/50 dark:bg-slate-800/50 backdrop-blur-sm">
                <!-- Left: Bulk Action or Filter Label -->
                <div class="flex items-center gap-3">
                    @if(count($selectedContacts) > 0)
                        <div
                            class="flex items-center gap-3 animate-fade-in px-3 py-1.5 bg-rose-50 dark:bg-rose-900/20 rounded-lg border border-rose-100 dark:border-rose-800/30">
                            <span class="text-sm font-medium text-rose-600 dark:text-rose-400">{{ count($selectedContacts) }}
                                selected</span>
                            <div class="h-4 w-px bg-rose-200 dark:bg-rose-700"></div>
                            <button wire:click="deleteSelected"
                                class="text-sm font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                    </path>
                                </svg>
                                Delete
                            </button>
                        </div>
                    @else
                        <!-- Filter Badge if active (e.g. Group Name) -->
                        @if($filterGroup)
                            @php $groupName = $groups->firstWhere('id', $filterGroup)->name ?? 'Unknown Group'; @endphp
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                                Filter: {{ $groupName }}
                            </span>
                        @endif
                    @endif
                </div>

                <!-- Right: Search Bar -->
                <div class="relative w-full sm:w-72">
                    <input type="text" wire:model.live.debounce.300ms="search"
                        class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-900/50 border-none rounded-xl text-sm font-medium text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-emerald-500/50 focus:bg-white dark:focus:bg-slate-800 transition-all placeholder:text-slate-400"
                        placeholder="Search...">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-slate-700/30 border-b border-slate-100 dark:border-slate-700">
                            <th class="px-6 py-4 w-10">
                                <input type="checkbox" wire:model.live="selectAll"
                                    class="rounded border-slate-300 dark:border-slate-600 text-emerald-500 shadow-sm focus:border-emerald-300 focus:ring focus:ring-emerald-200 focus:ring-opacity-50 transition-colors">
                            </th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                Name</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                                Phone</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                                Group</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                                Added</th>
                            <th
                                class="px-6 py-4 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse($contacts as $contact)
                            <tr
                                class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors {{ in_array($contact->id, $selectedContacts) ? 'bg-emerald-50/50 dark:bg-emerald-900/10' : '' }}">
                                <td class="px-6 py-4">
                                    <input type="checkbox" wire:model.live="selectedContacts" value="{{ $contact->id }}"
                                        class="rounded border-slate-300 dark:border-slate-600 text-emerald-500 shadow-sm focus:border-emerald-300 focus:ring focus:ring-emerald-200 focus:ring-opacity-50 transition-colors">
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center text-white font-semibold text-sm shadow-lg">
                                            {{ strtoupper(substr($contact->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-slate-800 dark:text-white">{{ $contact->name }}</p>
                                            @if($contact->email)
                                                <p class="text-xs text-slate-500">{{ $contact->email }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ $contact->phone_number }}</td>
                                <td class="px-6 py-4">
                                    @if($contact->group)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            style="background-color: {{ $contact->group->color }}20; color: {{ $contact->group->color }}">
                                            {{ $contact->group->name }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-slate-500 dark:text-slate-400 text-sm">
                                    {{ $contact->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button wire:click="edit({{ $contact->id }})"
                                            class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors text-slate-500 hover:text-sky-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                                </path>
                                            </svg>
                                        </button>
                                        <button wire:click="confirmDelete({{ $contact->id }})"
                                            class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors text-slate-500 hover:text-rose-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-slate-300 dark:text-slate-600 mb-4" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                            </path>
                                        </svg>
                                        <p class="text-slate-500 dark:text-slate-400 mb-2">No contacts found</p>
                                        <button wire:click="openModal"
                                            class="text-emerald-600 hover:text-emerald-700 font-medium">Add your first
                                            contact</button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($contacts->hasPages())
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700">
                    {{ $contacts->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- Add/Edit Contact Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" x-data
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="closeModal"></div>
            <div
                class="relative w-full max-w-md bg-white dark:bg-slate-800 rounded-3xl p-8 shadow-2xl border border-white/20 animate-scale-in">
                <div class="text-center mb-8">
                    <div
                        class="w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-600 mx-auto flex items-center justify-center text-white mb-4 shadow-lg shadow-emerald-500/30">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-800 dark:text-white">
                        {{ $isEditing ? 'Edit Contact' : 'New Contact' }}
                    </h3>
                    <p class="text-slate-500 dark:text-slate-400 mt-1">Fill in the details below</p>
                </div>

                <form wire:submit="save" class="space-y-5">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Full Name</label>
                        <input type="text" wire:model="name"
                            class="block px-4 py-3 w-full text-sm text-slate-900 bg-slate-50 dark:bg-slate-900/50 rounded-xl border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 transition-colors placeholder:text-slate-400"
                            placeholder="e.g. John Doe" />
                        @error('name') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Phone Number</label>
                        <input type="text" wire:model="phone_number"
                            class="block px-4 py-3 w-full text-sm text-slate-900 bg-slate-50 dark:bg-slate-900/50 rounded-xl border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 transition-colors placeholder:text-slate-400"
                            placeholder="e.g. 628123456789" />
                        @error('phone_number') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Email Address <span
                                class="text-slate-400 font-normal">(Optional)</span></label>
                        <input type="email" wire:model="email"
                            class="block px-4 py-3 w-full text-sm text-slate-900 bg-slate-50 dark:bg-slate-900/50 rounded-xl border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 transition-colors placeholder:text-slate-400"
                            placeholder="john@example.com" />
                        @error('email') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Assign Group</label>
                        <select wire:model="contact_group_id"
                            class="block px-4 py-3 w-full text-sm text-slate-900 bg-slate-50 dark:bg-slate-900/50 rounded-xl border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 transition-colors cursor-pointer">
                            <option value="">Uncategorized</option>
                            @foreach($this->groups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button type="button" wire:click="closeModal"
                            class="flex-1 px-4 py-3 text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-600 font-semibold rounded-xl transition-all">
                            Cancel
                        </button>
                        <button type="submit"
                            class="flex-1 px-4 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-semibold rounded-xl shadow-lg shadow-emerald-500/30 transform hover:scale-[1.02] transition-all">
                            {{ $isEditing ? 'Save Changes' : 'Create Contact' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modern Grabber Modal -->
    @if($showGrabModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="closeGrabModal"></div>
            <div
                class="relative w-full max-w-xl bg-white dark:bg-slate-800 rounded-3xl p-0 shadow-2xl border border-white/20 animate-scale-in flex flex-col max-h-[90vh] overflow-hidden">
                <!-- Header -->
                <div
                    class="flex-none p-6 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
                            <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                </path>
                            </svg>
                            Grab Contacts
                        </h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Import contacts directly from WhatsApp</p>
                    </div>
                    <button wire:click="closeGrabModal"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>

                <!-- Scrollable Body -->
                <div class="flex-1 overflow-y-auto p-8 space-y-6 custom-scrollbar">
                    <!-- Step 1: Mode Selection (Visual) -->
                    <div class="grid grid-cols-2 gap-4">
                        <div
                            class="relative p-4 rounded-2xl border-2 border-emerald-500 bg-emerald-50/50 dark:bg-emerald-900/20 cursor-pointer flex flex-col items-center gap-2 transition-all">
                            <div class="absolute top-2 right-2 text-emerald-500">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div
                                class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                    </path>
                                </svg>
                            </div>
                            <span class="font-semibold text-slate-800 dark:text-white">From Group</span>
                        </div>
                        <div
                            class="p-4 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 opacity-60 cursor-not-allowed flex flex-col items-center gap-2 grayscale">
                            <div
                                class="w-12 h-12 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                </svg>
                            </div>
                            <span class="font-medium text-slate-500">Sync All (Soon)</span>
                        </div>
                    </div>

                    <!-- Form Inputs -->
                    <div class="space-y-6">
                        <!-- Custom CSS for scroll handled in header style block -->

                        <div class="relative group">
                            <select wire:model.live="selectedDeviceId"
                                class="block px-4 py-3 w-full text-sm text-slate-900 bg-slate-50 dark:bg-slate-900/50 rounded-xl border-slate-200 dark:border-slate-700 appearance-none focus:outline-none focus:ring-0 focus:border-emerald-500 peer transition-colors cursor-pointer">
                                <option value="">Select Device</option>
                                @foreach($this->devices as $device)
                                    <option value="{{ $device->id }}">{{ $device->name }} ({{ $device->phone_number }})</option>
                                @endforeach
                            </select>
                            <label
                                class="absolute text-sm text-slate-500 dark:text-slate-400 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-transparent px-2 peer-focus:px-2 peer-focus:text-emerald-500 left-2">Source
                                Device</label>
                        </div>

                        <!-- WhatsApp Group Selection -->
                        @if($selectedDeviceId)
                            <div class="space-y-4 animate-fade-in" x-data="{ loaded: @entangle('waGroups').live }">
                                <!-- Header Action -->
                                <div class="flex justify-between items-end">
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300">WhatsApp
                                            Groups</label>
                                        <p class="text-xs text-slate-400 mt-1">Select groups to import contacts from</p>
                                    </div>
                                    <button wire:click="fetchWaGroups" wire:loading.attr="disabled"
                                        class="text-xs px-3 py-1.5 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400 dark:hover:bg-emerald-900/50 rounded-lg flex items-center gap-2 transition-all font-medium border border-emerald-100 dark:border-emerald-800">
                                        <svg wire:loading.remove wire:target="fetchWaGroups" class="w-4 h-4" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                            </path>
                                        </svg>
                                        <svg wire:loading wire:target="fetchWaGroups" class="w-4 h-4 animate-spin" fill="none"
                                            viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                        Refresh Groups
                                    </button>
                                </div>

                                <!-- Info Alert -->
                                <div
                                    class="bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-300 p-3 rounded-lg text-xs flex items-start gap-2 border border-blue-100 dark:border-blue-800">
                                    <svg class="w-4 h-4 mt-0.5 flex-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Contact names are retrieved from local sync cache. If sync is incomplete, phone
                                        numbers will be used.</span>
                                </div>

                                <!-- List Area -->
                                <div
                                    class="relative border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-900/50 h-56 overflow-hidden flex flex-col">
                                    <!-- Empty/Loading State -->
                                    @if(empty($waGroups))
                                        <div
                                            class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 p-6 text-center z-0">
                                            <div
                                                class="w-12 h-12 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mb-3">
                                                <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                                    </path>
                                                </svg>
                                            </div>
                                            <p class="text-sm font-medium text-slate-500">No groups loaded</p>
                                            <p class="text-xs mt-1">Click "Refresh Groups" to fetch from device</p>
                                        </div>
                                    @else
                                        <div class="overflow-y-auto p-2 space-y-1 h-full z-0 custom-scrollbar">
                                            @foreach($waGroups as $id => $group)
                                                <label
                                                    class="flex items-center p-3 rounded-lg hover:bg-white dark:hover:bg-slate-800 cursor-pointer transition-all border border-transparent hover:border-slate-200 dark:hover:border-slate-700 group select-none">
                                                    <div class="relative flex items-center">
                                                        <input type="checkbox" wire:model="selectedWaGroupIds"
                                                            value="{{ $group['id'] }}"
                                                            class="peer h-5 w-5 rounded-md border-slate-300 dark:border-slate-600 text-emerald-500 focus:ring-emerald-500 transition-all cursor-pointer">
                                                    </div>
                                                    <div class="ml-3 flex-1">
                                                        <p
                                                            class="text-sm font-medium text-slate-800 dark:text-slate-200 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                                                            {{ $group['subject'] ?? 'Unknown Group' }}
                                                        </p>
                                                        <p class="text-xs text-slate-500">{{ $group['size'] ?? 0 }} participants</p>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    @endif

                                    <!-- Loading Overlay -->
                                    <div wire:loading.flex wire:target="fetchWaGroups"
                                        class="absolute inset-0 bg-white/90 dark:bg-slate-900/90 items-center justify-center z-10 backdrop-blur-sm transition-all">
                                        <div class="flex flex-col items-center gap-3">
                                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-500"></div>
                                            <span class="text-emerald-600 font-medium text-sm animate-pulse">Syncing
                                                Groups...</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Target Local Group -->
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Save to Local
                                        Group</label>
                                    <select wire:model="targetLocalGroupId"
                                        class="block px-4 py-3 w-full text-sm text-slate-900 bg-slate-50 dark:bg-slate-900/50 rounded-xl border-slate-200 dark:border-slate-700 appearance-none focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 peer transition-colors cursor-pointer">
                                        <option value="">Uncategorized (No Group)</option>
                                        @foreach($this->groups as $group)
                                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Footer -->
                <div
                    class="flex-none p-6 border-t border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 flex justify-end gap-3 rounded-b-3xl">
                    <button wire:click="closeGrabModal"
                        class="px-5 py-2.5 text-slate-600 dark:text-slate-300 font-medium hover:bg-white dark:hover:bg-slate-700 rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button wire:click="startGrabbing" wire:loading.attr="disabled"
                        class="px-6 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-semibold rounded-xl shadow-lg shadow-emerald-500/30 ring-4 ring-emerald-500/20 disabled:opacity-50 disabled:ring-0 flex items-center gap-2 transition-all">
                        <svg wire:loading wire:target="startGrabbing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Start Import
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Create Group Modal -->
    @if($showGroupModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="closeGroupModal"></div>
            <div
                class="relative w-full max-w-md bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-2xl border border-white/20 animate-scale-in">
                <div class="text-center mb-6">
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-600 mx-auto flex items-center justify-center text-white mb-4 shadow-lg shadow-emerald-500/30">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white">Create New Group</h3>
                </div>

                <form wire:submit="saveGroup" class="space-y-6">
                    <div class="relative group">
                        <input type="text" wire:model="newGroupName" placeholder=" " autofocus
                            class="block px-4 py-3 w-full text-sm text-slate-900 bg-slate-50 dark:bg-slate-900/50 rounded-xl border-slate-200 dark:border-slate-700 appearance-none focus:outline-none focus:ring-0 focus:border-emerald-500 peer transition-colors" />
                        <label
                            class="absolute text-sm text-slate-500 dark:text-slate-400 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-transparent px-2 peer-focus:px-2 peer-focus:text-emerald-500 left-2">Group
                            Name</label>
                        @error('newGroupName') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" wire:click="closeGroupModal"
                            class="flex-1 px-4 py-2.5 text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-600 font-semibold rounded-xl transition-all">Cancel</button>
                        <button type="submit"
                            class="flex-1 px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-semibold rounded-xl shadow-lg shadow-emerald-500/30 transform hover:scale-[1.02] transition-all">Create
                            Group</button>
                    </div>
                </form>
            </div>
        </div>
    @endif