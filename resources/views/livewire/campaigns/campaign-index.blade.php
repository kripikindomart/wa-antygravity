<div class="space-y-6 animate-fade-in-up">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Campaigns</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Create and manage broadcast campaigns</p>
        </div>
        <button wire:click="openModal"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-medium rounded-xl shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/50 transition-all duration-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            New Campaign
        </button>
    </div>

    @if($this->devices->isEmpty())
        <div
            class="glass-card rounded-2xl p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                    </path>
                </svg>
                <div>
                    <p class="text-sm font-medium text-amber-800 dark:text-amber-300">No connected devices</p>
                    <p class="text-xs text-amber-700 dark:text-amber-400 mt-1">Connect a WhatsApp device first to create
                        campaigns.</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Campaigns List -->
    <div class="space-y-4">
        @forelse($campaigns as $campaign)
            <div class="glass-card rounded-2xl p-5 hover:shadow-xl transition-all duration-300">
                <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="font-semibold text-slate-800 dark:text-white">{{ $campaign->name }}</h3>
                            @if($campaign->status === 'running')
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-400">
                                    <span class="w-2 h-2 rounded-full bg-sky-500 animate-pulse"></span>
                                    Running
                                </span>
                            @elseif($campaign->status === 'completed')
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Completed</span>
                            @elseif($campaign->status === 'paused')
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Paused</span>
                            @else
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400">Draft</span>
                            @endif
                        </div>

                        <p class="text-sm text-slate-600 dark:text-slate-300 mb-3">{{ Str::limit($campaign->message, 100) }}
                        </p>

                        <div class="flex flex-wrap gap-4 text-sm text-slate-500 dark:text-slate-400">
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z">
                                    </path>
                                </svg>
                                {{ $campaign->total_recipients }} recipients
                            </span>
                            @if($campaign->device)
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                    {{ $campaign->device->name }}
                                </span>
                            @endif
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ $campaign->delay_seconds }}s delay
                            </span>
                        </div>
                    </div>

                    <!-- Progress -->
                    @if(in_array($campaign->status, ['running', 'completed']))
                        <div class="lg:w-48">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-slate-600 dark:text-slate-300">Progress</span>
                                <span
                                    class="font-medium text-slate-800 dark:text-white">{{ $campaign->progress_percentage }}%</span>
                            </div>
                            <div class="h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-500 transition-all duration-300"
                                    style="width: {{ $campaign->progress_percentage }}%"></div>
                            </div>
                            <div class="flex justify-between text-xs text-slate-500 mt-1">
                                <span class="text-emerald-600">{{ $campaign->sent_count }} sent</span>
                                <span class="text-rose-600">{{ $campaign->failed_count }} failed</span>
                            </div>
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="flex gap-2">
                        @if($campaign->status === 'draft')
                            <button wire:click="startCampaign({{ $campaign->id }})"
                                wire:confirm="Start this campaign? Messages will be sent to all recipients."
                                class="px-3 py-2 text-sm font-medium text-white bg-emerald-500 hover:bg-emerald-600 rounded-xl transition-colors">
                                Start
                            </button>
                            <button wire:click="edit({{ $campaign->id }})"
                                class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors text-slate-500 hover:text-sky-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                    </path>
                                </svg>
                            </button>
                        @endif
                        @if($campaign->status === 'running')
                            <button wire:click="pauseCampaign({{ $campaign->id }})"
                                class="px-3 py-2 text-sm font-medium text-amber-600 bg-amber-50 hover:bg-amber-100 rounded-xl transition-colors">
                                Pause
                            </button>
                        @endif
                        <button wire:click="delete({{ $campaign->id }})" wire:confirm="Delete this campaign?"
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
                            d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z">
                        </path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-800 dark:text-white mb-2">No campaigns yet</h3>
                <p class="text-slate-500 dark:text-slate-400 mb-6">Create your first broadcast campaign</p>
                <button wire:click="openModal"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-medium rounded-xl">
                    New Campaign
                </button>
            </div>
        @endforelse
    </div>

    @if($campaigns->hasPages())
        <div class="mt-4">
            {{ $campaigns->links() }}
        </div>
    @endif

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeModal"></div>
            <div
                class="relative w-full max-w-2xl glass-card rounded-2xl p-6 animate-fade-in-up max-h-[90vh] overflow-y-auto">
                <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-4">
                    {{ $isEditing ? 'Edit Campaign' : 'New Campaign' }}
                </h3>

                <form wire:submit="save">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Campaign
                                    Name *</label>
                                <input type="text" wire:model="name" placeholder="e.g. Holiday Promo"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white">
                                @error('name') <p class="mt-1 text-sm text-rose-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Device
                                    *</label>
                                <select wire:model="device_id"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white">
                                    <option value="">Select device...</option>
                                    @foreach($this->devices as $device)
                                        <option value="{{ $device->id }}">{{ $device->name }} ({{ $device->phone_number }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('device_id') <p class="mt-1 text-sm text-rose-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Message
                                *</label>
                            <textarea wire:model="message" rows="4" placeholder="Your broadcast message..."
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white resize-none"></textarea>
                            @error('message') <p class="mt-1 text-sm text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Select
                                Recipients *</label>
                            <div
                                class="max-h-48 overflow-y-auto border border-slate-300 dark:border-slate-600 rounded-xl p-3 space-y-2">
                                @forelse($this->contacts as $contact)
                                    <label
                                        class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/30 cursor-pointer">
                                        <input type="checkbox" wire:model="selectedContacts" value="{{ $contact->id }}"
                                            class="w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                                        <div>
                                            <p class="text-sm font-medium text-slate-800 dark:text-white">{{ $contact->name }}
                                            </p>
                                            <p class="text-xs text-slate-500">{{ $contact->phone_number }}</p>
                                        </div>
                                    </label>
                                @empty
                                    <p class="text-sm text-slate-500 text-center py-4">No contacts available. <a
                                            href="{{ route('contacts.index') }}" class="text-emerald-600">Add contacts
                                            first</a>.</p>
                                @endforelse
                            </div>
                            <p class="mt-1 text-xs text-slate-500">{{ count($selectedContacts) }} selected</p>
                            @error('selectedContacts') <p class="mt-1 text-sm text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Delay Between
                                Messages (seconds)</label>
                            <input type="number" wire:model="delay_seconds" min="5" max="60"
                                class="w-32 px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white">
                            <p class="mt-1 text-xs text-slate-500">Recommended: 10-30 seconds to avoid getting banned</p>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button type="button" wire:click="closeModal"
                            class="flex-1 px-4 py-2.5 text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 font-medium rounded-xl transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                            class="flex-1 px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-medium rounded-xl shadow-lg transition-all">
                            {{ $isEditing ? 'Update' : 'Create Campaign' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>