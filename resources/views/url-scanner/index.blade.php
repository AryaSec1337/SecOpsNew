@extends('layouts.dashboard')

@section('content')
<script>
    window.scanLogs = {!! json_encode($logs->items()) !!};
</script>
<div x-data="urlScanner(window.scanLogs)" class="min-h-screen font-sans text-slate-300">

    <!-- Top Stats HUD -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <!-- Total Scans -->
        <div class="relative overflow-hidden bg-slate-900/50 backdrop-blur-md rounded-xl border border-slate-700/50 p-4 group hover:border-cyan-500/50 transition-all duration-500">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-cyan-500/10 rounded-full blur-2xl group-hover:bg-cyan-500/20 transition-all"></div>
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <p class="text-xs font-bold text-cyan-400 uppercase tracking-widest mb-1">Total Scans</p>
                    <h3 class="text-3xl font-black text-white font-mono tracking-tighter">{{ number_format($stats['total']) }}</h3>
                </div>
                <div class="p-2 bg-cyan-500/10 rounded-lg border border-cyan-500/20 text-cyan-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
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

        <!-- Safe Links -->
        <div class="relative overflow-hidden bg-slate-900/50 backdrop-blur-md rounded-xl border border-slate-700/50 p-4 group hover:border-green-500/50 transition-all duration-500">
             <div class="absolute -right-6 -top-6 w-24 h-24 bg-green-500/10 rounded-full blur-2xl group-hover:bg-green-500/20 transition-all"></div>
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <p class="text-xs font-bold text-green-400 uppercase tracking-widest mb-1">Safe Links</p>
                    <h3 class="text-3xl font-black text-white font-mono tracking-tighter">{{ number_format($stats['safe']) }}</h3>
                </div>
                <div class="p-2 bg-green-500/10 rounded-lg border border-green-500/20 text-green-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>
        
        <!-- Queue -->
        <div class="relative overflow-hidden bg-slate-900/50 backdrop-blur-md rounded-xl border border-slate-700/50 p-4 group hover:border-blue-500/50 transition-all duration-500">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-blue-500/10 rounded-full blur-2xl group-hover:bg-blue-500/20 transition-all"></div>
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <p class="text-xs font-bold text-blue-400 uppercase tracking-widest mb-1">Analyzing</p>
                    <h3 class="text-3xl font-black text-white font-mono tracking-tighter">{{ number_format($stats['pending']) }}</h3>
                </div>
                <div class="p-2 bg-blue-500/10 rounded-lg border border-blue-500/20 text-blue-400">
                    <svg class="w-6 h-6 animate-spin-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Radar / Input Section -->
        <div class="lg:col-span-1">
             <div class="bg-slate-900/80 backdrop-blur-xl rounded-2xl border border-slate-800 shadow-2xl overflow-hidden p-6 relative group">
                <!-- Radar Animation Background -->
                <div class="absolute inset-0 z-0 opacity-20 pointer-events-none overflow-hidden rounded-2xl">
                     <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,_transparent_0%,_rgba(6,182,212,0.1)_70%)]"></div>
                     <div class="absolute top-1/2 left-1/2 w-[200%] h-[200%] -translate-x-1/2 -translate-y-1/2 border border-cyan-500/20 rounded-full"></div>
                     <div class="absolute top-1/2 left-1/2 w-[150%] h-[150%] -translate-x-1/2 -translate-y-1/2 border border-cyan-500/20 rounded-full"></div>
                     <div class="absolute top-1/2 left-1/2 w-full h-1 bg-cyan-500/30 origin-left animate-radar-sweep" x-show="scanning"></div>
                </div>

                <div class="relative z-10">
                    <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                        <span class="w-2 h-6 bg-cyan-500 rounded-sm shadow-[0_0_10px_cyan]"></span>
                        URL RADAR
                    </h2>

                    <form @submit.prevent="scanUrl" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-cyan-500 uppercase mb-2">Target URL</label>
                            <div class="relative">
                                <span class="absolute left-3 top-3 text-slate-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </span>
                                <input type="url" x-model="urlInput" placeholder="https://malicious-site.com" 
                                    class="w-full bg-slate-950 border border-slate-700 text-white pl-10 pr-4 py-3 rounded-xl focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all font-mono shadow-inner" required :disabled="scanning">
                            </div>
                        </div>

                        <button type="submit" :disabled="scanning" class="w-full relative group overflow-hidden rounded-xl p-[1px]">
                             <div class="absolute inset-0 bg-gradient-to-r from-cyan-500 to-blue-600 rounded-xl transition-all duration-300 group-hover:scale-105"></div>
                             <div class="relative bg-slate-900 h-full rounded-[11px] hover:bg-opacity-0 transition-all duration-300">
                                 <div class="px-4 py-3 flex items-center justify-center gap-2 text-white font-bold tracking-wide">
                                     <template x-if="!scanning">
                                        <span class="flex items-center gap-2">INITIATE SCAN <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg></span>
                                     </template>
                                     <template x-if="scanning">
                                        <span class="flex items-center gap-2">
                                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            SCANNING TARGET...
                                        </span>
                                     </template>
                                 </div>
                             </div>
                        </button>

                         <div class="text-center text-xs font-mono h-4">
                            <span :class="scanStatus.type === 'error' ? 'text-red-500' : 'text-cyan-400'" x-text="scanStatus.message"></span>
                        </div>
                    </form>

                     <div class="mt-8 p-4 bg-slate-950 rounded-lg border border-slate-800 text-xs text-slate-400">
                        <h4 class="font-bold text-slate-300 mb-2 flex items-center gap-2"><svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> INTELLIGENCE FEED</h4>
                        <p>Powered by VirusTotal API. Scans against 70+ antivirus engines including Google Safebrowsing, PhishTank, and Sophos.</p>
                    </div>
                </div>
             </div>
        </div>

        <!-- History Table -->
        <div class="lg:col-span-2">
            <div class="bg-slate-900/80 backdrop-blur-xl rounded-2xl border border-slate-800 shadow-2xl overflow-hidden h-[600px] flex flex-col">
                <div class="p-6 border-b border-slate-800 flex justify-between items-center bg-slate-900/50">
                     <h2 class="text-xl font-bold text-white flex items-center gap-2">
                        <span class="w-2 h-6 bg-blue-500 rounded-sm shadow-[0_0_10px_blue]"></span>
                        SCAN LOGS
                    </h2>
                </div>
                
                <div class="overflow-auto flex-1">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-950/80 text-xxs uppercase tracking-widest text-slate-500 font-bold font-mono">
                                <th class="p-4 border-b border-slate-800">Time</th>
                                <th class="p-4 border-b border-slate-800">Target URL</th>
                                <th class="p-4 border-b border-slate-800">Status</th>
                                <th class="p-4 border-b border-slate-800 text-right">Verdict</th>
                            </tr>
                        </thead>
                        <tbody class="font-mono text-xs">
                             <template x-for="log in logs" :key="log.id">
                                <tr class="border-b border-slate-800 hover:bg-slate-800/50 transition-all duration-200">
                                    <td class="p-4 text-slate-400" x-text="formatDate(log.created_at)"></td>
                                    <td class="p-4 text-white font-bold truncate max-w-[200px]" :title="log.url">
                                        <a :href="log.url" target="_blank" class="hover:text-cyan-400 hover:underline flex items-center gap-1">
                                            <span x-text="log.url"></span>
                                            <svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                        </a>
                                    </td>
                                    <td class="p-4">
                                        <span class="px-2 py-1 rounded text-xs font-bold border"
                                            :class="{
                                                'text-blue-400 border-blue-900 bg-blue-900/20 animate-pulse': log.status === 'pending',
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
                                                <button @click="openDetail(log)" class="text-slate-400 hover:text-white underline">Report</button>
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
                    <svg class="w-6 h-6 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Domain Intelligence Report
                 </h3>
                 <button @click="closeDetail()" class="text-slate-500 hover:text-white transition-colors">
                     <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                 </button>
             </div>

             <!-- Body -->
             <div class="p-6 overflow-y-auto max-h-[70vh]">
                 <template x-if="selectedLog">
                    <div class="space-y-6">
                        
                        <!-- Gauges -->
                        <div class="flex items-center justify-center py-6 gap-8">
                            <div class="relative w-32 h-32 flex items-center justify-center rounded-full border-4 shadow-[0_0_20px_currentColor]"
                                :class="getStats(selectedLog).malicious > 0 ? 'border-red-500 text-red-500' : 'border-green-500 text-green-500'">
                                <div class="text-center">
                                    <span class="block text-3xl font-black" x-text="getStats(selectedLog).malicious"></span>
                                    <span class="block text-xs font-bold uppercase">Active Threats</span>
                                </div>
                            </div>
                        </div>

                        <!-- Target Info -->
                        <div class="bg-slate-950 p-4 rounded-lg border border-slate-800 font-mono break-all text-center">
                            <span class="text-xs text-slate-500 uppercase block mb-1">Target URL</span>
                            <span class="text-blue-400 font-bold" x-text="selectedLog.url"></span>
                        </div>

                        <!-- Engine Results -->
                        <div>
                             <h4 class="text-xs font-bold text-slate-500 uppercase mb-3 text-center">Engine Verdicts</h4>
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

                    </div>
                 </template>
             </div>
        </div>
    </div>
