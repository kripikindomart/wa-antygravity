<div>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Contact Groups</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage groups for targeted campaigns.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('contacts.all', ['action' => 'grab']) }}"
                class="px-4 py-2 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-xl border border-slate-300 dark:border-slate-600 transition-all flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                    </path>
                </svg>
                <span>Grab from WA</span>
            </a>
            <a href="{{ route('contacts.all') }}"
                class="px-4 py-2 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-xl border border-slate-300 dark:border-slate-600 transition-all flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span>View All Contacts</span>
            </a>
            <button wire:click="create"
                class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-medium rounded-xl transition-all shadow-lg shadow-emerald-500/30 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>New Group</span>
            </button>
        </div>
    </div>

    {{-- Search --}}
    <div class="mb-6">
        <div class="relative max-w-md">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search groups..."
                class="w-full pl-10 pr-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:text-white transition-all shadow-sm">
            <svg class="w-5 h-5 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
    </div>

    {{-- Content --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
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
                    <div
                        class="flex items-center gap-1 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity">
                        <button wire:click="edit({{ $group->id }})"
                            class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 hover:text-emerald-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                </path>
                            </svg>
                        </button>
                        <button wire:click="confirmDelete({{ $group->id }})"
                            class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 hover:text-rose-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                </path>
                            </svg>
                        </button>
                    </div>
                </div>

                <p class="text-sm text-slate-600 dark:text-slate-300 mb-4 line-clamp-2 min-h-[2.5rem]">
                    {{ $group->description ?? 'No description provided.' }}
                </p>

                <div
                    class="flex items-center justify-between text-xs text-slate-400 border-t border-slate-100 dark:border-slate-700 pt-3">
                    <span>Created {{ $group->created_at->format('M d, Y') }}</span>
                    <span>ID: {{ $group->id }}</span>
                </div>
            </div>
        @empty
            <div class="col-span-full flex flex-col items-center justify-center py-12 text-center">
                <div class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-slate-800 dark:text-white mb-2">No contact groups</h3>
                <p class="text-slate-500 max-w-sm mx-auto mb-6">Create groups to organize your contacts for targeted
                    campaigns.</p>
                <button wire:click="create"
                    class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-medium rounded-lg transition-colors">
                    Create Group
                </button>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $groups->links() }}
    </div>

    {{-- Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/75 transition-opacity" aria-hidden="true"
                    wire:click="$set('showModal', false)"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div
                    class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <div class="px-6 py-5 border-b border-slate-200/50 dark:border-slate-700/50">
                        <h3 class="text-lg font-semibold text-slate-800 dark:text-white" id="modal-title">
                            {{ $editMode ? 'Edit Group' : 'New Contact Group' }}
                        </h3>
                    </div>
                    <div class="px-6 py-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Group
                                Name</label>
                            <input wire:model="name" type="text"
                                class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:text-white transition-all">
                            @error('name')
                                <span class="text-xs text-rose-500 mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Description</label>
                            <textarea wire:model="description" rows="3"
                                class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:text-white transition-all"></textarea>
                            @error('description')
                                <span class="text-xs text-rose-500 mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div
                        class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 flex flex-row-reverse gap-2 border-t border-slate-200/50 dark:border-slate-700/50">
                        <button wire:click="save" wire:loading.attr="disabled"
                            class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-medium rounded-xl shadow-lg shadow-emerald-500/20 transition-all">
                            {{ $editMode ? 'Update' : 'Create' }}
                        </button>
                        <button wire:click="$set('showModal', false)"
                            class="px-4 py-2 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 text-sm font-medium rounded-xl transition-all">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>