<div class="space-y-6 animate-fade-in-up">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white">API Tokens</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Manage your API access tokens for external
                integrations</p>
        </div>
        <button wire:click="openModal"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-medium rounded-xl shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/50 transition-all duration-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            New Token
        </button>
    </div>

    <!-- API Documentation Card -->
    <div class="glass-card rounded-2xl p-6">
        <h3 class="font-semibold text-slate-800 dark:text-white mb-3">API Documentation</h3>
        <p class="text-sm text-slate-600 dark:text-slate-300 mb-4">Use these endpoints to integrate WhatsApp messaging
            into your applications:</p>

        <div class="space-y-3">
            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-700/30 font-mono text-sm">
                <span class="text-emerald-600">POST</span> <span
                    class="text-slate-600 dark:text-slate-300">/api/send-message</span>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-700/30 font-mono text-sm">
                <span class="text-sky-600">GET</span> <span
                    class="text-slate-600 dark:text-slate-300">/api/devices</span>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-700/30 font-mono text-sm">
                <span class="text-sky-600">GET</span> <span
                    class="text-slate-600 dark:text-slate-300">/api/contacts</span>
            </div>
        </div>

        <p class="text-xs text-slate-500 mt-4">Include your token in the Authorization header: <code
                class="px-1.5 py-0.5 rounded bg-slate-200 dark:bg-slate-600">Bearer YOUR_TOKEN</code></p>
    </div>

    <!-- Tokens List -->
    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
            <h3 class="font-semibold text-slate-800 dark:text-white">Your Tokens</h3>
        </div>

        <div class="divide-y divide-slate-200 dark:divide-slate-700">
            @forelse($this->tokens as $token)
                <div
                    class="px-6 py-4 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                    <div>
                        <p class="font-medium text-slate-800 dark:text-white">{{ $token->name }}</p>
                        <p class="text-xs text-slate-500">Created {{ $token->created_at->diffForHumans() }} • Last used
                            {{ $token->last_used_at?->diffForHumans() ?? 'Never' }}</p>
                    </div>
                    <button wire:click="deleteToken({{ $token->id }})"
                        wire:confirm="Revoke this token? Any applications using it will lose access."
                        class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors text-slate-500 hover:text-rose-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                            </path>
                        </svg>
                    </button>
                </div>
            @empty
                <div class="px-6 py-12 text-center">
                    <svg class="w-12 h-12 mx-auto text-slate-300 dark:text-slate-600 mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z">
                        </path>
                    </svg>
                    <p class="text-slate-500 dark:text-slate-400 mb-2">No API tokens yet</p>
                    <button wire:click="openModal" class="text-emerald-600 hover:text-emerald-700 font-medium">Create your
                        first token →</button>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Create Token Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeModal"></div>
            <div class="relative w-full max-w-md bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-2xl border border-slate-200 dark:border-slate-700 animate-fade-in-up">
                @if($newToken)
                    <!-- Token Created View -->
                    <div class="text-center">
                        <div
                            class="w-16 h-16 mx-auto rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-2">Token Created!</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Make sure to copy your token now. You won't
                            be able to see it again.</p>

                        <div class="p-4 rounded-xl bg-slate-100 dark:bg-slate-700 mb-4">
                            <code class="text-sm text-slate-800 dark:text-slate-200 break-all">{{ $newToken }}</code>
                        </div>

                        <div class="flex gap-3">
                            <button type="button" onclick="navigator.clipboard.writeText('{{ $newToken }}')"
                                class="flex-1 px-4 py-2.5 text-emerald-700 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400 font-medium rounded-xl transition-colors">
                                Copy Token
                            </button>
                            <button wire:click="closeModal"
                                class="flex-1 px-4 py-2.5 text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 font-medium rounded-xl transition-colors">
                                Done
                            </button>
                        </div>
                    </div>
                @else
                    <!-- Create Token Form -->
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-4">Create API Token</h3>

                    <form wire:submit="createToken">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Token
                                Name</label>
                            <input type="text" wire:model="tokenName" placeholder="e.g. My App Integration"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white">
                            <p class="mt-1 text-xs text-slate-500">A name to help you remember what this token is for</p>
                            @error('tokenName') <p class="mt-1 text-sm text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex gap-3">
                            <button type="button" wire:click="closeModal"
                                class="flex-1 px-4 py-2.5 text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 font-medium rounded-xl transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                class="flex-1 px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-medium rounded-xl shadow-lg transition-all">
                                Create Token
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    @endif
</div>