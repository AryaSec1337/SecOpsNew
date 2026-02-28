@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen text-slate-300 font-sans" x-data="serverMonitor()" x-init="fetchServers(); setInterval(() => fetchServers(), 5000)">
    
    <!-- COMMAND HEADER -->
    <div class="bg-black border-b border-slate-800 p-6 mb-8 relative overflow-hidden group">
        <!-- Animated Background Grid -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.02)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.02)_1px,transparent_1px)] bg-[size:40px_40px] opacity-20 pointer-events-none"></div>
        <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-900/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-end gap-6">
            <div>
                 <div class="flex items-center gap-3 mb-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_10px_#10b981]"></span>
                    <span class="text-xs font-mono text-emerald-500 tracking-widest uppercase">System Online</span>
                </div>
                <h1 class="text-4xl font-black text-white tracking-tighter uppercase flex items-center gap-3">
                    <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    Infrastructure Command
                </h1>
                <p class="text-slate-500 text-sm mt-1 max-w-xl">Real-time telemetry and control dashboard for distributed server assets.</p>
            </div>

            <div class="flex items-center gap-4">
                 <a href="{{ route('assets.server.create') }}" class="px-5 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm tracking-wide rounded-sm shadow-[0_0_20px_rgba(79,70,229,0.3)] transition-all flex items-center gap-2 group/btn">
                    <svg class="w-4 h-4 group-hover/btn:animate-ping" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    DEPLOY NODE
                </a>
            </div>
        </div>

        <!-- AGGREGATE METRICS TICKER -->
        <div class="mt-8 grid grid-cols-2 md:grid-cols-4 gap-4 border-t border-slate-800 pt-6">
            <div class="bg-slate-900/50 p-3 border-l-2 border-indigo-500">
                <p class="text-[10px] text-slate-500 uppercase tracking-widest font-mono mb-1">Active Nodes</p>
                <p class="text-2xl font-black text-white" x-text="onlineCount + ' / ' + servers.length">0 / 0</p>
            </div>
             <div class="bg-slate-900/50 p-3 border-l-2 border-cyan-500">
                <p class="text-[10px] text-slate-500 uppercase tracking-widest font-mono mb-1">Avg CPU Load</p>
                <div class="flex items-end gap-2">
                    <p class="text-2xl font-black text-cyan-400" x-text="avgCpu + '%'">0%</p>
                    <div class="h-1 w-12 bg-slate-800 rounded-full mb-2 overflow-hidden">
                        <div class="h-full bg-cyan-500 transition-all duration-1000" :style="'width: ' + avgCpu + '%'"></div>
                    </div>
                </div>
            </div>
            <div class="bg-slate-900/50 p-3 border-l-2 border-purple-500">
                <p class="text-[10px] text-slate-500 uppercase tracking-widest font-mono mb-1">Avg RAM Usage</p>
                 <div class="flex items-end gap-2">
                    <p class="text-2xl font-black text-purple-400" x-text="avgRam + '%'">0%</p>
                    <div class="h-1 w-12 bg-slate-800 rounded-full mb-2 overflow-hidden">
                        <div class="h-full bg-purple-500 transition-all duration-1000" :style="'width: ' + avgRam + '%'"></div>
                    </div>
                </div>
            </div>
            <div class="bg-slate-900/50 p-3 border-l-2 border-red-500">
                <p class="text-[10px] text-slate-500 uppercase tracking-widest font-mono mb-1">Critical Alerts</p>
                <p class="text-2xl font-black text-red-500" x-text="criticalCount">0</p>
            </div>
        </div>
    </div>

    <!-- SERVER RACK GRID -->
    <div class="px-6 pb-20">
        <div x-show="loading" class="flex justify-center py-20">
            <div class="flex flex-col items-center gap-4">
                <div class="w-12 h-12 border-4 border-indigo-500/30 border-t-indigo-500 rounded-full animate-spin"></div>
                <span class="text-xs font-mono text-indigo-400 animate-pulse">ESTABLISHING LINK...</span>
            </div>
        </div>

        <div x-show="!loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" x-cloak>
            <template x-for="server in servers" :key="server.id">
                <div @click="openDetail(server)" class="bg-[#0f0f0f] border border-slate-800 hover:border-indigo-500/50 rounded-sm p-5 relative group cursor-pointer transition-all hover:translate-y-[-2px] hover:shadow-xl overflow-hidden">
                    
                    <!-- Status Indicator Line -->
                    <div class="absolute top-0 left-0 w-full h-1 transition-colors" :class="server.is_online ? 'bg-emerald-500' : 'bg-red-600'"></div>
                    
                    <!-- Card Header -->
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center gap-3">
                             <!-- OS Icon Box -->
                            <div class="w-10 h-10 bg-slate-900 border border-slate-700 flex items-center justify-center text-xl rounded-sm">
                                <span x-show="getOsIcon(server.os).includes('linux')">🐧</span>
                                <span x-show="getOsIcon(server.os).includes('windows')">🪟</span>
                                <svg x-show="!getOsIcon(server.os).includes('windows') && !getOsIcon(server.os).includes('linux')" class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-white text-sm tracking-wide" x-text="server.hostname"></h3>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="server.is_online ? 'bg-emerald-500 animate-pulse' : 'bg-red-500'"></span>
                                    <span class="text-[10px] font-mono" :class="server.is_online ? 'text-emerald-500' : 'text-red-500'" x-text="server.is_online ? 'ONLINE' : 'OFFLINE'"></span>
                                </div>
                            </div>
                        </div>
                        <span class="text-[9px] font-mono text-slate-600 border border-slate-800 px-1.5 py-0.5 rounded" x-text="server.ip_address"></span>
                    </div>

                    <!-- Mini Telemetry (Sparkline Sim) -->
                    <div class="space-y-3 mb-4">
                        <!-- CPU -->
                        <div>
                            <div class="flex justify-between text-[9px] font-mono text-slate-500 mb-1">
                                <span>CPU_LOAD</span>
                                <span :class="(server.metadata?.cpu_percent || 0) > 80 ? 'text-red-500' : 'text-slate-300'" x-text="(server.metadata?.cpu_percent || 0) + '%'"></span>
                            </div>
                            <div class="h-1 w-full bg-slate-900 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500" 
                                    :class="(server.metadata?.cpu_percent || 0) > 80 ? 'bg-red-500' : 'bg-indigo-500'"
                                    :style="`width: ${server.metadata?.cpu_percent || 0}%`"></div>
                            </div>
                        </div>
                        <!-- RAM -->
                        <div>
                           <div class="flex justify-between text-[9px] font-mono text-slate-500 mb-1">
                                <span>MEM_USAGE</span>
                                <span :class="(server.metadata?.memory_percent || 0) > 80 ? 'text-red-500' : 'text-slate-300'" x-text="(server.metadata?.memory_percent || 0) + '%'"></span>
                            </div>
                            <div class="h-1 w-full bg-slate-900 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500" 
                                    :class="(server.metadata?.memory_percent || 0) > 80 ? 'bg-red-500' : 'bg-purple-500'"
                                    :style="`width: ${server.metadata?.memory_percent || 0}%`"></div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center text-[10px] text-slate-600 font-mono border-t border-slate-800 pt-3 mt-auto">
                        <span>ROLE: <span class="text-slate-400" x-text="server.role || 'Workstation'"></span></span>
                        <span>seen: <span x-text="server.last_audit"></span></span>
                    </div>
                    
                    <!-- Hover Actions -->
                    <div class="absolute inset-0 bg-black/80 flex items-center justify-center gap-4 opacity-0 group-hover:opacity-100 transition-opacity backdrop-blur-sm">
                        <button class="px-4 py-2 border border-indigo-500 text-indigo-400 font-mono text-xs hover:bg-indigo-500 hover:text-white transition-colors">
                            > ACCESS_TERMINAL
                        </button>
                    </div>

                </div>
            </template>
            
            <!-- Add New Placeholder -->
            <a href="{{ route('assets.server.create') }}" class="border border-dashed border-slate-800 hover:border-slate-600 rounded-sm p-5 flex flex-col items-center justify-center gap-3 group transition-colors min-h-[200px]">
                <div class="w-12 h-12 rounded-full bg-slate-900 flex items-center justify-center text-slate-600 group-hover:text-indigo-400 group-hover:bg-indigo-900/20 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </div>
                <span class="text-xs font-mono text-slate-500 group-hover:text-slate-300 uppercase">Deploy New Node</span>
            </a>
        </div>
    </div>

    <!-- TERMINAL MODAL (Slide-over) -->
    <div x-show="showDetailModal" class="fixed inset-0 z-50 flex justify-end" style="display: none;" x-cloak>
         <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm transition-opacity" @click="showDetailModal = false" x-transition.opacity></div>

        <!-- Panel -->
        <div class="relative w-full max-w-2xl bg-[#0a0a0a] border-l border-indigo-900/30 shadow-2xl h-full overflow-y-auto flex flex-col" x-transition:enter="slide-in-right">
             
             <!-- Terminal Header -->
             <div class="bg-[#111] px-6 py-4 border-b border-slate-800 flex justify-between items-center sticky top-0 z-10">
                 <div class="flex items-center gap-3">
                     <div class="flex gap-2">
                        <span class="w-3 h-3 rounded-full bg-red-500/20 border border-red-500"></span>
                        <span class="w-3 h-3 rounded-full bg-yellow-500/20 border border-yellow-500"></span>
                        <span class="w-3 h-3 rounded-full bg-emerald-500/20 border border-emerald-500"></span>
                     </div>
                     <span class="text-xs font-mono text-slate-400 ml-2">root@<span class="text-indigo-400" x-text="selectedServer?.hostname">localhost</span>:~#</span>
                 </div>
                 <button @click="showDetailModal = false" class="text-slate-500 hover:text-white transition-colors">
                    [CLOSE_SESSION]
                 </button>
             </div>

             <!-- Terminal Content -->
             <div class="p-8 font-mono text-sm space-y-8 flex-1" x-show="selectedServer">
                 
                 <!-- Identity Block -->
                 <div>
                     <p class="text-[10px] text-slate-600 uppercase tracking-widest mb-3"># SYSTEM_IDENTITY</p>
                     <div class="bg-[#050505] border border-slate-800 p-4 grid grid-cols-2 gap-4">
                         <div>
                             <span class="block text-slate-500 text-xs">HOSTNAME</span>
                             <span class="text-indigo-400" x-text="selectedServer?.hostname"></span>
                         </div>
                         <div>
                             <span class="block text-slate-500 text-xs">IP_ADDR</span>
                             <span class="text-indigo-400" x-text="selectedServer?.ip_address"></span>
                         </div>
                         <div>
                             <span class="block text-slate-500 text-xs">OS_DISTRO</span>
                             <span class="text-slate-300" x-text="selectedServer?.os"></span>
                         </div>
                         <div>
                             <span class="block text-slate-500 text-xs">UPTIME_SINCE</span>
                             <span class="text-slate-300" x-text="selectedServer?.last_audit"></span>
                         </div>
                     </div>
                 </div>

                 <!-- Deployment Command Block -->
                 <div>
                     <p class="text-[10px] text-slate-600 uppercase tracking-widest mb-3"># AGENT_DEPLOYMENT_COMMAND</p>
                     <div class="bg-[#050505] border border-slate-800 p-4">
                        <div class="flex justify-between items-center mb-2">
                             <span class="text-xs text-slate-500">Run this on the remote server to install/update the agent:</span>
                             <button @click="navigator.clipboard.writeText(getInstallCommand(selectedServer))" class="text-xs text-indigo-400 hover:text-white transition-colors">[COPY_COMMAND]</button>
                        </div>
                        <div class="bg-black p-3 rounded border border-slate-800 font-mono text-xs text-emerald-400 break-all select-all">
                            <span x-text="getInstallCommand(selectedServer)"></span>
                        </div>
                        <p class="text-[10px] text-slate-600 mt-2">
                            Token: <span class="text-slate-400" x-text="selectedServer?.api_token || 'NO_TOKEN_GENERATED'"></span>
                        </p>
                     </div>
                 </div>

                 <!-- Resources ASCII/Graph -->
                 <div>
                     <p class="text-[10px] text-slate-600 uppercase tracking-widest mb-3"># RESOURCE_TELEMETRY</p>
                     <div class="bg-[#050505] border border-slate-800 p-4 space-y-4">
                        <!-- CPU -->
                        <div>
                            <div class="flex justify-between mb-1 text-xs">
                                <span>CPU0_LOAD</span>
                                <span x-text="(selectedServer?.metadata?.cpu_percent || 0) + '%'" :class="(selectedServer?.metadata?.cpu_percent || 0) > 80 ? 'text-red-500' : 'text-emerald-500'"></span>
                            </div>
                            <div class="w-full bg-slate-900 h-2">
                                <div class="h-full bg-emerald-500" :style="`width: ${selectedServer?.metadata?.cpu_percent || 0}%`"></div>
                            </div>
                        </div>
                        <!-- RAM -->
                        <div>
                            <div class="flex justify-between mb-1 text-xs">
                                <span>MEM_ALLOC</span>
                                <span class="text-xs text-slate-500" x-text="(selectedServer?.metadata?.memory_used || '?') + ' / ' + (selectedServer?.metadata?.memory_total || '?')"></span>
                            </div>
                            <div class="w-full bg-slate-900 h-2">
                                <div class="h-full bg-purple-500" :style="`width: ${selectedServer?.metadata?.memory_percent || 0}%`"></div>
                            </div>
                        </div>
                         <!-- DISK -->
                        <div>
                            <div class="flex justify-between mb-1 text-xs">
                                <span>DISK_MOUNT_ROOT</span>
                                <span class="text-xs text-slate-500" x-text="(selectedServer?.metadata?.disk_used || '?') + ' / ' + (selectedServer?.metadata?.disk_total || '?')"></span>
                            </div>
                            <div class="w-full bg-slate-900 h-2">
                                <div class="h-full bg-cyan-500" :style="`width: ${selectedServer?.metadata?.disk_percent || 0}%`"></div>
                            </div>
                        </div>
                     </div>
                 </div>

                 <!-- Simulated Process List -->
                 <div>
                      <p class="text-[10px] text-slate-600 uppercase tracking-widest mb-3"># TOP_PROCESSES</p>
                      <div class="bg-[#050505] border border-slate-800 p-4 text-xs text-slate-400">
                          <div class="grid grid-cols-4 border-b border-slate-800 pb-2 mb-2 text-slate-500">
                              <span>PID</span>
                              <span>USER</span>
                              <span>%CPU</span>
                              <span>COMMAND</span>
                          </div>
                          <!-- Fake Processes for aesthetics -->
                          <div class="grid grid-cols-4 py-1 hover:bg-white/5 cursor-pointer">
                              <span class="text-slate-500">1023</span>
                              <span>root</span>
                              <span x-text="(Math.random() * 5).toFixed(1) + '%'">0.0%</span>
                              <span class="text-emerald-500">systemd</span>
                          </div>
                           <div class="grid grid-cols-4 py-1 hover:bg-white/5 cursor-pointer">
                              <span class="text-slate-500">2204</span>
                              <span>www-data</span>
                              <span x-text="(Math.random() * 20).toFixed(1) + '%'">0.0%</span>
                              <span class="text-emerald-500">nginx: worker</span>
                          </div>
                           <div class="grid grid-cols-4 py-1 hover:bg-white/5 cursor-pointer">
                              <span class="text-slate-500">3392</span>
                              <span>mysql</span>
                              <span x-text="(Math.random() * 15).toFixed(1) + '%'">0.0%</span>
                              <span class="text-emerald-500">mysqld</span>
                          </div>
                           <div class="grid grid-cols-4 py-1 hover:bg-white/5 cursor-pointer">
                              <span class="text-slate-500">4001</span>
                              <span>root</span>
                              <span>0.1%</span>
                              <span class="text-emerald-500">sshd</span>
                          </div>
                      </div>
                 </div>

             </div>

             <!-- Admin Actions Footer -->
             <div class="p-6 border-t border-slate-800 bg-[#080808] flex justify-end gap-3 sticky bottom-0">
                 <form :action="'/assets/server/' + selectedServer?.id" method="POST" onsubmit="return confirm('WARNING: Are you sure you want to decommission this node?');">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="px-4 py-2 border border-red-900/50 text-red-500 hover:bg-red-900/20 text-xs font-bold transition-colors">
                        [DECOMMISSION_NODE]
                    </button>
                </form>
                <a :href="'/assets/server/' + selectedServer?.id + '/edit'" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold transition-colors shadow-lg shadow-indigo-500/20">
                    CONFIGURE_AGENT >
                </a>
             </div>
        </div>
    </div>
