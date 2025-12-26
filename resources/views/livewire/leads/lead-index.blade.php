<div class="space-y-6 animate-fade-in-up">
    <!-- Header & Controls -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Daily Leads</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Track and manage your daily incoming prospects</p>
        </div>
        <div class="flex items-center gap-3">
            <input type="date" wire:model.live="date"
                class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all">
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Total Leads -->
        <div
            class="glass-card bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-slate-800 dark:text-white">{{ $stats['total'] }}</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Total Leads Today</p>
                </div>
            </div>
        </div>

        <!-- New Leads -->
        <div
            class="glass-card bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z">
                        </path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-slate-800 dark:text-white">{{ $stats['new'] }}</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">New Leads</p>
                </div>
            </div>
        </div>

        <!-- Converted -->
        <div
            class="glass-card bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-xl bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center text-violet-600 dark:text-violet-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-slate-800 dark:text-white">{{ $stats['converted'] }}</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Converted</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Leads Table -->
    <div
        class="glass-card bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-6 py-4 font-semibold text-slate-700 dark:text-slate-200">Contact</th>
                        <th class="px-6 py-4 font-semibold text-slate-700 dark:text-slate-200">Source</th>
                        <th class="px-6 py-4 font-semibold text-slate-700 dark:text-slate-200">Last Message</th>
                        <th class="px-6 py-4 font-semibold text-slate-700 dark:text-slate-200">Activity</th>
                        <th class="px-6 py-4 font-semibold text-slate-700 dark:text-slate-200">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700/50">
                    @forelse($leads as $lead)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500 font-bold shrink-0">
                                        {{ strtoupper(substr($lead->name ?: $lead->number, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-slate-800 dark:text-white">{{ $lead->name ?: 'Unknown' }}
                                        </p>
                                        <p class="text-xs text-slate-500 font-mono">{{ $lead->number }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($lead->source === 'incoming')
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                        Incoming
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
                                        {{ Illuminate\Support\Str::title($lead->source) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 max-w-xs">
                                <p class="text-slate-600 dark:text-slate-300 truncate" title="{{ $lead->last_message }}">
                                    {{ $lead->last_message ?: '-' }}
                                </p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-slate-800 dark:text-white text-xs whitespace-nowrap">
                                    {{ $lead->last_activity_at->format('H:i') }}
                                </p>
                                <p class="text-slate-400 text-[10px]">{{ $lead->last_activity_at->diffForHumans() }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <select wire:change="updateStatus({{ $lead->id }}, $event.target.value)"
                                    class="pl-2 pr-8 py-1 rounded-lg border-0 bg-slate-100 dark:bg-slate-700 text-xs font-medium text-slate-700 dark:text-slate-300 focus:ring-2 focus:ring-emerald-500 cursor-pointer">
                                    <option value="new" @selected($lead->status === 'new')>New</option>
                                    <option value="follow_up" @selected($lead->status === 'follow_up')>Follow Up</option>
                                    <option value="converted" @selected($lead->status === 'converted')>Converted</option>
                                    <option value="lost" @selected($lead->status === 'lost')>Lost</option>
                                </select>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                No leads found for this date.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($leads->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700">
                {{ $leads->links() }}
            </div>
        @endif
    </div>
</div>