<div class="space-y-6 animate-fade-in-up">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Messages</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Send and track your WhatsApp messages</p>
        </div>
    </div>

    <!-- Message Composer -->
    <div class="glass-card rounded-2xl p-6">
        <h3 class="text-lg font-semibold text-slate-800 dark:text-white mb-4">Send Message</h3>

        @if($this->devices->isEmpty())
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-amber-800 dark:text-amber-300">No connected devices</p>
                        <p class="text-xs text-amber-700 dark:text-amber-400 mt-1">Please connect a WhatsApp device first
                            before sending messages.</p>
                        <a href="{{ route('devices.index') }}" wire:navigate
                            class="inline-block mt-2 text-xs font-medium text-amber-700 hover:text-amber-800 underline">Go
                            to Devices →</a>
                    </div>
                </div>
            </div>
        @else
            <form wire:submit="sendMessage">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Select Device
                                *</label>
                            <select wire:model="selectedDeviceId"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all">
                                <option value="">Choose a device...</option>
                                @foreach($this->devices as $device)
                                    <option value="{{ $device->id }}">{{ $device->name }} ({{ $device->phone_number }})</option>
                                @endforeach
                            </select>
                            @error('selectedDeviceId') <p class="mt-1 text-sm text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Recipient
                                Number *</label>
                            <input type="text" wire:model="recipient" placeholder="+62 812 xxxx xxxx"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all">
                            @error('recipient') <p class="mt-1 text-sm text-rose-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Message *</label>
                        <textarea wire:model="messageText" rows="4" placeholder="Type your message here..."
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all resize-none"></textarea>
                        @error('messageText') <p class="mt-1 text-sm text-rose-500">{{ $message }}</p> @enderror
                        <div class="mt-2 flex items-center justify-end">
                            <button type="submit"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white text-sm font-medium rounded-xl shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/50 transition-all duration-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                                Send
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        @endif
    </div>

    <!-- Message History -->
    <div class="glass-card rounded-2xl p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Message History</h3>
            <select wire:model.live="filterStatus"
                class="px-3 py-1.5 text-sm rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white">
                <option value="">All Status</option>
                <option value="sent">Sent</option>
                <option value="pending">Pending</option>
                <option value="failed">Failed</option>
            </select>
        </div>

        <div class="space-y-3">
            @forelse($messages as $msg)
                <div
                    class="flex items-center gap-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-700/30 hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-colors">
                    <div
                        class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center text-white font-semibold text-sm shadow-lg flex-shrink-0">
                        {{ strtoupper(substr($msg->to, -4, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-medium text-slate-800 dark:text-white">{{ $msg->to }}</p>
                            @if($msg->device)
                                <span class="text-xs text-slate-400">via {{ $msg->device->name }}</span>
                            @endif
                        </div>
                        <p class="text-sm text-slate-600 dark:text-slate-300 truncate mt-0.5">{{ $msg->message }}</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        @if($msg->status === 'sent')
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                                Sent
                            </span>
                        @elseif($msg->status === 'pending')
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                                Pending
                            </span>
                        @elseif($msg->status === 'failed')
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400">
                                Failed
                            </span>
                        @endif
                        <p class="text-xs text-slate-400 mt-1">{{ $msg->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="flex gap-1">
                        @if($msg->status === 'failed')
                            <button wire:click="retryMessage({{ $msg->id }})"
                                class="p-1.5 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-500 hover:text-emerald-600 transition-colors"
                                title="Retry">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                    </path>
                                </svg>
                            </button>
                        @endif
                        <button wire:click="deleteMessage({{ $msg->id }})" wire:confirm="Delete this message?"
                            class="p-1.5 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-500 hover:text-rose-600 transition-colors"
                            title="Delete">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                </path>
                            </svg>
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <svg class="w-12 h-12 mx-auto text-slate-300 dark:text-slate-600 mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                        </path>
                    </svg>
                    <p class="text-slate-500 dark:text-slate-400">No messages yet</p>
                </div>
            @endforelse
        </div>

        @if($messages->hasPages())
            <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                {{ $messages->links() }}
            </div>
        @endif
    </div>
</div>