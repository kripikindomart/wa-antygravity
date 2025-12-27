<div class="space-y-6" wire:poll.5s>
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('campaigns.index') }}" wire:navigate
                    class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white">{{ $campaign->name }}</h1>
                <span
                    class="px-2 py-1 rounded text-xs font-bold uppercase {{ $campaign->status === 'running' ? 'bg-emerald-100 text-emerald-700' : ($campaign->status === 'completed' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-700') }}">
                    {{ $campaign->status }}
                </span>
            </div>
            <p class="text-sm text-slate-500">Monitor your broadcast progress in real-time.</p>
        </div>

        <div class="flex items-center gap-3">
            @if($campaign->status === 'running')
                <button wire:click="pause"
                    class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg font-bold shadow-lg shadow-amber-500/30 transition-all">
                    Scanning / Send
                </button>
                <button wire:click="pause"
                    class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg font-bold transition-all">
                    Pause
                </button>
            @elseif($campaign->status === 'paused')
                <button wire:click="resume"
                    class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg font-bold shadow-lg shadow-emerald-500/30 transition-all">
                    Resume Campaign
                </button>
            @endif

            @if($stats['failed'] > 0 && $campaign->status !== 'running')
                <button wire:click="retryFailed"
                    class="px-4 py-2 bg-rose-500 hover:bg-rose-600 text-white rounded-lg font-bold shadow-lg shadow-rose-500/30 transition-all">
                    Retry Failed
                </button>
            @endif
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700">
            <div class="text-slate-500 text-xs uppercase font-bold mb-1">Total Recipients</div>
            <div class="text-2xl font-bold text-slate-800 dark:text-white">{{ $stats['total'] }}</div>
        </div>
        <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700">
            <div class="text-emerald-500 text-xs uppercase font-bold mb-1">Sent Successfully</div>
            <div class="text-2xl font-bold text-emerald-600">{{ $stats['sent'] }}</div>
        </div>
        <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700">
            <div class="text-rose-500 text-xs uppercase font-bold mb-1">Failed</div>
            <div class="text-2xl font-bold text-rose-600">{{ $stats['failed'] }}</div>
        </div>
        <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700">
            <div class="text-amber-500 text-xs uppercase font-bold mb-1">Processing</div>
            <div class="text-2xl font-bold text-amber-600">{{ $stats['processing'] }}</div>
        </div>
        <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700">
            <div class="text-slate-400 text-xs uppercase font-bold mb-1">Pending</div>
            <div class="text-2xl font-bold text-slate-500">{{ $stats['pending'] }}</div>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="bg-slate-200 dark:bg-slate-700 rounded-full h-4 overflow-hidden">
        @php
            $percent = $stats['total'] > 0 ? (($stats['sent'] + $stats['failed']) / $stats['total']) * 100 : 0;
        @endphp
        <div class="bg-emerald-500 h-full transition-all duration-1000 ease-out relative"
            style="width: {{ $percent }}%">
            @if($campaign->status === 'running')
                <div class="absolute inset-0 bg-white/30 animate-[shimmer_2s_infinite]"></div>
            @endif
        </div>
    </div>

    <!-- Recipient List -->
    <div
        class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
        <div
            class="p-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-800/50">
            <h3 class="font-bold text-slate-700 dark:text-slate-300">Recipient Log</h3>
            <select wire:model.live="filterStatus" class="text-sm border-slate-200 rounded-lg py-1 px-3">
                <option value="all">All Status</option>
                <option value="sent">Sent</option>
                <option value="failed">Failed</option>
                <option value="pending">Pending</option>
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-500">
                    <tr>
                        <th class="p-4 font-medium">Name</th>
                        <th class="p-4 font-medium">Phone</th>
                        <th class="p-4 font-medium">Status</th>
                        <th class="p-4 font-medium">Sent At</th>
                        <th class="p-4 font-medium">Attempt</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($recipients as $recipient)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="p-4 font-medium text-slate-800 dark:text-white">{{ $recipient->name ?: '-' }}</td>
                            <td class="p-4 text-slate-500 font-mono">{{ $recipient->phone_number }}</td>
                            <td class="p-4">
                                @if($recipient->status === 'sent')
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                        Sent
                                    </span>
                                @elseif($recipient->status === 'failed')
                                    <div class="group relative inline-block">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800 cursor-help">
                                            Failed
                                        </span>
                                        <!-- Tooltip -->
                                        @if($recipient->error_message)
                                            <div
                                                class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-48 p-2 bg-slate-800 text-white text-xs rounded shadow-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10">
                                                {{ Str::limit($recipient->error_message, 50) }}
                                            </div>
                                        @endif
                                    </div>
                                @elseif($recipient->status === 'processing')
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 animate-pulse">
                                        Sending...
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                        Pending
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-slate-400">
                                {{ $recipient->sent_at ? $recipient->sent_at->format('H:i:s') : '-' }}
                            </td>
                            <td class="p-4">
                                @if($recipient->custom_data)
                                    <span title="{{ json_encode($recipient->custom_data) }}"
                                        class="cursor-pointer text-blue-500">
                                        Has Variables
                                    </span>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-500">No recipients found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100 dark:border-slate-700">
            {{ $recipients->links() }}
        </div>
    </div>
</div>