</div>

<script>
    function serverMonitor() {
        return {
            servers: [], 
            loading: true,
            selectedServer: null,
            showDetailModal: false,
            
            async fetchServers() {
                try {
                    const response = await fetch('{{ route('assets.server.json') }}');
                    if (!response.ok) {
                        const text = await response.text();
                        console.error('Server Error:', text);
                        throw new Error('Network response was not ok: ' + response.status);
                    }
                    this.servers = await response.json();
                } catch (error) {
                    console.error('Error fetching servers:', error);
                } finally {
                    this.loading = false;
                }
            },
            
            openDetail(server) {
                this.selectedServer = server;
                this.showDetailModal = true;
            },

            getOsIcon(osName) {
                if (!osName) return 'server';
                if (osName.toLowerCase().includes('windows')) return 'windows';
                if (osName.toLowerCase().includes('linux')) return 'linux';
                if (osName.toLowerCase().includes('ubuntu')) return 'ubuntu';
                return 'server';
            },

            getInstallCommand(server) {
                if (!server || !server.api_token) return 'Generating unique token...';
                const baseUrl = window.location.origin;
                // Command: curl -O url/agent.py && python3 agent.py --install ...
                return `curl -O ${baseUrl}/agent.py && python3 agent.py --install --token "${server.api_token}" --server "${baseUrl}" --name "${server.hostname}"`;
            },

            // Computed Properties
            get onlineCount() {
                return this.servers.filter(s => s.is_online).length;
            },
            get avgCpu() {
                if (!this.servers.length) return 0;
                const total = this.servers.reduce((acc, s) => acc + (s.metadata?.cpu_percent || 0), 0);
                return Math.round(total / this.servers.length);
            },
            get avgRam() {
                if (!this.servers.length) return 0;
                const total = this.servers.reduce((acc, s) => acc + (s.metadata?.memory_percent || 0), 0);
                return Math.round(total / this.servers.length);
            },
            get criticalCount() {
                return this.servers.filter(s => (s.metadata?.cpu_percent || 0) > 90 || !s.is_online).length;
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
