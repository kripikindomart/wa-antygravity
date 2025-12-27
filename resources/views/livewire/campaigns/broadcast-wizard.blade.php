<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Wizard Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">Create New Broadcast</h1>
        <p class="text-slate-500 dark:text-slate-400">Follow the steps to setup your campaign.</p>

        <!-- Progress Steps -->
        <div class="mt-8 relative">
            <div
                class="absolute top-1/2 left-0 w-full h-1 bg-slate-200 dark:bg-slate-700 -translate-y-1/2 rounded-full">
            </div>
            <div class="absolute top-1/2 left-0 h-1 bg-emerald-500 -translate-y-1/2 rounded-full transition-all duration-500"
                style="width: {{ ($step - 1) / ($totalSteps - 1) * 100 }}%"></div>

            <div class="relative flex justify-between">
                @foreach(['Campaign Details', 'Audience & Data', 'Message Content', 'Review & Schedule'] as $index => $label)
                    @php $stepNum = $index + 1; @endphp
                    <div class="flex flex-col items-center cursor-pointer"
                        wire:click="$set('step', {{ $stepNum > $step ? $step : $stepNum }})">
                        <div @class([
                            'w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm border-2 transition-all duration-300 z-10 bg-white dark:bg-slate-800',
                            'border-emerald-500 text-emerald-500 shadow-lg shadow-emerald-500/20' => $step >= $stepNum,
                            'border-slate-300 text-slate-400 dark:border-slate-600' => $step < $stepNum,
                            'bg-emerald-500 text-white !border-emerald-500 scale-110' => $step === $stepNum
                        ])>
                            {{ $stepNum }}
                        </div>
                        <span @class([
                            'mt-2 text-xs font-semibold uppercase tracking-wider',
                            'text-emerald-600 dark:text-emerald-400' => $step >= $stepNum,
                            'text-slate-400' => $step < $stepNum
                        ])>{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Wizard Content -->
    <div
        class="bg-white dark:bg-slate-800 rounded-3xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden min-h-[500px] flex flex-col">

        <div class="p-8 flex-1">
            <!-- Step 1: Setup -->
            @if($step === 1)
                <div class="space-y-6 max-w-2xl mx-auto animate-fade-in-up">
                    <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Campaign Details</h2>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Campaign Name</label>
                        <input type="text" wire:model.live="name"
                            class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-700 focus:border-emerald-500 focus:ring-emerald-500/20 transition-all font-semibold"
                            placeholder="e.g. Promo Merdeka 2024" />
                        @error('name') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Sender Device</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @forelse($devices as $device)
                                <label class="cursor-pointer relative">
                                    <input type="radio" wire:model.live="device_id" value="{{ $device->id }}"
                                        class="peer sr-only">
                                    <div
                                        class="p-4 rounded-xl border-2 border-slate-200 dark:border-slate-700 hover:border-emerald-500/50 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 dark:peer-checked:bg-emerald-900/10 transition-all">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-xl">
                                                📱</div>
                                            <div>
                                                <div class="font-bold text-slate-800 dark:text-white">{{ $device->name }}</div>
                                                <div class="text-xs text-slate-500">{{ $device->phone_number }}</div>
                                            </div>
                                            <div class="ml-auto opacity-0 peer-checked:opacity-100 text-emerald-500">
                                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            @empty
                                <div class="col-span-2 text-center p-6 border-2 border-dashed border-slate-300 rounded-xl">
                                    <p class="text-slate-500">No connected devices found.</p>
                                </div>
                            @endforelse
                        </div>
                        @error('device_id') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
            @endif

            <!-- Step 2: Audience -->
            @if($step === 2)
                <div class="space-y-8 animate-fade-in-up">
                    <div class="flex flex-col md:flex-row gap-8">
                        <!-- Source Type Selection -->
                        <div class="w-full md:w-1/3 space-y-4">
                            <h2 class="text-xl font-bold text-slate-800 dark:text-white">Select Audience Source</h2>

                            <label class="cursor-pointer block">
                                <input type="radio" wire:model.live="audience_type" value="group" class="peer sr-only">
                                <div
                                    class="p-4 rounded-xl border-2 border-slate-200 dark:border-slate-700 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 dark:peer-checked:bg-emerald-900/10 transition-all">
                                    <div class="font-bold text-lg mb-1">Contact Groups</div>
                                    <p class="text-sm text-slate-500">Pick from existing groups in your account.</p>
                                </div>
                            </label>

                            <label class="cursor-pointer block">
                                <input type="radio" wire:model.live="audience_type" value="import" class="peer sr-only">
                                <div
                                    class="p-4 rounded-xl border-2 border-slate-200 dark:border-slate-700 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 dark:peer-checked:bg-emerald-900/10 transition-all">
                                    <div class="font-bold text-lg mb-1">Import File</div>
                                    <p class="text-sm text-slate-500">Upload CSV/Excel file with custom data.</p>
                                </div>
                            </label>
                        </div>

                        <!-- Source Content -->
                        <div
                            class="w-full md:w-2/3 bg-slate-50 dark:bg-slate-900/50 rounded-2xl p-6 border border-slate-200 dark:border-slate-800">

                            <!-- Groups Selection -->
                            @if($audience_type === 'group')
                                <h3 class="font-bold mb-4">Select Target Groups</h3>
                                <div class="grid grid-cols-2 gap-4 max-h-[400px] overflow-y-auto pr-2">
                                    @foreach($groups as $group)
                                        <label
                                            class="flex items-center gap-3 p-3 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 cursor-pointer hover:border-emerald-500 transition-colors">
                                            <input type="checkbox" wire:model="selected_groups" value="{{ $group->id }}"
                                                class="rounded text-emerald-500 focus:ring-emerald-500 border-slate-300">
                                            <div>
                                                <div class="font-bold text-sm">{{ $group->name }}</div>
                                                <div class="text-xs text-slate-500">{{ $group->contacts_count }} contacts</div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Import & Mapping -->
                            @if($audience_type === 'import')
                                <div class="space-y-6">
                                    <!-- File Drop -->
                                    <div class="flex justify-between items-center mb-4">
                                        <div
                                            class="border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-xl p-8 text-center hover:bg-slate-100 dark:hover:bg-slate-800/50 transition-colors relative flex-1 mr-4">
                                            <input type="file" wire:model="csv_file"
                                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                                accept=".csv,.txt,.xlsx,.xls">

                                            <!-- Loading Overlay -->
                                            <div wire:loading wire:target="csv_file"
                                                class="absolute inset-0 bg-white/80 dark:bg-slate-900/80 rounded-xl flex items-center justify-center z-10">
                                                <div class="flex flex-col items-center gap-2">
                                                    <svg class="animate-spin h-8 w-8 text-emerald-500"
                                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                            stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor"
                                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                        </path>
                                                    </svg>
                                                    <span
                                                        class="text-sm font-medium text-slate-600 dark:text-slate-300">Uploading...</span>
                                                </div>
                                            </div>

                                            @if($csv_file)
                                                <div class="text-emerald-500 font-bold mb-2">File Selected:
                                                    {{ $csv_file->getClientOriginalName() }}
                                                </div>
                                                <div class="text-sm text-slate-500">Click to change file</div>
                                            @else
                                                <div class="text-4xl mb-4">📂</div>
                                                <div class="font-bold text-slate-700 dark:text-slate-300">Drop your Excel/CSV file
                                                    here
                                                </div>
                                                <div class="text-sm text-slate-500 mt-2">Supports .xlsx, .xls, .csv</div>
                                            @endif
                                        </div>
                                        <div>
                                            <button type="button" wire:click="downloadTemplate"
                                                class="flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-lg text-sm font-medium transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4">
                                                    </path>
                                                </svg>
                                                Download Template
                                            </button>
                                        </div>
                                    </div>
                                    @error('csv_file') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror

                                    <!-- Mapping Table -->
                                    @if(!empty($csv_headers))
                                        <div class="mt-8 animate-fade-in">
                                            <!-- ... -->
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Step 3: Compose -->
            @if($step === 3)
                <div class="flex flex-col lg:flex-row gap-8 animate-fade-in-up h-full">
                    <!-- Editor -->
                    <div class="w-full lg:w-1/2 space-y-4 flex flex-col" x-data="{
                                    insert(text) {
                                        let el = $refs.editor;
                                        if (!el) return;
                                        let start = el.selectionStart;
                                        let end = el.selectionEnd;
                                        let val = el.value;
                                        let before = val.substring(0, start);
                                        let after = val.substring(end);
                                        el.value = before + text + after;
                                        el.selectionStart = el.selectionEnd = start + text.length;
                                        el.focus();
                                        $wire.set('message', el.value);
                                    },
                                    wrap(token) {
                                        let el = $refs.editor;
                                        if (!el) return;
                                        let start = el.selectionStart;
                                        let end = el.selectionEnd;
                                        let val = el.value;
                                        let selected = val.substring(start, end);
                                        if (!selected) return this.insert(token);

                                        let before = val.substring(0, start);
                                        let after = val.substring(end);
                                        el.value = before + token + selected + token + after;
                                        el.selectionStart = end + (token.length * 2); 
                                        el.selectionEnd = el.selectionStart;
                                        el.focus();
                                        $wire.set('message', el.value);
                                    }
                                }">

                        <!-- Rich Editor -->
                        <div
                            class="flex-1 relative group flex flex-col bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden focus-within:ring-2 focus-within:ring-emerald-500/20 focus-within:border-emerald-500 transition-all">
                            <!-- Toolbar -->
                            <div
                                class="bg-slate-50 dark:bg-slate-800 p-2 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between gap-2">
                                <div class="flex items-center gap-1">
                                    <button type="button" @click="wrap('*')"
                                        class="p-1.5 rounded hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors"
                                        title="Bold">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M6 4h8a4 4 0 014 4 4 4 0 01-4 4H6V4zm0 8h9a4 4 0 014 4 4 4 0 01-4 4H6v-8z">
                                            </path>
                                        </svg>
                                    </button>
                                    <button type="button" @click="wrap('_')"
                                        class="p-1.5 rounded hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors"
                                        title="Italic">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                                        </svg>
                                    </button>
                                    <button type="button" @click="wrap('~')"
                                        class="p-1.5 rounded hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors"
                                        title="Strikethrough">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                    </button>

                                    <div class="w-px h-4 bg-slate-300 dark:bg-slate-600 mx-1"></div>

                                    <!-- Currency Formatter -->
                                    <div x-data="{ open: false }" class="relative">
                                        <button @click="open = !open" @click.outside="open = false" type="button"
                                            class="p-1.5 rounded hover:bg-emerald-100 dark:hover:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 transition-colors"
                                            title="Format Currency">
                                            <span class="text-xs font-bold">Rp</span>
                                        </button>
                                        <div x-show="open"
                                            class="absolute left-0 top-full mt-1 w-64 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 z-50 p-3"
                                            style="display: none;">
                                            <p class="text-xs text-slate-500 mb-2">Format angka atau variable menjadi Rupiah
                                            </p>
                                            <div class="space-y-2">
                                                <button type="button" @click="
                                                        let el = $refs.editor;
                                                        let start = el.selectionStart;
                                                        let end = el.selectionEnd;
                                                        let selected = el.value.substring(start, end);
                                                        if (selected && !isNaN(selected)) {
                                                            let formatted = 'Rp. ' + parseInt(selected).toLocaleString('id-ID') + ',-';
                                                            el.value = el.value.substring(0, start) + formatted + el.value.substring(end);
                                                            el.focus();
                                                            $wire.set('message', el.value);
                                                        }
                                                        open = false;
                                                    "
                                                    class="w-full text-left px-3 py-2 text-sm bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/20 dark:hover:bg-emerald-900/40 rounded-lg transition-colors">
                                                    <span class="font-medium">Format Angka</span>
                                                    <span class="text-xs text-slate-500 block">25000 → Rp. 25.000,-</span>
                                                </button>
                                                <button type="button" @click="
                                                        let el = $refs.editor;
                                                        let start = el.selectionStart;
                                                        let end = el.selectionEnd;
                                                        let selected = el.value.substring(start, end);
                                                        if (selected) {
                                                            let formatted = 'Rp. ' + selected + ',-';
                                                            el.value = el.value.substring(0, start) + formatted + el.value.substring(end);
                                                            el.focus();
                                                            $wire.set('message', el.value);
                                                        }
                                                        open = false;
                                                    "
                                                    class="w-full text-left px-3 py-2 text-sm bg-purple-50 hover:bg-purple-100 dark:bg-purple-900/20 dark:hover:bg-purple-900/40 rounded-lg transition-colors">
                                                    <span class="font-medium">Wrap Variable</span>
                                                    <span class="text-xs text-slate-500 block">[Tagihan] → Rp.
                                                        [Tagihan],-</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Emoji Picker -->
                                    <div x-data="{ open: false }" class="relative">
                                        <button @click="open = !open" @click.outside="open = false" type="button"
                                            class="p-1.5 rounded hover:bg-amber-100 dark:hover:bg-amber-900/30 text-amber-600 dark:text-amber-400 transition-colors"
                                            title="Insert Emoji">
                                            <span class="text-sm">😊</span>
                                        </button>
                                        <div x-show="open" x-transition
                                            class="absolute left-0 top-full mt-1 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 z-[100] p-2"
                                            style="display: none; width: 240px;">
                                            <div style="display: flex; flex-wrap: wrap; gap: 2px;">
                                                @foreach(['😀', '😊', '😍', '🥰', '😎', '🤩', '😂', '🤣', '👍', '👏', '🙏', '💪', '🔥', '✨', '⭐', '💯', '❤️', '💕', '🎉', '🎊', '✅', '⚡', '🚀', '💰', '🛒', '📦', '💳', '📱', '📞', '💬', '📧', '🎁'] as $emoji)
                                                    <button type="button" @click="insert('{{ $emoji }}'); open = false"
                                                        style="width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; font-size: 16px; border-radius: 4px;"
                                                        class="hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">{{ $emoji }}</button>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Quick Snippets -->
                                    <div x-data="{ open: false }" class="relative">
                                        <button @click="open = !open" @click.outside="open = false" type="button"
                                            class="p-1.5 rounded hover:bg-sky-100 dark:hover:bg-sky-900/30 text-sky-600 dark:text-sky-400 transition-colors"
                                            title="Quick Snippets">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 6h16M4 12h16m-7 6h7"></path>
                                            </svg>
                                        </button>
                                        <div x-show="open"
                                            class="absolute left-0 top-full mt-1 w-64 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 z-50 overflow-hidden"
                                            style="display: none;">
                                            <div
                                                class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-700">
                                                Greetings</div>
                                            <button type="button" @click="insert('Halo [name], '); open = false"
                                                class="w-full text-left px-4 py-2 text-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Halo
                                                [name],</button>
                                            <button type="button" @click="insert('Selamat pagi, '); open = false"
                                                class="w-full text-left px-4 py-2 text-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Selamat
                                                pagi,</button>
                                            <button type="button"
                                                @click="insert('Terima kasih telah menghubungi kami. '); open = false"
                                                class="w-full text-left px-4 py-2 text-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Terima
                                                kasih...</button>
                                            <div
                                                class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50 dark:bg-slate-900/50 border-b border-t border-slate-100 dark:border-slate-700">
                                                CTA</div>
                                            <button type="button"
                                                @click="insert('Balas pesan ini untuk info lebih lanjut. '); open = false"
                                                class="w-full text-left px-4 py-2 text-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Balas
                                                untuk info...</button>
                                            <button type="button" @click="insert('Klik link berikut: '); open = false"
                                                class="w-full text-left px-4 py-2 text-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Klik
                                                link...</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <!-- Variable Dropdown -->
                                    <div x-data="{ open: false }" class="relative">
                                        <button @click="open = !open" @click.outside="open = false" type="button"
                                            class="flex items-center gap-1 px-3 py-1.5 text-xs font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors">
                                            <span>[ ] Variables</span>
                                            <svg class="w-3 h-3 transition-transform" :class="{'rotate-180': open}"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>
                                        <div x-show="open"
                                            class="absolute right-0 top-full mt-1 w-48 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 z-50 overflow-hidden"
                                            style="display: none;">
                                            <div
                                                class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-700">
                                                Standard</div>
                                            <button type="button" @click="insert(' [name] '); open = false"
                                                class="w-full text-left px-4 py-2 text-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors flex items-center justify-between group">
                                                <span class="font-mono text-slate-600 dark:text-slate-300">[name]</span>
                                                <span class="text-[10px] text-slate-400">FullName</span>
                                            </button>
                                            <button type="button" @click="insert(' [phone] '); open = false"
                                                class="w-full text-left px-4 py-2 text-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors flex items-center justify-between group">
                                                <span class="font-mono text-slate-600 dark:text-slate-300">[phone]</span>
                                                <span class="text-[10px] text-slate-400">Number</span>
                                            </button>

                                            @if($audience_type === 'import' && !empty($column_mapping))
                                                <div
                                                    class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-700 border-t">
                                                    Custom</div>
                                                @foreach($column_mapping as $header => $var)
                                                    @if(str_starts_with($var, 'variable:'))
                                                        @php $vName = str_replace('variable:', '', $var); @endphp
                                                        <button type="button" @click="insert(' [{{ $vName }}] '); open = false"
                                                            class="w-full text-left px-4 py-2 text-sm hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-colors flex items-center justify-between group">
                                                            <span
                                                                class="font-mono text-purple-600 dark:text-purple-400">[{{ $vName }}]</span>
                                                            <span
                                                                class="text-[10px] text-slate-400">{{ \Illuminate\Support\Str::limit($header, 10) }}</span>
                                                        </button>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Template Selector -->
                                    <div class="relative">
                                        <select wire:model.live="selected_template_id"
                                            class="appearance-none pl-3 pr-8 py-1.5 text-xs font-semibold bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300 rounded-lg border-0 focus:ring-0 cursor-pointer">
                                            <option value="">Load Template...</option>
                                            @foreach($templates as $template)
                                                <option value="{{ $template->id }}">
                                                    {{ \Illuminate\Support\Str::limit($template->name, 15) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <textarea wire:model.live="message" x-ref="editor"
                                class="w-full flex-1 min-h-[350px] px-4 py-4 bg-transparent border-0 focus:ring-0 font-mono text-sm resize-none"
                                placeholder="Type your message here..."></textarea>

                            <!-- Attachment Section -->
                            <div
                                class="border-t border-slate-200 dark:border-slate-700 p-3 bg-slate-50 dark:bg-slate-800/50">
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-semibold text-slate-500 uppercase">Attachment:</span>

                                    @if($attachment)
                                        <div
                                            class="flex items-center gap-2 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg">
                                            @if($attachment_type === 'image')
                                                <span class="text-lg">🖼️</span>
                                            @else
                                                <span class="text-lg">📄</span>
                                            @endif
                                            <span
                                                class="text-sm text-emerald-700 dark:text-emerald-400 font-medium">{{ $attachment->getClientOriginalName() }}</span>
                                            <button type="button" wire:click="$set('attachment', null)"
                                                class="text-rose-500 hover:text-rose-600">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    @else
                                        <label
                                            class="cursor-pointer flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors">
                                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                            <span class="text-sm text-slate-600 dark:text-slate-300">Image</span>
                                            <input type="file" wire:model="attachment" accept="image/*" class="hidden"
                                                @change="$wire.set('attachment_type', 'image')">
                                        </label>

                                        <label
                                            class="cursor-pointer flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors">
                                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                </path>
                                            </svg>
                                            <span class="text-sm text-slate-600 dark:text-slate-300">Document</span>
                                            <input type="file" wire:model="attachment" accept=".pdf,.doc,.docx,.xls,.xlsx"
                                                class="hidden" @change="$wire.set('attachment_type', 'document')">
                                        </label>

                                        <div wire:loading wire:target="attachment"
                                            class="flex items-center gap-2 text-sm text-slate-500">
                                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                    stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                            </svg>
                                            Uploading...
                                        </div>
                                    @endif
                                </div>
                                <p class="text-[10px] text-slate-400 mt-2">Optional: Attach an image or document to send
                                    with every message.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div class="w-full lg:w-1/2 flex flex-col">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-slate-500 text-sm uppercase">Message Preview</h3>
                            <div class="flex items-center gap-2">
                                <button class="p-1 hover:bg-slate-200 dark:hover:bg-slate-700 rounded transition-colors"
                                    wire:click="prevPreview" title="Previous Recipient">
                                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 19l-7-7 7-7"></path>
                                    </svg>
                                </button>
                                <span
                                    class="text-xs font-mono font-bold bg-white dark:bg-slate-700 px-3 py-1 rounded-full border border-slate-200 dark:border-slate-600 shadow-sm">{{ $preview_index + 1 }}
                                    / {{ count($preview_recipients) > 0 ? count($preview_recipients) : '?' }}</span>
                                <button class="p-1 hover:bg-slate-200 dark:hover:bg-slate-700 rounded transition-colors"
                                    wire:click="nextPreview" title="Next Recipient">
                                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Device Mockup -->
                        <div class="flex-1 bg-[#efe7dd] dark:bg-[#0b141a] rounded-3xl overflow-hidden border-[8px] border-slate-800 dark:border-slate-900 relative shadow-2xl flex flex-col"
                            style="background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png'); background-repeat: repeat; background-size: 300px;">

                            <!-- Header -->
                            <div
                                class="bg-[#008069] dark:bg-[#202c33] px-4 py-3 flex items-center gap-3 text-white shadow-md z-10">
                                <div class="w-8 h-8 rounded-full bg-slate-300 overflow-hidden">
                                    <svg class="w-full h-full text-slate-400" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-bold text-sm truncate">
                                        {{ $preview_recipients[$preview_index]['name'] ?? 'Recipient Name' }}
                                    </div>
                                    <div class="text-[10px] opacity-80 truncate">online</div>
                                </div>
                                <svg class="w-5 h-5 opacity-70" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
                                </svg>
                            </div>

                            <!-- Chat Area -->
                            <div class="flex-1 p-4 overflow-y-auto flex flex-col">
                                <div class="rounded-lg p-2 shadow-sm max-w-[85%] self-end relative text-sm leading-relaxed mb-2"
                                    style="border-top-right-radius: 0; background-color: #d9fdd3 !important; color: #111b21 !important;">

                                    @php
                                        $sample = $preview_recipients[$preview_index] ?? ['name' => 'John Doe', 'phone' => '62812345678'];
                                        $rawMsg = $message;
                                        // Interpolate
                                        foreach ($sample as $key => $val) {
                                            $rawMsg = str_replace(['[' . $key . ']'], $val, $rawMsg);
                                        }
                                        // Markdown formatting
                                        $formatted = preg_replace('/\*(.*?)\*/', '<strong>$1</strong>', htmlspecialchars($rawMsg));
                                        $formatted = preg_replace('/_(.*?)_/', '<em>$1</em>', $formatted);
                                        $formatted = preg_replace('/~(.*?)~/', '<strike>$1</strike>', $formatted);
                                    @endphp

                                    <div class="whitespace-pre-wrap">{!! nl2br($formatted) !!}</div>

                                    <div class="text-[10px] text-slate-500 text-right mt-1 flex items-center justify-end gap-1"
                                        style="color: #667781 !important;">
                                        <span>{{ now()->format('H:i') }}</span>
                                        <svg style="width: 16px; height: 16px; color: #53bdeb;" class="w-4 h-4"
                                            fill="currentColor" viewBox="0 0 16 16">
                                            <path
                                                d="M12.354 4.354a.5.5 0 0 0-.708-.708L5 10.293 1.854 7.146a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0l7-7zm-4.208 7-.896-.897.707-.707.543.543 6.646-6.647a.5.5 0 0 1 .708.708l-7 7a.5.5 0 0 1-.708 0z" />
                                        </svg>
                                    </div>

                                    <!-- Tail -->
                                    <svg class="absolute -right-2 top-0 w-2 h-3" style="color: #d9fdd3 !important;"
                                        fill="currentColor" viewBox="0 0 8 13">
                                        <path d="M5.188 1H0v11.193l6.467-8.625C7.526 2.156 6.958 1 5.188 1z" />
                                    </svg>
                                </div>
                            </div>

                            <!-- Input Mockup -->
                            <div class="bg-[#f0f2f5] dark:bg-[#202c33] p-2 flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-700"></div>
                                <div class="flex-1 bg-white dark:bg-slate-600 h-9 rounded-lg"></div>
                                <div class="w-8 h-8 rounded-full bg-[#008069]"></div>
                            </div>
                        </div>
                        <div class="mt-2 text-center">
                            <span class="text-xs text-slate-400 font-mono">Simulated WhatsApp View</span>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Step 4: Schedule -->
            @if($step === 4)
                <div class="max-w-3xl mx-auto space-y-8 animate-fade-in-up">
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700">
                        <h2 class="text-xl font-bold mb-6">Schedule & Settings</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Delay -->
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Delay per Message
                                    (Seconds)</label>
                                <input type="number" wire:model="delay_seconds" min="5" max="300"
                                    class="w-full px-4 py-2 rounded-xl bg-slate-50 border-slate-200">
                                <p class="text-xs text-slate-500 mt-1">Recommended: 10-30s to avoid banning.</p>
                            </div>

                            <!-- Error Mode -->
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Error Handling</label>
                                <select wire:model="error_mode"
                                    class="w-full px-4 py-2 rounded-xl bg-slate-50 border-slate-200">
                                    <option value="continue">Ignore errors & Continue</option>
                                    <option value="stop">Stop immediately on error</option>
                                </select>
                            </div>

                            <!-- Schedule -->
                            <div class="col-span-2">
                                <label class="block text-sm font-bold text-slate-700 mb-2">Start Time (Optional)</label>
                                <div class="flex gap-4">
                                    <input type="date" wire:model="schedule_date"
                                        class="flex-1 px-4 py-2 rounded-xl bg-slate-50 border-slate-200">
                                    <input type="time" wire:model="schedule_time"
                                        class="flex-1 px-4 py-2 rounded-xl bg-slate-50 border-slate-200">
                                </div>
                                <p class="text-xs text-slate-500 mt-1">Leave blank to send immediately.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div
                        class="bg-emerald-50 dark:bg-emerald-900/10 p-6 rounded-2xl border border-emerald-100 dark:border-emerald-800">
                        <h3 class="font-bold text-emerald-800 dark:text-emerald-400 mb-4">Broadcast Summary</h3>
                        <dl class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <dt class="text-slate-500">Campaign Name</dt>
                                <dd class="font-bold">{{ $name }}</dd>
                            </div>
                            <div>
                                <dt class="text-slate-500">Audience</dt>
                                <dd class="font-bold">{{ ucfirst($audience_type) }}
                                    ({{ count($preview_recipients) > 0 ? '~' . count($preview_recipients) . ' recipients' : 'Unknown' }})
                                </dd>
                            </div>
                            <div>
                                <dt class="text-slate-500">Delay</dt>
                                <dd class="font-bold">{{ $delay_seconds }}s</dd>
                            </div>
                            <div>
                                <dt class="text-slate-500">Status</dt>
                                <dd class="font-bold badge badge-emerald">Ready to Launch</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            @endif
        </div>

        <!-- Wizard Footer (Navigation) -->
        <div
            class="bg-slate-50 dark:bg-slate-900/50 p-6 border-t border-slate-200 dark:border-slate-800 flex justify-between items-center">
            <button wire:click="prevStep"
                class="px-6 py-2 rounded-xl font-bold text-slate-600 hover:bg-slate-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                @if($step === 1) disabled @endif>
                Back
            </button>

            <button wire:click="{{ $step === $totalSteps ? 'send' : 'nextStep' }}"
                class="px-8 py-2 rounded-xl font-bold bg-emerald-500 text-white hover:bg-emerald-600 shadow-lg shadow-emerald-500/30 transition-all transform hover:scale-105">
                {{ $step === $totalSteps ? 'Finish & Send' : 'Next Step' }}
            </button>
        </div>
    </div>
</div>