<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }"
    x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))" :class="{ 'dark': darkMode }">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} - {{ config('app.name', 'WA Gateway') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="font-sans antialiased bg-slate-50 dark:bg-slate-900 min-h-screen transition-colors duration-300">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside x-data="{ open: true }" :class="open ? 'w-64' : 'w-20'"
            class="hidden lg:flex flex-col bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl border-r border-slate-200/50 dark:border-slate-700/50 transition-all duration-300 ease-in-out">

            <!-- Logo -->
            <div
                class="flex items-center justify-between h-16 px-4 border-b border-slate-200/50 dark:border-slate-700/50">
                <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center shadow-lg shadow-emerald-500/30">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                            </path>
                        </svg>
                    </div>
                    <span x-show="open" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                        class="text-lg font-bold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">WA
                        Gateway</span>
                </a>
                <button @click="open = !open"
                    class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                    <svg class="w-5 h-5 text-slate-500" :class="{ 'rotate-180': !open }" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                    </svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <x-tenant.nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')"
                    icon="home">
                    <span x-show="open">Dashboard</span>
                </x-tenant.nav-link>
                <x-tenant.nav-link href="{{ route('devices.index') }}" :active="request()->routeIs('devices.*')"
                    icon="device-mobile">
                    <span x-show="open">Devices</span>
                </x-tenant.nav-link>
                <x-tenant.nav-link href="{{ route('contacts.index') }}" :active="request()->routeIs('contacts.*')"
                    icon="users">
                    <span x-show="open">Contacts</span>
                </x-tenant.nav-link>
                <x-tenant.nav-link href="{{ route('messages.index') }}" :active="request()->routeIs('messages.*')"
                    icon="chat-alt-2">
                    <span x-show="open">Messages</span>
                </x-tenant.nav-link>
                <x-tenant.nav-link href="{{ route('campaigns.index') }}" :active="request()->routeIs('campaigns.*')"
                    icon="speakerphone">
                    <span x-show="open">Campaigns</span>
                </x-tenant.nav-link>
                <x-tenant.nav-link href="{{ route('auto-replies.index') }}"
                    :active="request()->routeIs('auto-replies.*')" icon="reply">
                    <span x-show="open">Auto Reply</span>
                </x-tenant.nav-link>
                <x-tenant.nav-link href="{{ route('leads.index') }}" :active="request()->routeIs('leads.*')"
                    icon="user-group">
                    <span x-show="open">Daily Leads</span>
                </x-tenant.nav-link>

                <div class="pt-4 mt-4 border-t border-slate-200/50 dark:border-slate-700/50">
                    <x-tenant.nav-link href="{{ route('api-tokens.index') }}"
                        :active="request()->routeIs('api-tokens.*')" icon="key">
                        <span x-show="open">API Tokens</span>
                    </x-tenant.nav-link>
                    <x-tenant.nav-link href="{{ route('settings') }}" :active="request()->routeIs('settings')"
                        icon="cog">
                        <span x-show="open">Settings</span>
                    </x-tenant.nav-link>
                </div>
            </nav>

            <!-- User Menu -->
            <div class="p-3 border-t border-slate-200/50 dark:border-slate-700/50">
                <div
                    class="flex items-center gap-3 p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-colors cursor-pointer">
                    <div
                        class="w-10 h-10 rounded-full bg-gradient-to-br from-violet-400 to-purple-500 flex items-center justify-center text-white font-semibold shadow-lg shadow-violet-500/30">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <div x-show="open" class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-800 dark:text-white truncate">
                            {{ auth()->user()->name ?? 'User' }}
                        </p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ auth()->user()->email ??
                            'user@example.com' }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation -->
            <header
                class="h-16 bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl border-b border-slate-200/50 dark:border-slate-700/50 flex items-center justify-between px-4 lg:px-6">
                <!-- Mobile Menu Button -->
                <button class="lg:hidden p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                    <svg class="w-6 h-6 text-slate-600 dark:text-slate-300" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <!-- Page Title -->
                <div class="flex-1 lg:flex-none">
                    <h1 class="text-xl font-semibold text-slate-800 dark:text-white">{{ $title ?? 'Dashboard' }}</h1>
                </div>

                <!-- Right Side Actions -->
                <div class="flex items-center gap-3">
                    <!-- Dark Mode Toggle -->
                    <button @click="darkMode = !darkMode"
                        class="p-2.5 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 transition-all duration-200 group">
                        <svg x-show="!darkMode" class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                                clip-rule="evenodd"></path>
                        </svg>
                        <svg x-show="darkMode" x-cloak class="w-5 h-5 text-slate-300" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                    </button>

                    <!-- Notifications -->
                    <button
                        class="relative p-2.5 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 transition-all duration-200">
                        <svg class="w-5 h-5 text-slate-600 dark:text-slate-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                            </path>
                        </svg>
                        <span
                            class="absolute top-1.5 right-1.5 w-2 h-2 bg-rose-500 rounded-full ring-2 ring-white dark:ring-slate-700"></span>
                    </button>

                    <!-- Logout -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="p-2.5 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-rose-100 dark:hover:bg-rose-900/30 hover:text-rose-600 transition-all duration-200">
                            <svg class="w-5 h-5 text-slate-600 dark:text-slate-300" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                </path>
                            </svg>
                        </button>
                    </form>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-4 lg:p-6">
                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- Toast Notifications -->
    <div x-data="{ 
            notifications: [],
            add(notification) {
                // Handle Livewire 3 array payload wrapper
                if (Array.isArray(notification)) {
                    notification = notification[0];
                }
                notification.id = Date.now();
                this.notifications.push(notification);
                setTimeout(() => this.remove(notification.id), 5000);
            },
            remove(id) {
                this.notifications = this.notifications.filter(n => n.id !== id);
            }
        }" @notify.window="add($event.detail)" class="fixed bottom-4 right-4 z-50 space-y-2">
        <template x-for="notification in notifications" :key="notification.id">
            <div x-show="true" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-8"
                class="flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg backdrop-blur-xl" :class="{
                    'bg-emerald-500/90 text-white': notification.type === 'success',
                    'bg-rose-500/90 text-white': notification.type === 'error',
                    'bg-amber-500/90 text-white': notification.type === 'warning',
                    'bg-sky-500/90 text-white': notification.type === 'info'
                }">
                <span x-text="notification.message"></span>
                <button @click="remove(notification.id)" class="ml-2 opacity-70 hover:opacity-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
        </template>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('show-delete-confirmation', ({
                action,
                title,
                text
            }) => {
                Swal.fire({
                    title: title || 'Are you sure?',
                    text: text || "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#ef4444',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Livewire.dispatch(action);
                    }
                });
            });
        });
    </script>
    @livewireScripts
</body>

</html>