<div class="space-y-6 animate-fade-in-up">
    <!-- Welcome Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Welcome back, {{ Auth::user()->name }}! 👋
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Here's what's happening with your WhatsApp Gateway</p>
        </div>
        <a href="{{ route('messages.index') }}" wire:navigate
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-medium rounded-xl shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/50 transition-all duration-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
            </svg>
            Send Message
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Devices -->
        <div class="glass-card rounded-2xl p-5 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Active Devices</p>
                    <p class="text-3xl font-bold text-slate-800 dark:text-white mt-1">
                        {{ $this->stats['devices']['connected'] }}</p>
                    <p class="text-xs text-slate-400 mt-1">of {{ $this->stats['devices']['total'] }} total</p>
                </div>
                <div
                    class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center shadow-lg shadow-emerald-500/30">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Messages Sent -->
        <div class="glass-card rounded-2xl p-5 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Messages Sent</p>
                    <p class="text-3xl font-bold text-slate-800 dark:text-white mt-1">
                        {{ number_format($this->stats['messages']['sent']) }}</p>
                    <p class="text-xs text-emerald-600 mt-1">{{ $this->stats['messages']['today'] }} today</p>
                </div>
                <div
                    class="w-12 h-12 rounded-xl bg-gradient-to-br from-sky-400 to-blue-500 flex items-center justify-center shadow-lg shadow-sky-500/30">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                        </path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Contacts -->
        <div class="glass-card rounded-2xl p-5 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Total Contacts</p>
                    <p class="text-3xl font-bold text-slate-800 dark:text-white mt-1">
                        {{ number_format($this->stats['contacts']['total']) }}</p>
                </div>
                <div
                    class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-400 to-purple-500 flex items-center justify-center shadow-lg shadow-violet-500/30">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Success Rate -->
        <div class="glass-card rounded-2xl p-5 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Success Rate</p>
                    @php
                        $total = $this->stats['messages']['sent'] + $this->stats['messages']['failed'];
                        $rate = $total > 0 ? round(($this->stats['messages']['sent'] / $total) * 100, 1) : 100;
                    @endphp
                    <p class="text-3xl font-bold text-slate-800 dark:text-white mt-1">{{ $rate }}%</p>
                    <p class="text-xs text-slate-400 mt-1">{{ $this->stats['messages']['failed'] }} failed</p>
                </div>
                <div
                    class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-lg shadow-amber-500/30">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                        </path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Messages -->
        <div class="glass-card rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Recent Messages</h3>
                <a href="{{ route('messages.index') }}" wire:navigate
                    class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">View All →</a>
            </div>
            <div class="space-y-3">
                @forelse($this->recentMessages as $msg)
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-700/30">
                        <div
                            class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center text-white font-semibold text-xs shadow-lg flex-shrink-0">
                            {{ strtoupper(substr($msg->to, -4, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-800 dark:text-white truncate">{{ $msg->to }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 truncate">
                                {{ Str::limit($msg->message, 40) }}</p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            @if($msg->status === 'sent')
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Sent</span>
                            @elseif($msg->status === 'failed')
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400">Failed</span>
                            @else
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Pending</span>
                            @endif
                            <p class="text-xs text-slate-400 mt-1">{{ $msg->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <svg class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600 mb-2" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                            </path>
                        </svg>
                        <p class="text-sm text-slate-500">No messages yet</p>
                        <a href="{{ route('messages.index') }}" wire:navigate
                            class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">Send your first message
                            →</a>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Device Status -->
        <div class="glass-card rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Device Status</h3>
                <a href="{{ route('devices.index') }}" wire:navigate
                    class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">Manage →</a>
            </div>
            <div class="space-y-3">
                @forelse($this->devices as $device)
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-700/30">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center shadow-lg flex-shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-slate-800 dark:text-white">{{ $device->name }}</p>
                            <p class="text-xs text-slate-500">{{ $device->phone_number ?: 'Not connected' }}</p>
                        </div>
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
                                Offline
                            </span>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-8">
                        <svg class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600 mb-2" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        <p class="text-sm text-slate-500">No devices yet</p>
                        <a href="{{ route('devices.index') }}" wire:navigate
                            class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">Add your first device →</a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="glass-card rounded-2xl p-6">
        <h3 class="text-lg font-semibold text-slate-800 dark:text-white mb-4">Quick Actions</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <a href="{{ route('devices.index') }}" wire:navigate
                class="flex flex-col items-center gap-2 p-4 rounded-xl bg-slate-50 dark:bg-slate-700/30 hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-colors group">
                <div
                    class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
                <span class="text-sm font-medium text-slate-700 dark:text-slate-200">Add Device</span>
            </a>
            <a href="{{ route('contacts.index') }}" wire:navigate
                class="flex flex-col items-center gap-2 p-4 rounded-xl bg-slate-50 dark:bg-slate-700/30 hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-colors group">
                <div
                    class="w-12 h-12 rounded-xl bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z">
                        </path>
                    </svg>
                </div>
                <span class="text-sm font-medium text-slate-700 dark:text-slate-200">Add Contact</span>
            </a>
            <a href="{{ route('messages.index') }}" wire:navigate
                class="flex flex-col items-center gap-2 p-4 rounded-xl bg-slate-50 dark:bg-slate-700/30 hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-colors group">
                <div
                    class="w-12 h-12 rounded-xl bg-sky-100 dark:bg-sky-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                </div>
                <span class="text-sm font-medium text-slate-700 dark:text-slate-200">Send Message</span>
            </a>
            <a href="{{ route('campaigns.index') }}" wire:navigate
                class="flex flex-col items-center gap-2 p-4 rounded-xl bg-slate-50 dark:bg-slate-700/30 hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-colors group">
                <div
                    class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z">
                        </path>
                    </svg>
                </div>
                <span class="text-sm font-medium text-slate-700 dark:text-slate-200">New Campaign</span>
            </a>
        </div>
    </div>
</div>