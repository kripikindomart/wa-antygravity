<div class="space-y-6 animate-fade-in-up">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Devices</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Manage your WhatsApp devices</p>
        </div>
        <div class="flex gap-2">
            <button wire:click="syncAllDevices" wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 px-4 py-2.5 text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 font-medium rounded-xl transition-colors">
                <svg wire:loading.class="animate-spin" class="w-5 h-5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                    </path>
                </svg>
                <span wire:loading.remove wire:target="syncAllDevices">Sync Status</span>
                <span wire:loading wire:target="syncAllDevices">Syncing...</span>
            </button>
            <button wire:click="openAddModal"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-medium rounded-xl shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/50 transition-all duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Device
            </button>
        </div>
    </div>

    <!-- Device Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($this->devices as $device)
            <div class="glass-card rounded-2xl p-5 hover:shadow-xl transition-all duration-300">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center shadow-lg shadow-emerald-500/30">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-slate-800 dark:text-white">{{ $device->name }}</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                {{ $device->phone_number ?: 'Not connected' }}
                            </p>
                        </div>
                    </div>

                    <!-- Status Badge -->
                    @if($device->status === 'connected')
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            Connected
                        </span>
                    @elseif($device->status === 'scanning')
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            Scanning
                        </span>
                    @else
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400">
                            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                            Disconnected
                        </span>
                    @endif
                </div>

                <!-- Device Info -->
                <div class="mt-4 pt-4 border-t border-slate-200/50 dark:border-slate-700/50">
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div>
                            <p class="text-slate-500 dark:text-slate-400">Created</p>
                            <p class="font-medium text-slate-800 dark:text-white">{{ $device->created_at->diffForHumans() }}
                            </p>
                        </div>
                        @if($device->last_connected_at)
                            <div>
                                <p class="text-slate-500 dark:text-slate-400">Last Active</p>
                                <p class="font-medium text-slate-800 dark:text-white">
                                    {{ $device->last_connected_at->diffForHumans() }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-4 flex gap-2">
                    @if($device->status === 'connected')
                        <button wire:click="disconnectDevice({{ $device->id }})"
                            wire:confirm="Are you sure you want to disconnect this device?"
                            class="flex-1 px-3 py-2 text-sm font-medium text-amber-600 bg-amber-50 dark:bg-amber-900/20 hover:bg-amber-100 dark:hover:bg-amber-900/30 rounded-xl transition-colors">
                            Disconnect
                        </button>
                    @else
                        <button wire:click="reconnectDevice({{ $device->id }})"
                            class="flex-1 px-3 py-2 text-sm font-medium text-emerald-600 bg-emerald-50 dark:bg-emerald-900/20 hover:bg-emerald-100 dark:hover:bg-emerald-900/30 rounded-xl transition-colors">
                            Connect
                        </button>
                    @endif

                    <button wire:click="editDevice({{ $device->id }})"
                        class="px-3 py-2 text-sm font-medium text-slate-600 bg-slate-50 dark:bg-slate-800/50 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                            </path>
                        </svg>
                    </button>

                    <button wire:click="deleteDevice({{ $device->id }})"
                        wire:confirm="Are you sure you want to delete this device? This action cannot be undone."
                        class="px-3 py-2 text-sm font-medium text-rose-600 bg-rose-50 dark:bg-rose-900/20 hover:bg-rose-100 dark:hover:bg-rose-900/30 rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                            </path>
                        </svg>
                    </button>
                </div>
            </div>
        @empty
            <!-- Empty State -->
            <div class="col-span-full">
                <div class="glass-card rounded-2xl p-12 text-center">
                    <div
                        class="w-20 h-20 mx-auto rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center mb-4">
                        <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-800 dark:text-white mb-2">No devices yet</h3>
                    <p class="text-slate-500 dark:text-slate-400 mb-6">Add your first WhatsApp device to get started</p>
                    <button wire:click="openAddModal"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-medium rounded-xl shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/50 transition-all duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Device
                    </button>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Add Device Modal -->
    @if($showAddModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" x-data
            x-init="$el.querySelector('input')?.focus()">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeAddModal"></div>
            <div
                class="relative w-full max-w-md bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-2xl border border-slate-200 dark:border-slate-700 animate-fade-in-up">
                <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-4">
                    {{ $isEditing ? 'Edit Device' : 'Add New Device' }}
                </h3>

                <form wire:submit="save">
                    <div class="space-y-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Device
                                Name</label>
                            <input type="text" wire:model="deviceName" placeholder="e.g. Customer Support"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all">
                            @error('deviceName')
                                <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Webhook URL
                                (Optional)</label>
                            <input type="url" wire:model="webhookUrl" placeholder="https://your-domain.com/webhook"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all">
                            <p class="mt-1 text-xs text-slate-400">Incoming messages will be forwarded to this URL via POST.
                            </p>
                            @error('webhookUrl')
                                <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    @if(!$isEditing)
                        <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">
                            After creating the device, you'll be asked to scan a QR code with your WhatsApp mobile app to
                            connect.
                        </p>
                    @endif

                    <div class="flex gap-3">
                        <button type="button" wire:click="closeAddModal"
                            class="flex-1 px-4 py-2.5 text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 font-medium rounded-xl transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                            class="flex-1 px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-medium rounded-xl shadow-lg shadow-emerald-500/30 transition-all">
                            {{ $isEditing ? 'Update Device' : 'Create & Connect' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- QR Code Modal -->
    @if($showQrModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" wire:poll.2s="pollQrCode">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeQrModal"></div>
            <div
                class="relative w-full max-w-md bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-2xl border border-slate-200 dark:border-slate-700 animate-fade-in-up text-center">
                <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-2">Scan QR Code</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Open WhatsApp on your phone and scan this QR code
                </p>

                <!-- QR Code Display -->
                <div class="relative w-64 h-64 mx-auto mb-6 rounded-2xl overflow-hidden bg-white p-4">
                    @if($qrCode)
                        <img src="{{ $qrCode }}" alt="QR Code" class="w-full h-full object-contain">
                    @elseif($deviceStatus === 'connected')
                        <div class="w-full h-full flex flex-col items-center justify-center bg-emerald-50">
                            <svg class="w-16 h-16 text-emerald-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-emerald-600 font-medium">Connected!</p>
                        </div>
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center">
                            <div
                                class="w-12 h-12 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin mb-4">
                            </div>
                            <p class="text-slate-500 text-sm">Generating QR Code...</p>
                        </div>
                    @endif
                </div>

                <div class="space-y-3">
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        <span class="font-medium">How to scan:</span> Open WhatsApp → Menu → Linked Devices → Link a Device
                    </p>

                    <button wire:click="closeQrModal"
                        class="w-full px-4 py-2.5 text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 font-medium rounded-xl transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>