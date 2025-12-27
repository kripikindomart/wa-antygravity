<div class="min-h-screen">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Message Templates</h1>
                <p class="text-slate-500 dark:text-slate-400">Create reusable message templates with dynamic variables
                </p>
            </div>
            <button wire:click="openModal"
                class="px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-medium rounded-xl shadow-lg shadow-emerald-500/30 hover:shadow-xl transition-all flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Template
            </button>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="glass-card p-4 mb-6 flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search templates..."
                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white">
        </div>
        <select wire:model.live="filterCategory"
            class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white">
            <option value="">All Categories</option>
            @foreach($this->categories as $cat)
                <option value="{{ $cat }}">{{ $cat }}</option>
            @endforeach
        </select>
    </div>

    <!-- Templates Grid -->
    @if($templates->isEmpty())
        <div class="glass-card p-12 text-center">
            <div
                class="w-16 h-16 mx-auto mb-4 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-slate-700 dark:text-slate-300 mb-2">No templates yet</h3>
            <p class="text-slate-500 mb-4">Create your first message template to get started</p>
            <button wire:click="openModal" class="px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600">
                Create Template
            </button>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($templates as $template)
                <div class="glass-card p-5 hover:shadow-lg transition-shadow group">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="font-semibold text-slate-800 dark:text-white truncate">{{ $template->name }}</h3>
                                @if($template->is_favorite)
                                    <span class="text-amber-500">⭐</span>
                                @endif
                            </div>
                            @if($template->category)
                                <span
                                    class="inline-block mt-1 px-2 py-0.5 text-xs bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400 rounded-full">{{ $template->category }}</span>
                            @endif
                        </div>
                        <button wire:click="toggleFavorite({{ $template->id }})" class="text-slate-400 hover:text-amber-500 p-1"
                            title="Toggle favorite">
                            <svg class="w-5 h-5" fill="{{ $template->is_favorite ? 'currentColor' : 'none' }}"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                        </button>
                    </div>

                    <p class="text-sm text-slate-600 dark:text-slate-400 line-clamp-3 mb-4">
                        {{ Str::limit(strip_tags($template->content), 120) }}</p>

                    <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button wire:click="edit({{ $template->id }})"
                            class="flex-1 px-3 py-1.5 text-sm bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-lg hover:bg-emerald-200">
                            Edit
                        </button>
                        <button wire:click="duplicate({{ $template->id }})"
                            class="px-3 py-1.5 text-sm bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400 rounded-lg hover:bg-slate-200"
                            title="Duplicate">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </button>
                        <button wire:click="delete({{ $template->id }})" wire:confirm="Delete this template?"
                            class="px-3 py-1.5 text-sm bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-200"
                            title="Delete">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $templates->links() }}
        </div>
    @endif

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeModal"></div>
            <div
                class="relative w-full max-w-3xl bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 animate-fade-in-up max-h-[90vh] flex flex-col">

                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white">
                        {{ $isEditing ? 'Edit Template' : 'Create Template' }}
                    </h3>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 overflow-y-auto flex-1 space-y-5">
                    <!-- Name & Category -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Template
                                Name</label>
                            <input type="text" wire:model="name" placeholder="e.g. Welcome Message"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white">
                            @error('name') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Category
                                (Optional)</label>
                            <input type="text" wire:model="category" placeholder="e.g. Sales, Support, Promo"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white">
                        </div>
                    </div>

                    <!-- Variables Picker -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Insert
                            Variable</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($this->variables as $var)
                                <button type="button" onclick="insertVariable('{{ $var['key'] }}')"
                                    class="px-3 py-1.5 text-sm bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-emerald-100 hover:text-emerald-700 transition-colors flex items-center gap-1.5">
                                    <span>{{ $var['icon'] }}</span>
                                    <span>{{ $var['label'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- WYSIWYG Editor -->
                    <div x-data="{ content: @entangle('content') }" x-init="
                            const quill = new Quill($refs.editor, {
                                theme: 'snow',
                                placeholder: 'Write your message here... Use variables like [name] for personalization.',
                                modules: {
                                    toolbar: [
                                        ['bold', 'italic', 'underline', 'strike'],
                                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                        ['clean']
                                    ]
                                }
                            });
                            quill.root.innerHTML = content;
                            quill.on('text-change', () => {
                                content = quill.root.innerHTML;
                            });
                            window.insertVariable = (variable) => {
                                const range = quill.getSelection(true);
                                quill.insertText(range.index, variable);
                                quill.setSelection(range.index + variable.length);
                            };
                        ">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Message
                            Content</label>
                        <div x-ref="editor"
                            class="bg-white dark:bg-slate-700 rounded-xl border border-slate-300 dark:border-slate-600 min-h-[200px]">
                        </div>
                        @error('content') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <!-- Preview Toggle -->
                    <div class="pt-4 border-t border-slate-200 dark:border-slate-700">
                        <button type="button" wire:click="$toggle('showPreview')"
                            class="text-sm text-emerald-600 hover:text-emerald-700 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            {{ $showPreview ? 'Hide Preview' : 'Show Preview' }}
                        </button>

                        @if($showPreview)
                            <div class="mt-4 p-4 bg-slate-50 dark:bg-slate-900 rounded-xl">
                                <div class="flex gap-4 mb-3">
                                    <input type="text" wire:model.live="previewName" placeholder="Sample name"
                                        class="flex-1 px-3 py-1.5 text-sm rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700">
                                    <input type="text" wire:model.live="previewPhone" placeholder="Sample phone"
                                        class="flex-1 px-3 py-1.5 text-sm rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700">
                                </div>
                                <div
                                    class="p-3 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 text-sm whitespace-pre-wrap">
                                    {!! $this->getPreviewContent() !!}
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Favorite Toggle -->
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="is_favorite" class="w-4 h-4 text-emerald-500 rounded">
                        <span class="text-sm text-slate-700 dark:text-slate-300">⭐ Mark as favorite</span>
                    </label>
                </div>

                <!-- Modal Footer -->
                <div class="flex justify-end gap-3 p-6 border-t border-slate-200 dark:border-slate-700">
                    <button wire:click="closeModal"
                        class="px-4 py-2.5 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl">
                        Cancel
                    </button>
                    <button wire:click="save" wire:loading.attr="disabled"
                        class="px-6 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-medium rounded-xl shadow-lg shadow-emerald-500/30">
                        <span wire:loading.remove wire:target="save">{{ $isEditing ? 'Update' : 'Create' }} Template</span>
                        <span wire:loading wire:target="save">Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Quill.js CDN -->
    @push('styles')
        <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
        <style>
            .ql-container {
                font-size: 14px;
            }

            .ql-editor {
                min-height: 150px;
            }

            .dark .ql-toolbar {
                border-color: rgb(71 85 105);
                background: rgb(51 65 85);
            }

            .dark .ql-container {
                border-color: rgb(71 85 105);
            }

            .dark .ql-editor {
                color: white;
            }

            .dark .ql-stroke {
                stroke: rgb(148 163 184);
            }

            .dark .ql-fill {
                fill: rgb(148 163 184);
            }

            .dark .ql-picker-label {
                color: rgb(148 163 184);
            }
        </style>
    @endpush
    @push('scripts')
        <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
    @endpush
</div>