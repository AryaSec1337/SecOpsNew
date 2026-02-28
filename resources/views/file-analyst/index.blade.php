@extends('layouts.dashboard')

@section('content')
<div x-data="fileAnalyst({{ json_encode($logs->items()) }})" class="min-h-screen font-sans text-slate-300">
    
    <!-- Top HUD / Stats Bar -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <!-- Total Scans -->
        <div class="relative overflow-hidden bg-slate-900/50 backdrop-blur-md rounded-xl border border-slate-700/50 p-4 group hover:border-purple-500/50 transition-all duration-500">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-purple-500/10 rounded-full blur-2xl group-hover:bg-purple-500/20 transition-all"></div>
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <p class="text-xs font-bold text-purple-400 uppercase tracking-widest mb-1">Total Scans</p>
                    <h3 class="text-3xl font-black text-white font-mono tracking-tighter">{{ number_format($stats['total']) }}</h3>
                </div>
                <div class="p-2 bg-purple-500/10 rounded-lg border border-purple-500/20 text-purple-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </div>
            </div>
        </div>

        <!-- Malicious Found -->
        <div class="relative overflow-hidden bg-slate-900/50 backdrop-blur-md rounded-xl border border-slate-700/50 p-4 group hover:border-red-500/50 transition-all duration-500">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-red-500/10 rounded-full blur-2xl group-hover:bg-red-500/20 transition-all"></div>
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <p class="text-xs font-bold text-red-500 uppercase tracking-widest mb-1">Threats Detected</p>
                    <h3 class="text-3xl font-black text-white font-mono tracking-tighter">{{ number_format($stats['malicious']) }}</h3>
                </div>
                <div class="p-2 bg-red-500/10 rounded-lg border border-red-500/20 text-red-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
            </div>
             <div class="mt-4 w-full bg-slate-800 rounded-full h-1">
                <div class="bg-red-500 h-1 rounded-full shadow-[0_0_10px_red]" style="width: {{ $stats['total'] > 0 ? ($stats['malicious'] / $stats['total'] * 100) : 0 }}%"></div>
            </div>
        </div>

        <!-- Clean Files -->
        <div class="relative overflow-hidden bg-slate-900/50 backdrop-blur-md rounded-xl border border-slate-700/50 p-4 group hover:border-green-500/50 transition-all duration-500">
             <div class="absolute -right-6 -top-6 w-24 h-24 bg-green-500/10 rounded-full blur-2xl group-hover:bg-green-500/20 transition-all"></div>
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <p class="text-xs font-bold text-green-400 uppercase tracking-widest mb-1">Clean Files</p>
                    <h3 class="text-3xl font-black text-white font-mono tracking-tighter">{{ number_format($stats['clean']) }}</h3>
                </div>
                <div class="p-2 bg-green-500/10 rounded-lg border border-green-500/20 text-green-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>
        
        <!-- Processing -->
         <div class="relative overflow-hidden bg-slate-900/50 backdrop-blur-md rounded-xl border border-slate-700/50 p-4 group hover:border-yellow-500/50 transition-all duration-500">
             <div class="absolute -right-6 -top-6 w-24 h-24 bg-yellow-500/10 rounded-full blur-2xl group-hover:bg-yellow-500/20 transition-all"></div>
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <p class="text-xs font-bold text-yellow-400 uppercase tracking-widest mb-1">Queue / Padding</p>
                    <h3 class="text-3xl font-black text-white font-mono tracking-tighter">{{ number_format($stats['pending']) }}</h3>
                </div>
                <div class="p-2 bg-yellow-500/10 rounded-lg border border-yellow-500/20 text-yellow-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Upload Zone -->
        <div class="lg:col-span-1">
            <div class="bg-slate-900/80 backdrop-blur-xl rounded-2xl border border-slate-800 shadow-2xl overflow-hidden p-6 relative">
                 <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-purple-600 via-blue-600 to-purple-600 opacity-50"></div>
                 
                 <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                    <span class="w-2 h-6 bg-purple-500 rounded-sm shadow-[0_0_10px_purple]"></span>
                    THE ANALYZER
                 </h2>

                 <div 
                    @dragover.prevent="dragover = true"
                    @dragleave.prevent="dragover = false"
                    @drop.prevent="handleDrop($event)"
                    class="relative w-full h-64 border-2 border-dashed rounded-xl flex flex-col items-center justify-center transition-all duration-300"
                    :class="dragover ? 'border-purple-500 bg-purple-500/10 scale-[1.02]' : 'border-slate-700 bg-slate-950 hover:border-slate-600'">
                    
                    <template x-if="!uploading && !analyzing">
                        <div class="flex flex-col items-center justify-center pointer-events-none">
                            <div class="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                                <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                            </div>
                            <p class="text-sm font-bold text-slate-300">Drop Payload Here</p>
                            <p class="text-xs text-slate-500 mt-1">EXE, PDF, DOCX (Max 32MB)</p>
                        </div>
                    </template>

                    <!-- Progress State -->
                    <template x-if="uploading || analyzing">
                        <div class="flex flex-col items-center justify-center w-full px-8">
                             <div class="w-16 h-16 relative flex items-center justify-center mb-4">
                                <div class="absolute w-full h-full border-4 border-slate-800 rounded-full"></div>
                                <div class="absolute w-full h-full border-4 border-t-purple-500 rounded-full animate-spin"></div>
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                             </div>
                             <p class="text-sm font-bold text-white animate-pulse" x-text="uploading ? 'UPLOADING...' : 'ANALYZING...'"></p>
                             <div class="w-full bg-slate-800 h-1 mt-4 rounded-full overflow-hidden">
                                 <div class="h-full bg-purple-500 animate-progress"></div>
                             </div>
                        </div>
                    </template>

                    <input type="file" @change="handleFileSelect" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" :disabled="uploading || analyzing">
                 </div>
                 
                 <div class="mt-4 flex justify-between items-center text-xs font-mono">
                    <span class="text-slate-500" x-text="selectedFile ? selectedFile.name : 'No file selected'"></span>
                    <span :class="uploadStatus.type === 'error' ? 'text-red-500' : 'text-green-500'" x-text="uploadStatus.message"></span>
                 </div>
            </div>
        </div>

        <!-- History Table -->
        <div class="lg:col-span-2">
            <div class="bg-slate-900/80 backdrop-blur-xl rounded-2xl border border-slate-800 shadow-2xl overflow-hidden h-[600px] flex flex-col">
                <div class="p-6 border-b border-slate-800 flex justify-between items-center bg-slate-900/50">
                     <h2 class="text-xl font-bold text-white flex items-center gap-2">
                        <span class="w-2 h-6 bg-blue-500 rounded-sm shadow-[0_0_10px_blue]"></span>
                        ANALYSIS HISTORY
                    </h2>
                </div>
                
                <div class="overflow-auto flex-1">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-950/80 text-xxs uppercase tracking-widest text-slate-500 font-bold font-mono">
                                <th class="p-4 border-b border-slate-800">Time</th>
                                <th class="p-4 border-b border-slate-800">File Name</th>
                                <th class="p-4 border-b border-slate-800">Status</th>
                                <th class="p-4 border-b border-slate-800 text-right">Result</th>
                            </tr>
                        </thead>
                        <tbody class="font-mono text-xs">
                             <template x-for="log in logs" :key="log.id">
                                <tr class="border-b border-slate-800 hover:bg-slate-800/50 transition-all duration-200">
                                    <td class="p-4 text-slate-400" x-text="formatDate(log.created_at)"></td>
                                    <td class="p-4 text-white font-bold truncate max-w-[150px]" :title="log.file_name" x-text="log.file_name"></td>
                                    <td class="p-4">
                                        <span class="px-2 py-1 rounded text-xs font-bold border"
                                            :class="{
                                                'text-yellow-400 border-yellow-900 bg-yellow-900/20 animate-pulse': log.status === 'pending',
                                                'text-green-400 border-green-900 bg-green-900/20': log.status === 'completed',
                                                'text-red-400 border-red-900 bg-red-900/20': log.status === 'error'
                                            }" x-text="log.status"></span>
                                    </td>
                                    <td class="p-4 text-right">
                                        <template x-if="log.status === 'completed' && log.result">
                                            <div class="flex items-center justify-end gap-3">
                                                <span class="font-bold flex items-center gap-1" 
                                                    :class="getStats(log).malicious > 0 ? 'text-red-500 shadow-[0_0_10px_red]' : 'text-green-500'">
                                                     <template x-if="getStats(log).malicious > 0">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                     </template>
                                                     <span x-text="getStats(log).text"></span>
                                                </span>
                                                <button @click="openDetail(log)" class="text-slate-400 hover:text-white underline">View</button>
                                            </div>
                                        </template>
                                        <template x-if="log.status === 'pending'">
                                            <span class="text-slate-500 italic">Scanning...</span>
                                        </template>
                                    </td>
                                </tr>
                             </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div x-show="modalOpen" class="fixed inset-0 bg-slate-950/90 backdrop-blur-sm" @click="closeDetail()"></div>

        <div x-show="modalOpen" class="bg-slate-900 border border-slate-700 w-full max-w-2xl rounded-2xl shadow-2xl relative overflow-hidden z-20">
             
             <!-- Header -->
             <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center bg-slate-950">
                 <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Analysis Report
                 </h3>
                 <button @click="closeDetail()" class="text-slate-500 hover:text-white transition-colors">
                     <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                 </button>
             </div>

             <!-- Body -->
             <div class="p-6 overflow-y-auto max-h-[70vh]">
                 <template x-if="selectedLog">
                    <div class="space-y-6">
                        
                        <!-- Overview Score -->
                        <div class="flex items-center justify-center py-6">
                            <div class="relative w-32 h-32 flex items-center justify-center rounded-full border-4 shadow-[0_0_20px_currentColor]"
                                :class="getStats(selectedLog).malicious > 0 ? 'border-red-500 text-red-500' : 'border-green-500 text-green-500'">
                                <div class="text-center">
                                    <span class="block text-3xl font-black" x-text="getStats(selectedLog).malicious"></span>
                                    <span class="block text-xs font-bold uppercase">Detections</span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 text-center">
                            <div class="bg-slate-950 p-3 rounded-lg border border-slate-800">
                                <span class="block text-xs text-slate-500 uppercase">File Name</span>
                                <span class="block text-sm text-white font-mono truncate" x-text="selectedLog.file_name"></span>
                            </div>
                             <div class="bg-slate-950 p-3 rounded-lg border border-slate-800">
                                <span class="block text-xs text-slate-500 uppercase">Total Engines</span>
                                <span class="block text-sm text-white font-bold" x-text="getStats(selectedLog).total"></span>
                            </div>
                        </div>

                        <!-- Engine Results -->
                        <div>
                             <h4 class="text-xs font-bold text-slate-500 uppercase mb-3">Engine Detectors</h4>
                             <div class="grid grid-cols-2 gap-2 h-64 overflow-y-auto pr-2 custom-scrollbar">
                                <template x-for="engine in getEngineResults(selectedLog)" :key="engine.name">
                                    <div class="flex justify-between items-center p-2 rounded bg-slate-950 border border-slate-800"
                                        :class="engine.isBad ? 'border-red-500/30 bg-red-900/10' : ''">
                                        <span class="text-xs font-bold text-slate-400" x-text="engine.name"></span>
                                        <span class="text-xs font-bold" :class="engine.isBad ? 'text-red-500' : 'text-green-500'" x-text="engine.result || 'Clean'"></span>
                                    </div>
                                </template>
                             </div>
                        </div>

                        <!-- YARA Matches -->
                        <template x-if="selectedLog.yara_matches && selectedLog.yara_matches.matches && selectedLog.yara_matches.matches.length > 0">
                            <div class="mt-6 border-t border-slate-800 pt-6">
                                <h4 class="text-xs font-bold text-slate-500 uppercase mb-3 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                                    YARA Rule Matches
                                </h4>
                                <div class="space-y-2">
                                    <template x-for="match in selectedLog.yara_matches.matches" :key="match.rule">
                                        <div class="p-3 rounded bg-purple-900/20 border border-purple-500/30">
                                            <div class="flex justify-between items-center mb-1">
                                                <span class="text-sm font-bold text-purple-400" x-text="match.rule"></span>
                                                <span class="text-xs text-slate-400" x-text="match.namespace"></span>
                                            </div>
                                            <div class="flex flex-wrap gap-1 mt-2" x-show="match.tags && match.tags.length">
                                                <template x-for="tag in match.tags">
                                                    <span class="px-2 py-0.5 rounded text-[10px] bg-slate-800 text-slate-300 border border-slate-700" x-text="tag"></span>
                                                </template>
                                            </div>
                                            <!-- Strings Debug -->
                                            <!-- 
                                            <div class="mt-2 p-2 bg-slate-950 rounded text-xs font-mono text-slate-500 overflow-x-auto">
                                                <template x-for="str in match.strings">
                                                    <div x-text="str[1] + ': ' + str[2]"></div>
                                                </template>
                                            </div> 
                                            -->
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                        <template x-if="selectedLog.yara_matches && (!selectedLog.yara_matches.matches || selectedLog.yara_matches.matches.length === 0)">
                             <div class="mt-6 border-t border-slate-800 pt-6">
                                <h4 class="text-xs font-bold text-slate-500 uppercase mb-3">YARA Analysis</h4>
                                <div class="p-3 rounded bg-slate-800/50 border border-slate-800 text-center">
                                    <p class="text-sm text-slate-400">No YARA rules matched this file.</p>
                                </div>
                             </div>
                        </template>

                    </div>
                 </template>
             </div>
        </div>
    </div>

