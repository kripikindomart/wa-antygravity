<div class="space-y-6 animate-fade-in-up">
    <!-- Header -->
    <div>
        <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Settings</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400">Manage your account settings</p>
    </div>

    <!-- Profile Settings -->
    <div class="glass-card rounded-2xl p-6">
        <h3 class="text-lg font-semibold text-slate-800 dark:text-white mb-4">Profile Information</h3>

        <form wire:submit="updateProfile">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Name</label>
                    <input type="text" wire:model="name"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white">
                    @error('name') <p class="mt-1 text-sm text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Email</label>
                    <input type="email" wire:model="email"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white">
                    @error('email') <p class="mt-1 text-sm text-rose-500">{{ $message }}</p> @enderror
                </div>
            </div>
            <button type="submit"
                class="px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-medium rounded-xl shadow-lg shadow-emerald-500/30 transition-all">
                Save Changes
            </button>
        </form>
    </div>

    <!-- Password Settings -->
    <div class="glass-card rounded-2xl p-6">
        <h3 class="text-lg font-semibold text-slate-800 dark:text-white mb-4">Update Password</h3>

        <form wire:submit="updatePassword">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Current
                        Password</label>
                    <input type="password" wire:model="current_password"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white">
                    @error('current_password') <p class="mt-1 text-sm text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">New
                        Password</label>
                    <input type="password" wire:model="new_password"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white">
                    @error('new_password') <p class="mt-1 text-sm text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Confirm
                        Password</label>
                    <input type="password" wire:model="new_password_confirmation"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white">
                </div>
            </div>
            <button type="submit"
                class="px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-medium rounded-xl shadow-lg shadow-emerald-500/30 transition-all">
                Update Password
            </button>
        </form>
    </div>

    <!-- Account Info -->
    <div class="glass-card rounded-2xl p-6">
        <h3 class="text-lg font-semibold text-slate-800 dark:text-white mb-4">Account Information</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <p class="text-sm text-slate-500 dark:text-slate-400">Account Created</p>
                <p class="font-medium text-slate-800 dark:text-white">{{ Auth::user()->created_at->format('M d, Y') }}
                </p>
            </div>
            <div>
                <p class="text-sm text-slate-500 dark:text-slate-400">Email Verified</p>
                <p class="font-medium text-slate-800 dark:text-white">
                    @if(Auth::user()->email_verified_at)
                        <span class="text-emerald-600">Verified</span>
                    @else
                        <span class="text-amber-600">Not Verified</span>
                    @endif
                </p>
            </div>
            <div>
                <p class="text-sm text-slate-500 dark:text-slate-400">Total Devices</p>
                <p class="font-medium text-slate-800 dark:text-white">{{ Auth::user()->devices()->count() }}</p>
            </div>
        </div>
    </div>

    <!-- Danger Zone -->
    <div class="glass-card rounded-2xl p-6 border border-rose-200 dark:border-rose-800">
        <h3 class="text-lg font-semibold text-rose-600 dark:text-rose-400 mb-4">Danger Zone</h3>
        <p class="text-sm text-slate-600 dark:text-slate-300 mb-4">Once you delete your account, all of your data will
            be permanently removed. This action cannot be undone.</p>
        <button
            class="px-4 py-2.5 text-rose-600 bg-rose-50 dark:bg-rose-900/20 hover:bg-rose-100 dark:hover:bg-rose-900/30 font-medium rounded-xl transition-colors">
            Delete Account
        </button>
    </div>
</div>