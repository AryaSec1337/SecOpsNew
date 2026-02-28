@extends('layouts.dashboard')

@section('content')
    <div class="space-y-6" x-data="yarGen()">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">YARA Rule Generator</h1>
                <p class="text-slate-400 text-sm mt-1">Generate robust YARA rules from malware samples using yarGen.</p>
            </div>
            <div>
                <span x-show="loading" class="text-blue-400 text-sm animate-pulse flex items-center gap-2">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Processing Samples...
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Upload & Config -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Config -->
                <div class="bg-slate-800 rounded-xl border border-slate-700 p-6 shadow-xl relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                    </div>
                    
                    <h3 class="text-lg font-bold text-white mb-4 relative z-10">Configuration</h3>
                    
                    <div class="space-y-4 relative z-10">
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-1">Author Name</label>
                            <input type="text" x-model="config.author" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-sm" placeholder="e.g. SOC Analyst">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-1">Reference</label>
                            <input type="text" x-model="config.reference" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-sm" placeholder="e.g. Case #1234">
                        </div>
                    </div>
                </div>

                <!-- Upload Zone -->
                <div class="bg-slate-800 rounded-xl border border-slate-700 p-6 shadow-xl">
                    <h3 class="text-lg font-bold text-white mb-4">Malware Samples</h3>
                    
                    <div class="border-2 border-dashed border-slate-600 hover:border-blue-500 rounded-xl p-8 text-center transition-all cursor-pointer bg-slate-900/50"
                         @dragover.prevent="dragover = true"
                         @dragleave.prevent="dragover = false"
                         @drop.prevent="handleDrop($event)"
                         @click="$refs.fileInput.click()"
                         :class="{ 'border-blue-500 bg-blue-500/10': dragover }">
                        
                        <input type="file" x-ref="fileInput" multiple class="hidden" @change="handleFiles($event.target.files)">
                        
                        <svg class="mx-auto h-12 w-12 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        
                        <p class="mt-4 text-sm text-slate-300 font-medium">
                            <span class="text-blue-400">Click to upload</span> or drag and drop
                        </p>
                        <p class="mt-1 text-xs text-slate-500">
                            EXE, DLL, PHP, Script files (Max 10MB)
                        </p>
                    </div>

                    <!-- File List -->
                    <div class="mt-4 space-y-2" x-show="files.length > 0">
                        <template x-for="(file, index) in files" :key="index">
                            <div class="flex items-center justify-between p-2 bg-slate-900 rounded border border-slate-700">
                                <span class="text-xs text-slate-300 truncate max-w-[200px]" x-text="file.name"></span>
                                <button @click="removeFile(index)" class="text-slate-500 hover:text-red-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                        </template>
                    </div>

                    <button @click="generateRule()" 
                            :disabled="files.length === 0 || loading"
                            class="mt-6 w-full py-2 bg-blue-600 hover:bg-blue-500 disabled:bg-slate-700 disabled:text-slate-500 text-white rounded-lg font-bold shadow-lg shadow-blue-500/20 transition-all flex items-center justify-center gap-2">
                        <span x-show="!loading">Generate Rule</span>
                        <span x-show="loading">Analysing...</span>
                    </button>
                    
                    <!-- Error Message -->
                    <div x-show="error" x-transition class="mt-4 p-3 bg-red-500/10 border border-red-500/20 rounded text-red-400 text-xs" x-text="error"></div>
                </div>
            </div>

            <!-- Right: Results & History -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Generator Output -->
                <div class="bg-slate-800 rounded-xl border border-slate-700 p-6 shadow-xl flex flex-col h-[600px]">
                    <div class="flex items-center justify-between mb-4 flex-none">
                        <h3 class="text-lg font-bold text-white">Generated YARA Rule</h3>
                        <div class="flex gap-2" x-show="result">
                            <button @click="copyToClipboard()" class="px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-slate-200 text-xs rounded transition-colors border border-slate-600">
                                Copy
                            </button>
                            <button @click="downloadRule()" class="px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-slate-200 text-xs rounded transition-colors border border-slate-600">
                                Download .yar
                            </button>
                        </div>
                    </div>

                    <div class="flex-1 bg-slate-950 rounded-lg border border-slate-800 relative font-mono text-sm leading-relaxed overflow-hidden">
                        <textarea readonly x-model="result" 
                                  class="w-full h-full bg-transparent text-emerald-400 p-4 resize-none focus:outline-none custom-scrollbar overflow-auto whitespace-pre"
                                  placeholder="// Generated YARA rule will appear here..."></textarea>
                        
                        <div x-show="loading" class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm flex items-center justify-center">
                            <div class="text-center">
                                <svg class="w-12 h-12 text-blue-500 animate-spin mx-auto mb-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <p class="text-slate-300 font-medium">Analyzing opcodes & strings...</p>
                                <p class="text-xs text-slate-500 mt-2">This may take a few minutes depending on file size.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- History -->
                <div class="bg-slate-800 rounded-xl border border-slate-700 p-6 shadow-xl">
                    <h3 class="text-lg font-bold text-white mb-4">Recent History</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-400">
                            <thead class="bg-slate-900/50 text-slate-200 uppercase font-medium text-xs">
                                <tr>
                                    <th class="px-4 py-3 rounded-l-lg">Time</th>
                                    <th class="px-4 py-3">Reference</th>
                                    <th class="px-4 py-3">Author</th>
                                    <th class="px-4 py-3">Files</th>
                                    <th class="px-4 py-3 rounded-r-lg text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700/50">
                                <template x-for="item in history" :key="item.id">
                                    <tr class="hover:bg-slate-700/30 transition-colors">
                                        <td class="px-4 py-3 whitespace-nowrap" x-text="formatDate(item.created_at)"></td>
                                        <td class="px-4 py-3 font-medium text-white" x-text="item.reference"></td>
                                        <td class="px-4 py-3" x-text="item.author"></td>
                                        <td class="px-4 py-3 max-w-[200px] truncate" x-text="item.file_names.join(', ')"></td>
                                        <td class="px-4 py-3 text-right">
                                            <button @click="loadHistory(item)" class="text-blue-400 hover:text-blue-300 font-medium text-xs">Load Rule</button>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="history.length === 0">
                                    <td colspan="5" class="px-4 py-8 text-center text-slate-500">No history available yet.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('yarGen', () => ({
                config: {
                    author: '{{ auth()->user()->name }}',
                    reference: 'Investigation ' + new Date().toISOString().split('T')[0]
                },
                dragover: false,
                files: [],
                loading: false,
                result: '',
                error: null,
                history: @json($history),

                handleDrop(e) {
                    this.dragover = false;
                    this.handleFiles(e.dataTransfer.files);
                },

                handleFiles(fileList) {
                    if (fileList.length > 0) {
                        for (let i = 0; i < fileList.length; i++) {
                            if (fileList[i].size > 10 * 1024 * 1024) {
                                alert(`File ${fileList[i].name} is too large (Max 10MB)`);
                                continue;
                            }
                            this.files.push(fileList[i]);
                        }
                    }
                },

                removeFile(index) {
                    this.files.splice(index, 1);
                },

                formatDate(dateString) {
                    const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
                    return new Date(dateString).toLocaleDateString(undefined, options);
                },

                loadHistory(item) {
                     this.result = item.rule_content;
                     this.config.author = item.author;
                     this.config.reference = item.reference;
                     window.scrollTo({ top: 0, behavior: 'smooth' });
                },

                async generateRule() {
                    this.loading = true;
                    this.error = null;
                    this.result = ''; 

                    const formData = new FormData();
                    this.files.forEach(file => {
                        formData.append('files[]', file);
                    });
                    formData.append('author', this.config.author);
                    formData.append('reference', this.config.reference);

                    try {
                        const response = await fetch('{{ route("investigation.yargen.generate") }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: formData
                        });

                        const data = await response.json();

                        if (!response.ok) throw new Error(data.message || 'Generation failed');

                        if (data.status === 'success') {
                            this.result = data.rules;
                            // Prepend new history item
                            if (data.history_item) {
                                this.history.unshift(data.history_item);
                                if (this.history.length > 10) this.history.pop();
                            }
                        } else {
                            throw new Error(data.message);
                        }

                    } catch (err) {
                        console.error(err);
                        this.error = err.message;
                    } finally {
                        this.loading = false;
                    }
                },

                copyToClipboard() {
                    navigator.clipboard.writeText(this.result);
                },

                downloadRule() {
                    if (!this.result) return;
                    const blob = new Blob([this.result], { type: 'text/plain' });
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'generated_rules.yar';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);
                }
            }));
        });
    </script>
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.5);
            border-radius: 5px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(59, 130, 246, 0.5); /* Blue-500 optimized */
            border-radius: 5px;
            border: 2px solid rgba(15, 23, 42, 0.5);
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(59, 130, 246, 0.8);
        }
    </style>
@endsection