</div>

<style>
    @keyframes progress {
        0% { width: 0%; }
        100% { width: 100%; }
    }
    .animate-progress {
        animation: progress 2s ease-in-out infinite;
    }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #1e293b; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; border-radius: 4px; }
</style>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('fileAnalyst', (initialLogs) => ({
            logs: initialLogs,
            dragover: false,
            uploading: false,
            analyzing: false,
            selectedFile: null,
            modalOpen: false,
            selectedLog: null,
            uploadStatus: { message: '', type: '' },
            csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            init() {
                // Poll for pending items every 5s
                setInterval(() => this.pollPending(), 5000);
            },

            handleFileSelect(e) {
                if (e.target.files.length) {
                    this.selectedFile = e.target.files[0];
                    this.uploadFile();
                }
            },

            handleDrop(e) {
                this.dragover = false;
                if (e.dataTransfer.files.length) {
                    this.selectedFile = e.dataTransfer.files[0];
                    this.uploadFile();
                }
            },

            async uploadFile() {
                if (!this.selectedFile) return;
                
                this.uploading = true;
                this.uploadStatus = { message: 'Uploading...', type: 'info' };

                const formData = new FormData();
                formData.append('file', this.selectedFile);

                try {
                    const res = await fetch('{{ route("file-analyst.analyze") }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': this.csrf },
                        body: formData
                    });
                    
                    const data = await res.json();
                    
                    if (res.ok) {
                        this.uploading = false;
                        this.analyzing = true;
                        this.uploadStatus = { message: 'Upload complete. Queued for analysis.', type: 'success' };
                        
                        // Wait a bit then reload to show new item (or prepend it manually if improved)
                        setTimeout(() => window.location.reload(), 1500); 
                    } else {
                        throw new Error(data.error || 'Upload failed');
                    }
                } catch (e) {
                    this.uploading = false;
                    this.analyzing = false;
                    this.uploadStatus = { message: e.message, type: 'error' };
                }
            },

            async pollPending() {
                const pending = this.logs.filter(l => l.status === 'pending');
                if (pending.length === 0) return;

                for (let log of pending) {
                    try {
                        const res = await fetch(`/file-analyst/status/${log.id}`);
                        const data = await res.json();
                        if (data.status === 'completed') {
                            log.status = 'completed';
                            log.result = data.result;
                            this.showNotification('Scan Finished', `${log.file_name} analysis complete.`, 'success');
                        }
                    } catch (e) { console.error('Poll error', e); }
                }
            },

            showNotification(title, msg, type) {
                const Toast = Swal.mixin({
                    toast: true, position: 'top-end', showConfirmButton: false, timer: 3000,
                    background: '#1e293b', color: '#fff'
                });
                Toast.fire({ icon: type, title: title, text: msg });
            },

            openDetail(log) {
                this.selectedLog = log;
                this.modalOpen = true;
            },

            closeDetail() {
                this.modalOpen = false;
            },
            
            formatDate(dateStr) {
                 return new Date(dateStr).toLocaleString('id-ID', {
                        day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit'
                 });
            },

            getStats(log) {
                if (!log.result) return { malicious: 0, total: 0, text: 'N/A' };
                const attr = log.result.attributes || log.result;
                const stats = attr.last_analysis_stats || attr.stats;
                if (!stats) return { malicious: 0, total: 0, text: 'No Stats' };
                
                const mal = (stats.malicious || 0) + (stats.suspicious || 0);
                const total = (stats.harmless || 0) + (stats.undetected || 0) + mal;
                
                return {
                    malicious: mal,
                    total: total,
                    text: mal > 0 ? `${mal} Malicious` : 'Clean'
                };
            },

            getEngineResults(log) {
                if (!log.result) return [];
                const attr = log.result.attributes || log.result;
                const results = attr.last_analysis_results || attr.results || {};
                
                return Object.entries(results).map(([name, res]) => ({
                    name: name,
                    result: res.result || res.category,
                    isBad: res.category === 'malicious' || res.category === 'suspicious'
                })).sort((a, b) => (a.isBad === b.isBad) ? 0 : a.isBad ? -1 : 1);
            }
        }));
    });
</script>
@endsection