</div>

<style>
    @keyframes radar-sweep {
        0% { transform: translate(-50%, -50%) rotate(0deg); }
        100% { transform: translate(-50%, -50%) rotate(360deg); }
    }
    .animate-radar-sweep {
        animation: radar-sweep 4s linear infinite;
        transform-origin: left center; /* Actually manipulated via top-1/2 left-1/2 */
    }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #1e293b; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; border-radius: 4px; }
</style>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('urlScanner', (initialLogs) => ({
            logs: initialLogs,
            urlInput: '',
            scanning: false,
            scanStatus: { message: '', type: '' },
            modalOpen: false,
            selectedLog: null,
            csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            init() {
                setInterval(() => this.pollPending(), 5000);
            },

            async scanUrl() {
                if (!this.urlInput) return;
                
                this.scanning = true;
                this.scanStatus = { message: 'Initiating scan...', type: 'info' };

                const formData = new FormData();
                formData.append('url', this.urlInput);

                try {
                    const res = await fetch('{{ route("url-scanner.scan") }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': this.csrf },
                        body: formData
                    });
                    
                    const data = await res.json();
                    
                    if (res.ok) {
                        this.scanStatus = { message: 'Scan queued. Waiting for results...', type: 'success' };
                        // Reload to show the new pending item
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        throw new Error(data.error || 'Scan failed');
                    }
                } catch (e) {
                    this.scanning = false;
                    this.scanStatus = { message: e.message, type: 'error' };
                }
            },

            async pollPending() {
                const pending = this.logs.filter(l => l.status === 'pending');
                if (pending.length === 0) return;

                for (let log of pending) {
                    try {
                        const res = await fetch(`/url-scanner/status/${log.id}`);
                        const data = await res.json();
                        if (data.status === 'completed') {
                            log.status = 'completed';
                            log.result = data.result;
                            this.showNotification('Scan Complete', `Analysis finished for ${log.url}`, 'success');
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
            closeDetail() { this.modalOpen = false; },

            formatDate(dateStr) {
                 return new Date(dateStr).toLocaleString('id-ID', {
                        day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit'
                 });
            },

            getStats(log) {
                if (!log.result) return { malicious: 0, text: 'N/A' };
                const attr = log.result.attributes || log.result;
                const stats = attr.last_analysis_stats || attr.stats;
                if (!stats) return { malicious: 0, text: 'No Stats' };
                
                const mal = (stats.malicious || 0) + (stats.suspicious || 0);
                return {
                    malicious: mal,
                    text: mal > 0 ? `${mal} Threats` : 'Safe'
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
