@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen text-slate-300 font-sans" x-data="appInventory()" x-init="fetchApps(); setInterval(() => fetchApps(), 5000)">
    
    <!-- COMMAND HEADER -->
    <div class="bg-black border-b border-slate-800 p-6 mb-8 relative overflow-hidden group">
        <!-- Animated Background Grid -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.02)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.02)_1px,transparent_1px)] bg-[size:40px_40px] opacity-20 pointer-events-none"></div>
        <div class="absolute top-0 right-0 w-96 h-96 bg-cyan-900/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-end gap-6">
            <div>
                 <div class="flex items-center gap-3 mb-2">
                    <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse shadow-[0_0_10px_#22d3ee]"></span>
                    <span class="text-xs font-mono text-cyan-400 tracking-widest uppercase">Registry Online</span>
                </div>
                <h1 class="text-4xl font-black text-white tracking-tighter uppercase flex items-center gap-3">
                    <svg class="w-8 h-8 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                    Software Command
                </h1>
                <p class="text-slate-500 text-sm mt-1 max-w-xl">Centralized registry and state management for enterprise application assets.</p>
            </div>

            <div class="flex items-center gap-4">
                 <a href="{{ route('assets.application.create') }}" class="px-5 py-3 bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-sm tracking-wide rounded-sm shadow-[0_0_20px_rgba(8,145,178,0.3)] transition-all flex items-center gap-2 group/btn">
                    <svg class="w-4 h-4 group-hover/btn:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    REGISTER MODULE
                </a>
            </div>
        </div>

        <!-- AGGREGATE METRICS TICKER -->
        <div class="mt-8 grid grid-cols-2 md:grid-cols-4 gap-4 border-t border-slate-800 pt-6">
            <div class="bg-slate-900/50 p-3 border-l-2 border-cyan-500">
                <p class="text-[10px] text-slate-500 uppercase tracking-widest font-mono mb-1">Total Modules</p>
                <p class="text-2xl font-black text-white" x-text="apps.length">0</p>
            </div>
             <div class="bg-slate-900/50 p-3 border-l-2 border-emerald-500">
                <p class="text-[10px] text-slate-500 uppercase tracking-widest font-mono mb-1">Active Status</p>
                <p class="text-2xl font-black text-emerald-400" x-text="activeCount">0</p>
            </div>
            <div class="bg-slate-900/50 p-3 border-l-2 border-blue-500">
                <p class="text-[10px] text-slate-500 uppercase tracking-widest font-mono mb-1">Dev Builds</p>
                <p class="text-2xl font-black text-blue-400" x-text="devCount">0</p>
            </div>
            <div class="bg-slate-900/50 p-3 border-l-2 border-red-500">
                <p class="text-[10px] text-slate-500 uppercase tracking-widest font-mono mb-1">Critical Tier</p>
                <p class="text-2xl font-black text-red-500" x-text="criticalCount">0</p>
            </div>
        </div>
    </div>

    <!-- MODULE GRID -->
    <div class="px-6 pb-20">
        <div x-show="loading" class="flex justify-center py-20">
            <div class="flex flex-col items-center gap-4">
                <div class="w-12 h-12 border-4 border-cyan-500/30 border-t-cyan-500 rounded-full animate-spin"></div>
                <span class="text-xs font-mono text-cyan-400 animate-pulse">Scanning Registry...</span>
            </div>
        </div>

        <div x-show="!loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" x-cloak>
            <template x-for="app in apps" :key="app.id">
                <div @click="openDetail(app)" class="bg-[#0f0f0f] border border-slate-800 hover:border-cyan-500/50 rounded-sm p-5 relative group cursor-pointer transition-all hover:translate-y-[-2px] hover:shadow-xl overflow-hidden">
                    
                    <!-- Status Indicator Line -->
                    <div class="absolute top-0 left-0 w-full h-1 transition-colors" 
                         :class="app.criticality === 'Critical' ? 'bg-red-500' : (app.status === 'Active' ? 'bg-cyan-500' : 'bg-slate-700')"></div>
                    
                    <!-- Card Header -->
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center gap-3">
                             <!-- Type Icon Box -->
                            <div class="w-10 h-10 bg-slate-900 border border-slate-700 flex items-center justify-center text-xl rounded-sm">
                                <span x-show="app.type.toLowerCase().includes('web')">🌐</span>
                                <span x-show="app.type.toLowerCase().includes('db') || app.type.toLowerCase().includes('data')">🛢️</span>
                                <span x-show="app.type.toLowerCase().includes('api')">🔌</span>
                                <span x-show="!app.type.toLowerCase().match(/web|db|data|api/)">📦</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-white text-sm tracking-wide" x-text="app.name"></h3>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="w-1.5 h-1.5 rounded-full" 
                                          :class="{
                                            'bg-emerald-500': app.status === 'Active',
                                            'bg-blue-500': app.status === 'Development',
                                            'bg-yellow-500': app.status === 'Warning',
                                            'bg-red-500': app.status === 'Offline'
                                          }"></span>
                                    <span class="text-[10px] font-mono uppercase" 
                                          :class="{
                                            'text-emerald-500': app.status === 'Active',
                                            'text-blue-500': app.status === 'Development',
                                            'text-yellow-500': app.status === 'Warning',
                                            'text-red-500': app.status === 'Offline'
                                          }"
                                          x-text="app.status"></span>
                                </div>
                            </div>
                        </div>
                        <span class="text-[9px] font-mono text-slate-600 border border-slate-800 px-1.5 py-0.5 rounded" x-text="app.version || 'v?'"></span>
                    </div>

                    <!-- Mini Telemetry (Simulated Health) -->
                    <div class="space-y-3 mb-4">
                        <!-- Security Score -->
                        <div>
                            <div class="flex justify-between text-[9px] font-mono text-slate-500 mb-1">
                                <span>SEC_COMPLIANCE</span>
                                <span class="text-emerald-400">PASS</span>
                            </div>
                            <div class="h-1 w-full bg-slate-900 rounded-full overflow-hidden">
                                <div class="h-full bg-emerald-500 w-[95%]"></div>
                            </div>
                        </div>
                        <!-- Availability -->
                        <div>
                           <div class="flex justify-between text-[9px] font-mono text-slate-500 mb-1">
                                <span>AVAILABILITY</span>
                                <span class="text-cyan-400">99.9%</span>
                            </div>
                            <div class="h-1 w-full bg-slate-900 rounded-full overflow-hidden">
                                <div class="h-full bg-cyan-500 w-[99%]"></div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center text-[10px] text-slate-600 font-mono border-t border-slate-800 pt-3 mt-auto">
                        <span>OWNER: <span class="text-slate-400 truncate max-w-[80px]" x-text="app.owner || 'Unassigned'"></span></span>
                        <span>Tier: <span :class="app.criticality === 'Critical' ? 'text-red-500 font-bold' : 'text-slate-400'" x-text="app.criticality"></span></span>
                    </div>
                    
                    <!-- Hover Actions -->
                    <div class="absolute inset-0 bg-black/80 flex items-center justify-center gap-4 opacity-0 group-hover:opacity-100 transition-opacity backdrop-blur-sm">
                        <button class="px-4 py-2 border border-cyan-500 text-cyan-400 font-mono text-xs hover:bg-cyan-500 hover:text-white transition-colors">
                            > ACCESS_CONSOLE
                        </button>
                    </div>

                </div>
            </template>
            
            <!-- Add New Placeholder -->
            <a href="{{ route('assets.application.create') }}" class="border border-dashed border-slate-800 hover:border-slate-600 rounded-sm p-5 flex flex-col items-center justify-center gap-3 group transition-colors min-h-[200px] bg-[#0a0a0a]">
                <div class="w-12 h-12 rounded-full bg-slate-900 flex items-center justify-center text-slate-600 group-hover:text-cyan-400 group-hover:bg-cyan-900/20 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </div>
                <span class="text-xs font-mono text-slate-500 group-hover:text-slate-300 uppercase">Register Module</span>
            </a>
        </div>
    </div>

    <!-- TERMINAL MODAL (Slide-over) -->
    <div x-show="showDetailModal" class="fixed inset-0 z-50 flex justify-end" style="display: none;" x-cloak>
         <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm transition-opacity" @click="showDetailModal = false" x-transition.opacity></div>

        <!-- Panel -->
        <div class="relative w-full max-w-2xl bg-[#0a0a0a] border-l border-cyan-900/30 shadow-2xl h-full overflow-y-auto flex flex-col" x-transition:enter="slide-in-right">
             
             <!-- Terminal Header -->
             <div class="bg-[#111] px-6 py-4 border-b border-slate-800 flex justify-between items-center sticky top-0 z-10">
                 <div class="flex items-center gap-3">
                     <div class="flex gap-2">
                        <span class="w-3 h-3 rounded-full bg-red-500/20 border border-red-500"></span>
                        <span class="w-3 h-3 rounded-full bg-yellow-500/20 border border-yellow-500"></span>
                        <span class="w-3 h-3 rounded-full bg-emerald-500/20 border border-emerald-500"></span>
                     </div>
                     <span class="text-xs font-mono text-slate-400 ml-2">sysadmin@<span class="text-cyan-400" x-text="selectedApp?.name.toLowerCase().replace(/\s+/g, '-')">module</span>:~#</span>
                 </div>
                 <button @click="showDetailModal = false" class="text-slate-500 hover:text-white transition-colors">
                    [CLOSE_SESSION]
                 </button>
             </div>

             <!-- Terminal Content -->
             <div class="p-8 font-mono text-sm space-y-8 flex-1" x-show="selectedApp">
                 
                 <!-- Identity Block -->
                 <div>
                     <p class="text-[10px] text-slate-600 uppercase tracking-widest mb-3"># MODULE_MANIFEST</p>
                     <div class="bg-[#050505] border border-slate-800 p-4 grid grid-cols-2 gap-4">
                         <div>
                             <span class="block text-slate-500 text-xs">MODULE_NAME</span>
                             <span class="text-cyan-400" x-text="selectedApp?.name"></span>
                         </div>
                         <div>
                             <span class="block text-slate-500 text-xs">VENDOR</span>
                             <span class="text-slate-300" x-text="selectedApp?.vendor"></span>
                         </div>
                         <div>
                             <span class="block text-slate-500 text-xs">VERSION_BUILD</span>
                             <span class="text-slate-300" x-text="selectedApp?.version"></span>
                         </div>
                         <div>
                             <span class="block text-slate-500 text-xs">OWNER_REF</span>
                             <span class="text-slate-300" x-text="selectedApp?.owner"></span>
                         </div>
                     </div>
                 </div>

                 <!-- Status Block -->
                 <div>
                     <p class="text-[10px] text-slate-600 uppercase tracking-widest mb-3"># OPERATIONAL_STATE</p>
                     <div class="bg-[#050505] border border-slate-800 p-4 space-y-4">
                        <div class="flex items-center gap-4">
                            <div class="w-24 text-xs text-slate-500">CRITICALITY</div>
                            <div class="flex-1">
                                <span class="px-2 py-1 bg-slate-900 border text-xs"
                                      :class="{
                                        'border-red-500 text-red-500': selectedApp?.criticality === 'Critical',
                                        'border-orange-500 text-orange-500': selectedApp?.criticality === 'High',
                                        'border-blue-500 text-blue-500': selectedApp?.criticality === 'Medium',
                                        'border-slate-500 text-slate-500': selectedApp?.criticality === 'Low'
                                      }"
                                      x-text="selectedApp?.criticality"></span>
                            </div>
                        </div>
                         <div class="flex items-center gap-4">
                            <div class="w-24 text-xs text-slate-500">CTI_MONITOR</div>
                            <div class="flex-1">
                                 <span class="px-2 py-1 bg-slate-900 border text-xs flex items-center gap-2 w-fit"
                                      :class="selectedApp?.cti_enabled ? 'border-purple-500 text-purple-400' : 'border-slate-700 text-slate-600'">
                                      <span class="w-2 h-2 rounded-full" :class="selectedApp?.cti_enabled ? 'bg-purple-500 animate-pulse' : 'bg-slate-600'"></span>
                                      <span x-text="selectedApp?.cti_enabled ? 'ACTIVE' : 'DISABLED'"></span>
                                </span>
                            </div>
                        </div>
                     </div>
                 </div>

                 <!-- Simulated Logs -->
                 <div>
                      <p class="text-[10px] text-slate-600 uppercase tracking-widest mb-3"># SYSTEM_LOGS (LAST 5 EVENTS)</p>
                      <div class="bg-[#050505] border border-slate-800 p-4 text-xs text-slate-400 font-mono space-y-2">
                          <div class="flex gap-4">
                              <span class="text-slate-600">[2023-10-27 10:00:01]</span>
                              <span>System health check initiated... <span class="text-emerald-500">OK</span></span>
                          </div>
                          <div class="flex gap-4">
                              <span class="text-slate-600">[2023-10-27 10:05:22]</span>
                              <span>CTI Scraper job completed. 0 threats found.</span>
                          </div>
                          <div class="flex gap-4">
                              <span class="text-slate-600">[2023-10-27 10:15:00]</span>
                              <span>Performance metrics synced.</span>
                          </div>
                      </div>
                 </div>

             </div>

             <!-- Admin Actions Footer -->
             <div class="p-6 border-t border-slate-800 bg-[#080808] flex justify-end gap-3 sticky bottom-0">
                 <a :href="'/assets/application/' + selectedApp?.id + '/edit'" class="px-4 py-2 bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-bold transition-colors shadow-lg shadow-cyan-500/20">
                    CONFIGURE_MODULE >
                </a>
             </div>
        </div>
    </div>
</div>

<script>
    function appInventory() {
        return {
            apps: [], 
            loading: true,
            selectedApp: null,
            showDetailModal: false,
            
            async fetchApps() {
                try {
                    const response = await fetch('{{ route('assets.application.json') }}');
                    if (!response.ok) {
                        const text = await response.text();
                        console.error('API Error:', text);
                        throw new Error('Network response was not ok');
                    }
                    this.apps = await response.json();
                } catch (error) {
                    console.error('Error fetching apps:', error);
                } finally {
                    this.loading = false;
                }
            },
            
            openDetail(app) {
                this.selectedApp = app;
                this.showDetailModal = true;
            },

            // Computed Stats
            get activeCount() {
                return this.apps.filter(x => x.status === 'Active' || x.status === 'Stable').length;
            },
            get devCount() {
                return this.apps.filter(x => x.status === 'Development').length;
            },
            get criticalCount() {
                 return this.apps.filter(x => x.criticality === 'Critical').length;
            }
        }
    }
</script>

<style>
    [x-cloak] { display: none !important; }
    
    @keyframes slide-in-right {
        from { transform: translateX(100%); }
        to { transform: translateX(0); }
    }
</style>
@endsection
