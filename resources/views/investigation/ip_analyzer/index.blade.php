@extends('layouts.dashboard')

@section('content')
<!-- Custom Styles -->
<style>
    @keyframes scan-line {
        0% { transform: translateY(-100%); opacity: 0; }
        50% { opacity: 1; }
        100% { transform: translateY(100%); opacity: 0; }
    }
    .animate-scan { animation: scan-line 2.5s cubic-bezier(0.4, 0, 0.2, 1) infinite; }
    .glass-panel {
        background: rgba(10, 10, 15, 0.6);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: rgba(0,0,0,0.2); }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 2px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }
    
    .bg-grid-pattern {
        background-image: linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        background-size: 30px 30px;
    }
</style>

<div class="h-[calc(100vh-80px)] font-sans text-slate-300 relative overflow-hidden flex" x-data="ipAnalyzer()">
    
    <!-- Background -->
    <div class="absolute inset-0 bg-slate-950 bg-grid-pattern pointer-events-none z-0"></div>

    <!-- LEFT SIDEBAR: CONTROLS & HISTORY (30%) -->
    <div class="w-full md:w-[350px] lg:w-[400px] shrink-0 border-r border-white/5 bg-slate-900/50 backdrop-blur-md flex flex-col z-10 relative">
        
        <!-- Header -->
        <div class="p-6 border-b border-white/5">
            <h1 class="text-xl font-black text-white tracking-widest flex items-center gap-2 neon-text-amber">
                <span class="text-amber-500">IP</span> ANALYZER
            </h1>
            <p class="text-xs text-slate-500 font-mono mt-1">> THREAT_INTEL_MODULE_V2</p>
        </div>

        <!-- Search Input -->
        <div class="p-6 pb-2">
            <form @submit.prevent="analyzeIp">
                <label class="text-[10px] uppercase font-bold text-slate-500 tracking-wider mb-2 block">Target IP</label>
                <div class="relative group">
                    <input type="text" x-model="ip" placeholder="e.g. 1.1.1.1" 
                        class="w-full bg-black/40 border border-slate-700 text-white pl-4 pr-12 py-3 rounded-lg focus:border-amber-500 focus:ring-1 focus:ring-amber-500/50 transition-all font-mono text-sm group-hover:border-slate-500"
                        :disabled="loading">
                    <button type="submit" :disabled="loading || !ip" 
                        class="absolute right-2 top-2 bottom-2 px-3 bg-slate-800 hover:bg-amber-600 text-slate-300 hover:text-white rounded transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg x-show="!loading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </button>
                </div>
                <div class="mt-2 flex justify-between text-[10px] font-mono text-slate-600">
                    <span>STATUS: <span x-text="loading ? 'SCANNING...' : 'IDLE'" :class="loading ? 'text-amber-500 animate-pulse' : 'text-emerald-500'"></span></span>
                    <span>API: READY</span>
                </div>
            </form>
        </div>

        <!-- History List -->
        <div class="flex-1 overflow-hidden flex flex-col p-6 pt-2">
           <div class="flex justify-between items-center mb-4">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Recent Scans
                </h3>
                <button @click="fetchHistory" class="text-[10px] text-amber-500 hover:text-amber-400">REFRESH</button>
           </div>
           
           <div class="flex-1 overflow-y-auto custom-scrollbar space-y-2 pr-1">
                <template x-for="item in history" :key="item.id">
                    <div @click="loadFromHistory(item)" class="p-3 rounded border border-white/5 bg-slate-900/30 hover:bg-slate-800 cursor-pointer transition-all group border-l-2"
                         :class="{
                            'border-l-emerald-500': item.risk_score < 30,
                            'border-l-amber-500': item.risk_score >= 30 && item.risk_score < 70,
                            'border-l-red-500': item.risk_score >= 70
                         }">
                        <div class="flex justify-between items-start mb-1">
                            <span class="font-mono text-xs font-bold text-white group-hover:text-amber-400 transition-colors" x-text="item.ip_address"></span>
                            <span class="text-[10px] font-bold" 
                                  :class="{
                                    'text-emerald-400': item.risk_score < 30,
                                    'text-amber-400': item.risk_score >= 30 && item.risk_score < 70,
                                    'text-red-400': item.risk_score >= 70
                                  }" x-text="item.risk_score + '% Risk'"></span>
                        </div>
                         <div class="flex justify-between items-end text-[10px] text-slate-500">
                            <span x-text="new Date(item.created_at).toLocaleString()"></span>
                            <svg class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </div>
                    </div>
                </template>
                <div x-show="history.length === 0" class="text-center py-8 text-xs text-slate-600 italic">
                    No scan history available.
                </div>
           </div>
        </div>
    </div>

    <!-- RIGHT CONTENT: RESULTS (70%) -->
    <div class="flex-1 relative overflow-y-auto custom-scrollbar p-6 lg:p-10 scroll-smooth">
        
        <!-- Empty State -->
        <div x-show="!result && !loading && !error" class="h-full flex flex-col items-center justify-center text-slate-600 opacity-50">
             <svg class="w-24 h-24 mb-4 text-slate-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
             <p class="text-sm font-mono uppercase tracking-widest">Initiate scan or select from history</p>
        </div>
        
        <!-- Loading State -->
        <div x-show="loading" class="h-full flex flex-col items-center justify-center">
            <div class="relative w-24 h-24 mb-8">
                <div class="absolute inset-0 border-t-2 border-amber-500 rounded-full animate-spin"></div>
                <div class="absolute inset-2 border-r-2 border-slate-700 rounded-full animate-spin-reverse"></div>
                <div class="absolute inset-0 flex items-center justify-center font-mono font-bold text-amber-500 animate-pulse text-xs">SCANNING</div>
            </div>
            <div class="font-mono text-sm text-amber-500 tracking-widest animate-pulse">ACQUIRING TELEMETRY...</div>
        </div>

        <!-- ERROR MESSAGE -->
        <div x-show="error" x-transition class="bg-red-900/20 border border-red-500/50 p-4 rounded-lg flex items-center gap-3 text-red-400 mb-8 max-w-2xl mx-auto mt-10">
             <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
             <span x-text="errorMessage" class="font-mono text-sm"></span>
        </div>

        <!-- RESULT DASHBOARD -->
        
        <!-- ALIENVAULT MODAL -->
        <div x-show="showPulseModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
             x-transition.opacity>
             <div class="bg-slate-900 border border-purple-500/30 rounded-2xl w-full max-w-2xl max-h-[80vh] flex flex-col shadow-2xl shadow-purple-900/20" @click.outside="closePulseModal">
                 <div class="p-4 border-b border-white/5 flex justify-between items-center bg-slate-800/50 rounded-t-2xl">
                     <h3 class="text-white font-bold text-sm tracking-wider flex items-center gap-2">
                         <span class="text-purple-400">OTX</span> PULSE DETAIL
                     </h3>
                     <button @click="closePulseModal" class="text-slate-500 hover:text-white transition-colors">
                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                     </button>
                 </div>
                 <div class="p-6 overflow-y-auto custom-scrollbar">
                     <template x-if="selectedPulse">
                        <div class="space-y-6">
                            <div>
                                <h4 class="text-lg font-bold text-white mb-2" x-text="selectedPulse.name"></h4>
                                <p class="text-sm text-slate-400" x-text="selectedPulse.description || 'No description provided.'"></p>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-black/30 p-3 rounded border border-white/5">
                                    <div class="text-[10px] text-slate-500 uppercase">Created</div>
                                    <div class="text-sm text-slate-300" x-text="new Date(selectedPulse.created).toLocaleDateString()"></div>
                                </div>
                                <div class="bg-black/30 p-3 rounded border border-white/5">
                                    <div class="text-[10px] text-slate-500 uppercase">Author</div>
                                    <div class="text-sm text-slate-300" x-text="selectedPulse.author_name"></div>
                                </div>
                            </div>
                            
                            <div>
                                <div class="text-[10px] text-slate-500 uppercase mb-2">Tags & References</div>
                                <div class="flex flex-wrap gap-2 mb-4">
                                    <template x-for="tag in selectedPulse.tags">
                                        <span class="px-2 py-1 rounded bg-purple-900/20 text-purple-300 text-xs border border-purple-500/20" x-text="tag"></span>
                                    </template>
                                </div>
                                <a :href="'https://otx.alienvault.com/pulse/' + selectedPulse.id" target="_blank" class="inline-flex items-center gap-2 text-xs text-blue-400 hover:text-blue-300 transition-colors">
                                    View Full OTX Report <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                </a>
                            </div>

                            <div x-show="selectedPulse.indicators && selectedPulse.indicators.length > 0">
                                 <div class="text-[10px] text-slate-500 uppercase mb-2">Indicators (IOCs)</div>
                                 <div class="bg-black/40 rounded border border-white/5 overflow-hidden">
                                     <table class="w-full text-left text-xs">
                                         <thead class="bg-white/5 text-slate-400">
                                             <tr>
                                                 <th class="p-2">Type</th>
                                                 <th class="p-2">Indication</th>
                                             </tr>
                                         </thead>
                                         <tbody class="text-slate-300">
                                             <template x-for="ioc in selectedPulse.indicators.slice(0, 10)">
                                                 <tr class="border-b border-white/5 last:border-0 hover:bg-white/5">
                                                     <td class="p-2 font-mono text-amber-400" x-text="ioc.type"></td>
                                                     <td class="p-2 font-mono truncate max-w-xs" x-text="ioc.indicator"></td>
                                                 </tr>
                                             </template>
                                         </tbody>
                                     </table>
                                     <div x-show="selectedPulse.indicators.length > 10" class="p-2 text-center text-[10px] text-slate-500 italic">
                                         Showing top 10 of <span x-text="selectedPulse.indicators.length"></span> indicators.
                                     </div>
                                 </div>
                            </div>

                        </div>
                     </template>
                 </div>
                 <div class="p-4 border-t border-white/5 bg-slate-800/50 rounded-b-2xl text-right">
                     <button @click="closePulseModal" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded text-xs font-bold transition-colors">CLOSE</button>
                 </div>
             </div>
        </div>

        <div x-show="result && !loading" x-transition.opacity.duration.500ms class="max-w-6xl mx-auto space-y-8 pb-20">
            
            <!-- HEADER INFO -->
             <div class="flex flex-col md:flex-row justify-between items-end border-b border-white/5 pb-4">
                <div>
                    <div class="text-[10px] uppercase text-slate-500 font-bold tracking-widest mb-1">Target Assessment</div>
                    <div class="text-4xl font-black text-white font-mono" x-text="result?.ip"></div>
                </div>
                <div class="flex gap-2 mt-4 md:mt-0">
                    <button @click="showRaw = !showRaw" class="px-3 py-1.5 rounded border border-slate-700 text-xs text-slate-400 hover:text-white hover:border-white transition-colors uppercase font-bold tracking-wider">
                        JSON Raw
                    </button>
                    <button @click="exportReport()" class="px-3 py-1.5 rounded bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold uppercase tracking-wider transition-colors shadow-lg shadow-amber-900/20">
                        Export Report
                    </button>
                </div>
            </div>

             <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- COL 1: RISK & GEO (1/3) -->
                <div class="space-y-6">
                     <!-- RISK GAUGE -->
                    <div class="glass-panel rounded-xl p-6 relative overflow-hidden text-center group">
                        <div class="absolute inset-0 bg-gradient-to-b from-slate-800/20 to-transparent pointer-events-none"></div>
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-6">Threat Score</h3>
                        
                        <div class="relative w-40 h-40 mx-auto mb-4">
                             <svg class="w-full h-full transform -rotate-90">
                                <circle cx="80" cy="80" r="70" stroke="rgba(255,255,255,0.05)" stroke-width="8" fill="none"/>
                                <circle cx="80" cy="80" r="70" stroke="currentColor" stroke-width="8" fill="none" stroke-linecap="round"
                                        :stroke-dasharray="2 * Math.PI * 70" 
                                        :stroke-dashoffset="2 * Math.PI * 70 * (1 - (result?.risk_score / 100))"
                                        class="transition-all duration-1000 ease-out drop-shadow-[0_0_10px_currentColor]"
                                        :class="{
                                            'text-emerald-500': result?.risk_score < 30,
                                            'text-amber-500': result?.risk_score >= 30 && result?.risk_score < 70,
                                            'text-red-500': result?.risk_score >= 70
                                        }" />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-4xl font-black text-white" x-text="result?.risk_score"></span>
                                <span class="text-[10px] text-slate-500 font-bold">%</span>
                            </div>
                        </div>
                        <div class="inline-block px-3 py-1 rounded text-xs font-bold uppercase border"
                             :class="{
                                'border-emerald-500/30 bg-emerald-500/10 text-emerald-400': result?.risk_score < 30,
                                'border-amber-500/30 bg-amber-500/10 text-amber-400': result?.risk_score >= 30 && result?.risk_score < 70,
                                'border-red-500/30 bg-red-500/10 text-red-500 animate-pulse': result?.risk_score >= 70
                             }">
                             <span x-text="result?.risk_score < 30 ? 'SAFE' : (result?.risk_score < 70 ? 'SUSPICIOUS' : 'MALICIOUS')"></span>
                        </div>
                    </div>

                    <!-- GEO -->
                     <div class="glass-panel rounded-xl p-6 relative overflow-hidden">
                        <h3 class="flex items-center gap-2 text-xs font-bold text-blue-400 uppercase tracking-widest mb-4">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Location
                        </h3>
                        <div class="space-y-3">
                            <div>
                                <div class="text-[10px] text-slate-500 uppercase">Country</div>
                                <div class="text-lg text-white font-bold flex items-center gap-2">
                                    <span x-text="result?.geo?.country ?? 'N/A'"></span>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <div class="text-[10px] text-slate-500 uppercase">Region</div>
                                    <div class="text-sm text-slate-300" x-text="result?.geo?.region ?? '-'"></div>
                                </div>
                                 <div>
                                    <div class="text-[10px] text-slate-500 uppercase">Coords</div>
                                    <div class="text-sm text-slate-300 font-mono" x-text="result?.geo?.loc ?? '-'"></div>
                                </div>
                            </div>
                            <div class="pt-2 border-t border-white/5">
                                 <div class="text-[10px] text-slate-500 uppercase">ASN / ISP</div>
                                 <div class="text-xs text-slate-300 font-mono truncate" x-text="result?.geo?.org ?? '-'"></div>
                            </div>
                        </div>
                     </div>
                </div>

                <!-- COL 2 & 3: DETAILS (2/3) -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- GRID SOURCES -->
                     <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- VT -->
                        <div class="glass-panel rounded-xl p-6 border-t-2 border-t-red-500 relative flex flex-col h-full">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-xs font-bold text-red-500 uppercase tracking-widest flex items-center gap-2">Virustotal</h3>
                                <span class="text-xl font-black text-white" x-text="result?.virustotal?.reputation ?? 0"></span>
                            </div>
                            
                            <div class="space-y-4 flex-1">
                                <!-- Bar -->
                                <div class="bg-slate-800 h-1.5 rounded-full overflow-hidden w-full">
                                    <div class="bg-red-500 h-full" :style="'width: ' + (((result?.virustotal?.last_analysis_stats?.malicious ?? 0) / 90) * 100) + '%'"></div>
                                </div>
                                <div class="flex justify-between text-[10px] text-slate-400 font-mono">
                                    <span class="text-white"><span x-text="result?.virustotal?.last_analysis_stats?.malicious ?? 0"></span> Malicious</span>
                                    <span><span x-text="result?.virustotal?.last_analysis_stats?.suspicious ?? 0"></span> Suspicious</span>
                                </div>

                                <!-- Detections List -->
                                <div class="max-h-32 overflow-y-auto custom-scrollbar border-t border-white/5 pt-2">
                                     <template x-for="(val, key) in result?.virustotal?.last_analysis_results" :key="key">
                                        <div x-show="val.category === 'malicious' || val.category === 'suspicious'" class="flex justify-between items-center py-1 text-[10px] border-b border-white/5 last:border-0 hover:bg-white/5 px-1 rounded">
                                            <span class="text-slate-300 truncate w-24" x-text="val.engine_name"></span>
                                            <span class="uppercase font-bold" :class="val.category === 'malicious' ? 'text-red-400' : 'text-amber-400'" x-text="val.result"></span>
                                        </div>
                                     </template>
                                     <div x-show="!Object.values(result?.virustotal?.last_analysis_results || {}).some(v => v.category === 'malicious')" class="text-[10px] text-slate-600 text-center py-2 italic">
                                         Clean Engine Scan
                                     </div>
                                </div>
                            </div>
                        </div>

                        <!-- AbuseIPDB -->
                        <div class="glass-panel rounded-xl p-6 border-t-2 border-t-amber-500 relative flex flex-col h-full">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-xs font-bold text-amber-500 uppercase tracking-widest flex items-center gap-2">AbuseIPDB</h3>
                                <div class="flex flex-col items-end">
                                    <span class="text-xl font-black text-white" x-text="(result?.abuseipdb?.abuseConfidenceScore ?? 0) + '%'"></span>
                                    <span x-show="result?.abuseipdb?.isWhitelisted" class="text-[9px] px-1 rounded bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 uppercase font-bold tracking-wider">Whitelisted</span>
                                </div>
                            </div>
                            
                            <div class="space-y-3 font-mono text-xs flex-1">
                                <div class="flex justify-between border-b border-white/5 pb-2">
                                    <span class="text-slate-500">Reports</span>
                                    <span class="text-white" x-text="result?.abuseipdb?.totalReports ?? 0"></span>
                                </div>
                                <div class="flex justify-between border-b border-white/5 pb-2">
                                    <span class="text-slate-500">Users</span>
                                    <span class="text-white" x-text="result?.abuseipdb?.numDistinctUsers ?? 0"></span>
                                </div>
                                <div class="flex justify-between border-b border-white/5 pb-2">
                                    <span class="text-slate-500">Usage</span>
                                    <span class="text-amber-400 truncate max-w-[120px] text-right" x-text="result?.abuseipdb?.usageType ?? '-'"></span>
                                </div>
                                <div class="flex justify-between border-b border-white/5 pb-2">
                                    <span class="text-slate-500">Domain</span>
                                    <span class="text-slate-300 text-right break-all" x-text="result?.abuseipdb?.domain ?? '-'"></span>
                                </div>
                            </div>
                        </div>
                     </div>

                     <!-- ALIENVAULT & GREYNOISE -->
                     <div class="glass-panel rounded-xl p-4" x-show="result?.alienvault && result?.alienvault?.pulses?.length > 0">
                         <div class="flex items-center justify-between mb-3">
                             <h3 class="text-xs font-bold text-purple-400 uppercase tracking-widest">AlienVault OTX</h3>
                             <span class="px-2 py-0.5 rounded bg-purple-500/20 text-purple-300 text-[10px]" x-text="(result?.alienvault?.pulse_count ?? 0) + ' Pulses'"></span>
                         </div>
                         <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-48 overflow-y-auto custom-scrollbar">
                             <template x-for="pulse in result?.alienvault?.pulses">
                                 <div @click="viewPulse(pulse)" class="block p-3 rounded bg-slate-900/40 hover:bg-purple-900/20 border border-white/5 hover:border-purple-500/30 transition-all group cursor-pointer">
                                     <div class="text-[11px] font-bold text-purple-200 truncate group-hover:text-white" x-text="pulse.name"></div>
                                     <div class="mt-1 flex flex-wrap gap-1">
                                         <template x-for="tag in pulse.tags.slice(0, 3)">
                                             <span class="text-[9px] px-1 rounded bg-black/40 text-slate-400" x-text="tag"></span>
                                         </template>
                                     </div>
                                     <div class="mt-2 text-[9px] text-slate-500 line-clamp-1" x-text="pulse.description || 'No description'"></div>
                                 </div>
                             </template>
                         </div>
                     </div>

                </div>
             </div>

             <!-- ABUSE REPORTS (Widened) -->
             <div class="glass-panel rounded-xl p-6" x-show="result?.abuseipdb?.reports && result?.abuseipdb?.reports.length > 0">
                 <div class="flex items-center justify-between mb-4">
                     <h3 class="text-xs font-bold text-amber-500 uppercase tracking-widest flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Abuse Reports
                     </h3>
                     <span class="text-[10px] text-slate-500">Recent User Reports</span>
                 </div>
                 <div class="overflow-x-auto min-h-[300px]">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="text-[10px] text-slate-500 uppercase border-b border-white/5">
                            <tr>
                                <th class="p-2 w-32">Date</th>
                                <th class="p-2 w-48">Categories</th>
                                <th class="p-2">Comment</th>
                            </tr>
                        </thead>
                        <tbody class="text-slate-300 divide-y divide-white/5">
                            <template x-for="report in paginatedReports" :key="report.reportedAt + Math.random()">
                                <tr class="hover:bg-white/5 transition-colors">
                                    <td class="p-2 font-mono text-[11px]" x-text="new Date(report.reportedAt).toLocaleDateString() + ' ' + new Date(report.reportedAt).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})"></td>
                                    <td class="p-2">
                                        <div class="flex gap-1 flex-wrap">
                                            <template x-for="cat in report.categories">
                                                <span class="px-1.5 py-0.5 rounded bg-amber-900/20 text-amber-500 text-[10px] border border-amber-500/20 whitespace-nowrap" x-text="getCategoryName(cat)"></span>
                                            </template>
                                        </div>
                                    </td>
                                    <td class="p-2 italic opacity-80 break-words" x-text="report.comment || 'No comment provided.'"></td>
                                </tr>
                            </template>
                             <tr x-show="paginatedReports.length === 0">
                                <td colspan="3" class="p-4 text-center text-slate-500 italic">No reports available.</td>
                            </tr>
                        </tbody>
                    </table>
                 </div>

                 <!-- Pagination Controls -->
                 <div class="mt-4 flex justify-between items-center border-t border-white/5 pt-4" x-show="totalPages > 1">
                     <button @click="prevPage" :disabled="currentPage === 1" class="px-3 py-1 text-xs rounded border border-white/10 hover:bg-white/5 disabled:opacity-50 disabled:cursor-not-allowed transition-colors text-slate-400">Previous</button>
                     <div class="flex gap-1">
                         <template x-for="page in totalPages">
                             <button @click="goToPage(page)" 
                                     class="w-6 h-6 text-[10px] rounded flex items-center justify-center transition-colors border"
                                     :class="currentPage === page ? 'bg-amber-600 border-amber-500 text-white' : 'border-transparent text-slate-500 hover:bg-white/5'"
                                     x-text="page"
                                     x-show="page === 1 || page === totalPages || (page >= currentPage - 1 && page <= currentPage + 1)"></button>
                         </template>
                     </div>
                     <button @click="nextPage" :disabled="currentPage === totalPages" class="px-3 py-1 text-xs rounded border border-white/10 hover:bg-white/5 disabled:opacity-50 disabled:cursor-not-allowed transition-colors text-slate-400">Next</button>
                 </div>
             </div>

             <!-- RAW JSON -->
            <div x-show="showRaw" x-collapse class="bg-black p-4 rounded-xl font-mono text-[10px] text-emerald-500 overflow-x-auto border border-slate-800">
                <pre x-text="JSON.stringify(result, null, 2)"></pre>
            </div>
            
        </div>

    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('ipAnalyzer', () => ({
            ip: '',
            loading: false,
            result: null,
            error: false,
            errorMessage: '',
            showRaw: false, // Ensure this is initialized false
            history: [],
            
            // Modal for AlienVault Pulse
            showPulseModal: false,
            selectedPulse: null,

            // AbuseIPDB Category Map
            categoryMap: {
                3: 'Fraud Orders', 4: 'DDoS Attack', 9: 'Open Proxy', 10: 'Web Spam',
                11: 'Email Spam', 14: 'Port Scan', 15: 'Hacking', 18: 'Brute-Force',
                19: 'Bad Web Bot', 20: 'Exploited Host', 21: 'Web App Attack', 
                22: 'SSH', 23: 'IoT Targeted'
            },

            // Pagination State
            currentPage: 1,
            pageSize: 5,

            init() {
                this.fetchHistory();
            },

            // Computed property for paginated reports
            get paginatedReports() {
                if (!this.result?.abuseipdb?.reports) return [];
                const start = (this.currentPage - 1) * this.pageSize;
                const end = start + this.pageSize;
                return this.result.abuseipdb.reports.slice(start, end);
            },

            get totalPages() {
                if (!this.result?.abuseipdb?.reports) return 0;
                return Math.ceil(this.result.abuseipdb.reports.length / this.pageSize);
            },

            nextPage() {
                if (this.currentPage < this.totalPages) this.currentPage++;
            },

            prevPage() {
                if (this.currentPage > 1) this.currentPage--;
            },
            
            goToPage(page) {
                 this.currentPage = page;
            },

            fetchHistory() {
                fetch('{{ route("investigation.ip-analyzer.history") }}')
                    .then(res => res.json())
                    .then(data => this.history = data)
                    .catch(err => console.error(err));
            },

            loadFromHistory(item) {
                this.currentPage = 1; // Reset pagination
                this.ip = item.ip_address;
                this.result = {
                    ip: item.ip_address,
                    risk_score: item.risk_score,
                    geo: item.geo_data,
                    virustotal: item.virustotal_data,
                    abuseipdb: item.abuseipdb_data,
                    greynoise: item.greynoise_data,
                    alienvault: item.alienvault_data
                };
            },

             getCategoryName(id) {
                return this.categoryMap[id] || 'Cat ' + id;
            },

            viewPulse(pulse) {
                this.selectedPulse = pulse;
                this.showPulseModal = true;
            },
            
            closePulseModal() {
                this.showPulseModal = false;
                this.selectedPulse = null;
            },

            exportReport() {
                if (!this.result) return;
                
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("investigation.ip-analyzer.export") }}';
                form.target = '_self';
                
                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';
                form.appendChild(csrf);

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'data';
                input.value = JSON.stringify(this.result);
                form.appendChild(input);

                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            },

            analyzeIp() {
                if (!this.ip) return;
                
                this.loading = true;
                this.error = false;
                this.result = null;
                this.showRaw = false; // Reset raw view
                this.currentPage = 1; // Reset pagination

                fetch('{{ route("investigation.ip-analyzer.analyze") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ ip: this.ip })
                })
                .then(res => res.json())
                .then(data => {
                    this.loading = false;
                    if (data.result) {
                        this.result = data.result;
                        this.fetchHistory(); 
                    } else {
                        this.error = true;
                        this.errorMessage = 'Failed to analyze IP. No data returned.';
                    }
                })
                .catch(err => {
                    this.loading = false;
                    this.error = true;
                    this.errorMessage = err.message || 'Connection failed.';
                });
            }
        }));
    });
</script>
@endsection
