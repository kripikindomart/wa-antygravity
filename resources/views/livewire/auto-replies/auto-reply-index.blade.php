<div class="space-y-6 animate-fade-in-up">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Auto Replies</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Automatically respond to incoming messages</p>
        </div>
        <button wire:click="openModal"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-medium rounded-xl shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/50 transition-all duration-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add Rule
        </button>
    </div>

    <!-- Info Card -->
    <div class="glass-card rounded-2xl p-4 bg-sky-50 dark:bg-sky-900/20 border border-sky-200 dark:border-sky-800">
        <div class="flex gap-3">
            <svg class="w-5 h-5 text-sky-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <p class="text-sm text-sky-800 dark:text-sky-300">Auto-reply rules are triggered when incoming messages
                    match the specified keywords. Higher priority rules are checked first.</p>
            </div>
        </div>
    </div>

    <!-- Rules List -->
    <div class="space-y-4">
        @forelse($this->autoReplyRules as $rule)
            <div
                class="glass-card rounded-2xl p-5 hover:shadow-xl transition-all duration-300 {{ !$rule->is_active ? 'opacity-60' : '' }}">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="font-semibold text-slate-800 dark:text-white">{{ $rule->name }}</h3>
                            @if($rule->is_active)
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Active</span>
                            @else
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400">Inactive</span>
                            @endif
                            <span class="text-xs text-slate-400">Priority: {{ $rule->priority }}</span>
                        </div>

                        <div class="flex flex-wrap gap-2 mb-3">
                            @foreach($rule->keywords as $keyword)
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400">
                                    {{ $keyword }}
                                </span>
                            @endforeach
                        </div>

                        <div class="text-sm text-slate-500 dark:text-slate-400 mb-2">
                            <span class="font-medium">Match:</span> {{ ucfirst(str_replace('_', ' ', $rule->match_type)) }}
                            @if($rule->device)
                                <span class="mx-2">•</span>
                                <span class="font-medium">Device:</span> {{ $rule->device->name }}
                            @else
                                <span class="mx-2">•</span>
                                <span>All Devices</span>
                            @endif
                        </div>

                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-700/30">
                            <p class="text-sm text-slate-600 dark:text-slate-300">
                                {{ Str::limit($rule->reply_message, 150) }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <button wire:click="toggleActive({{ $rule->id }})"
                            class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors {{ $rule->is_active ? 'text-emerald-600' : 'text-slate-400' }}"
                            title="{{ $rule->is_active ? 'Deactivate' : 'Activate' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.07m7.072 0a5 5 0 010 7.07M13 12a1 1 0 11-2 0 1 1 0 012 0z">
                                </path>
                            </svg>
                        </button>
                        <button wire:click="edit({{ $rule->id }})"
                            class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors text-slate-500 hover:text-sky-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                </path>
                            </svg>
                        </button>
                        <button wire:click="delete({{ $rule->id }})" wire:confirm="Delete this auto-reply rule?"
                            class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors text-slate-500 hover:text-rose-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                </path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="glass-card rounded-2xl p-12 text-center">
                <div
                    class="w-20 h-20 mx-auto rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center mb-4">
                    <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z">
                        </path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-800 dark:text-white mb-2">No auto-reply rules</h3>
                <p class="text-slate-500 dark:text-slate-400 mb-6">Create your first rule to automatically respond to
                    messages</p>
                <button wire:click="openModal"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-medium rounded-xl">
                    Add Rule
                </button>
            </div>
        @endforelse
    </div>

    <!-- Add/Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeModal"></div>
            <div
                class="relative w-full max-w-lg glass-card rounded-2xl p-6 animate-fade-in-up max-h-[90vh] overflow-y-auto">
                <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-4">
                    {{ $isEditing ? 'Edit Rule' : 'Add Auto-Reply Rule' }}
                </h3>

                <form wire:submit="save">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Rule Name
                                *</label>
                            <input type="text" wire:model="name" placeholder="e.g. Welcome Message"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white">
                            @error('name') <p class="mt-1 text-sm text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Trigger
                                Keywords *</label>
                            <input type="text" wire:model="keywords" placeholder="hello, hi, halo (comma separated)"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white">
                            <p class="mt-1 text-xs text-slate-500">Separate multiple keywords with commas</p>
                            @error('keywords') <p class="mt-1 text-sm text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Match
                                    Type</label>
                                <select wire:model="match_type"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white">
                                    <option value="contains">Contains</option>
                                    <option value="exact">Exact Match</option>
                                    <option value="starts_with">Starts With</option>
                                    <option value="regex">Regex</option>
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Priority</label>
                                <input type="number" wire:model="priority" min="0"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Apply to
                                Device</label>
                            <select wire:model="device_id"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white">
                                <option value="">All Devices</option>
                                @foreach($this->devices as $device)
                                    <option value="{{ $device->id }}">{{ $device->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Reply Message
                                *</label>
                            <textarea wire:model="reply_message" rows="4" placeholder="Your auto-reply message..."
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white resize-none"></textarea>
                            @error('reply_message') <p class="mt-1 text-sm text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="checkbox" wire:model="is_active" id="is_active"
                                class="w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                            <label for="is_active" class="text-sm text-slate-700 dark:text-slate-300">Active</label>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button type="button" wire:click="closeModal"
                            class="flex-1 px-4 py-2.5 text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 font-medium rounded-xl transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                            class="flex-1 px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-medium rounded-xl shadow-lg transition-all">
                            {{ $isEditing ? 'Update' : 'Create' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>