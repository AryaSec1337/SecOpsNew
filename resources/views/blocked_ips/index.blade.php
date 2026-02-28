@extends('layouts.dashboard')

@section('content')
<div x-data="blockedIpManager()" class="min-h-screen font-sans text-slate-300">

    <!-- Top Stats HUD -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Active Blocks -->
        <div class="relative overflow-hidden bg-slate-900/50 backdrop-blur-md rounded-xl border border-slate-700/50 p-4 group hover:border-red-500/50 transition-all duration-500">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-red-500/10 rounded-full blur-2xl group-hover:bg-red-500/20 transition-all"></div>
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <p class="text-xs font-bold text-red-500 uppercase tracking-widest mb-1">Active Blocks</p>
                    <h3 class="text-3xl font-black text-white font-mono tracking-tighter" x-text="stats.blocked">0</h3>
                </div>
                <div class="p-2 bg-red-500/10 rounded-lg border border-red-500/20 text-red-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
            </div>
             <div class="mt-4 w-full bg-slate-800 rounded-full h-1">
                <div class="bg-red-500 h-1 rounded-full shadow-[0_0_10px_red]" :style="`width: ${stats.total > 0 ? (stats.blocked / stats.total * 100) : 0}%`"></div>
            </div>
        </div>

        <!-- Pending Actions -->
        <div class="relative overflow-hidden bg-slate-900/50 backdrop-blur-md rounded-xl border border-slate-700/50 p-4 group hover:border-yellow-500/50 transition-all duration-500">
             <div class="absolute -right-6 -top-6 w-24 h-24 bg-yellow-500/10 rounded-full blur-2xl group-hover:bg-yellow-500/20 transition-all"></div>
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <p class="text-xs font-bold text-yellow-500 uppercase tracking-widest mb-1">Pending Actions</p>
                    <h3 class="text-3xl font-black text-white font-mono tracking-tighter" x-text="stats.pending">0</h3>
                </div>
                <div class="p-2 bg-yellow-500/10 rounded-lg border border-yellow-500/20 text-yellow-400">
                    <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Online Agents -->
        <div class="relative overflow-hidden bg-slate-900/50 backdrop-blur-md rounded-xl border border-slate-700/50 p-4 group hover:border-blue-500/50 transition-all duration-500">
             <div class="absolute -right-6 -top-6 w-24 h-24 bg-blue-500/10 rounded-full blur-2xl group-hover:bg-blue-500/20 transition-all"></div>
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <p class="text-xs font-bold text-blue-500 uppercase tracking-widest mb-1">Agents Online</p>
                    <h3 class="text-3xl font-black text-white font-mono tracking-tighter" x-text="stats.agents">0</h3>
                </div>
                <div class="p-2 bg-blue-500/10 rounded-lg border border-blue-500/20 text-blue-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <h2 class="text-2xl font-bold text-white flex items-center gap-2">
            <span class="w-2 h-8 bg-red-600 rounded-sm shadow-[0_0_15px_red]"></span>
            DEFENSE GRID
        </h2>
        
        <button @click="openModal()" class="group relative px-6 py-2 bg-red-600/20 border border-red-500/50 text-red-500 rounded-lg overflow-hidden hover:bg-red-600 hover:text-white transition-all duration-300">
            <div class="absolute inset-0 w-full h-full bg-red-600/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
            <span class="relative font-bold flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                NEUTRALIZE TARGET
            </span>
        </button>
    </div>

    <!-- The Grid (Table) -->
    <div class="bg-slate-900/80 backdrop-blur-xl rounded-2xl border border-slate-800 shadow-2xl overflow-hidden relative">
        <!-- Grid Background Pattern -->
        <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: radial-gradient(#334155 1px, transparent 1px); background-size: 20px 20px;"></div>

        <div class="overflow-x-auto relative z-10">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-950/80 text-xxs uppercase tracking-widest text-slate-500 font-bold font-mono">
                        <th class="p-4 border-b border-slate-800">Target IP</th>
                        <th class="p-4 border-b border-slate-800">Enforcing Agent</th>
                        <th class="p-4 border-b border-slate-800">Status</th>
                        <th class="p-4 border-b border-slate-800">Reason</th>
                        <th class="p-4 border-b border-slate-800 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="font-mono text-xs">
                    <template x-for="ip in ips" :key="ip.id">
                        <tr class="border-b border-slate-800 hover:bg-slate-800/50 transition-all duration-200 group">
                            <td class="p-4 text-white font-bold tracking-wider">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-slate-600 group-hover:text-red-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                                    <span x-text="ip.ip_address"></span>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="flex items-center gap-2 text-slate-400">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="ip.agent_status === 'Online' ? 'bg-green-500 shadow-[0_0_5px_lime]' : 'bg-slate-600'"></span>
                                    <span x-text="ip.agent_name"></span>
                                </span>
                            </td>
                            <td class="p-4">
                                <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded bg-slate-950 border border-slate-800 text-xxs font-bold uppercase tracking-wide"
                                    :class="{
                                        'text-red-500 border-red-900/50 shadow-[0_0_10px_rgba(239,68,68,0.2)]': ip.status === 'blocked',
                                        'text-yellow-500 border-yellow-900/50 animate-pulse': ip.status.includes('pending'),
                                        'text-slate-500': ip.status === 'unblocked'
                                    }">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="{
                                        'bg-red-500': ip.status === 'blocked',
                                        'bg-yellow-500': ip.status.includes('pending'),
                                        'bg-slate-500': ip.status === 'unblocked'
                                    }"></span>
                                    <span x-text="ip.status_label"></span>
                                </div>
                            </td>
                             <td class="p-4 text-slate-500 truncate max-w-[200px]" :title="ip.reason" x-text="ip.reason"></td>
                            <td class="p-4 text-right">
                                <template x-if="ip.status === 'blocked' || ip.status === 'pending_block'">
                                    <button @click="unblockIp(ip)" class="text-xs font-bold text-slate-500 hover:text-green-400 border border-slate-700 hover:border-green-500/50 px-3 py-1 rounded transition-all">
                                        UNLOCK
                                    </button>
                                </template>
                            </td>
                        </tr>
                    </template>
                     <template x-if="ips.length === 0">
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-500 italic">No restrictions active. Defense grid clear.</td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Neutralize Modal -->
    <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="fixed inset-0 bg-slate-950/90 backdrop-blur-sm" @click="closeModal()"></div>

        <div class="bg-slate-900 border border-red-900/30 w-full max-w-lg rounded-xl shadow-2xl relative overflow-hidden z-20">
            <!-- Header -->
            <div class="px-6 py-4 bg-red-950/20 border-b border-red-900/20 flex justify-between items-center">
                 <h3 class="text-lg font-bold text-red-500 flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    INITIATE BLOCK PROTOCOL
                 </h3>
                 <button @click="closeModal()" class="text-red-500/50 hover:text-red-500 transition-colors">
                     <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                 </button>
            </div>

            <!-- Form -->
            <form action="{{ route('blocked-ips.store') }}" method="POST" class="p-6 space-y-6">
                 @csrf
                 
                 <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Select Enforcer (Agent)</label>
                    <div class="relative">
                         <select name="agent_id" class="w-full bg-slate-950 border border-slate-700 text-white rounded-lg p-3 appearance-none focus:border-red-500 focus:ring-1 focus:ring-red-500 transition-all font-mono text-sm" required>
                             @foreach($servers as $server)
                                 <option value="{{ $server->id }}">{{ $server->name }} [{{ $server->ip_address }}]</option>
                             @endforeach
                         </select>
                         <div class="absolute right-3 top-3 text-slate-500 pointer-events-none">
                             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                         </div>
                    </div>
                 </div>

                 <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Target IP Address</label>
                    <div class="relative">
                        <span class="absolute left-3 top-3 text-slate-500">
                             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                        </span>
                        <input type="text" name="ip_address" class="w-full bg-slate-950 border border-slate-700 text-white pl-10 pr-4 py-3 rounded-lg focus:border-red-500 focus:ring-1 focus:ring-red-500 transition-all font-mono text-sm shadow-inner" placeholder="0.0.0.0" required>
                    </div>
                 </div>

                 <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Reason</label>
                    <textarea name="reason" rows="2" class="w-full bg-slate-950 border border-slate-700 text-white p-3 rounded-lg focus:border-red-500 focus:ring-1 focus:ring-red-500 transition-all text-sm resize-none" placeholder="Manual security override..." required></textarea>
                 </div>

                 <div class="pt-4 border-t border-red-900/20 flex justify-end gap-3">
                     <button type="button" @click="closeModal()" class="px-4 py-2 text-slate-500 hover:text-white transition-colors text-sm font-bold">CANCEL</button>
                     <button type="submit" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-bold shadow-lg shadow-red-900/20 transition-all flex items-center gap-2">
                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                         LOCK TARGET
                     </button>
                 </div>
            </form>
        </div>
    </div>

</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('blockedIpManager', () => ({
            ips: [],
            modalOpen: false,
            stats: { blocked: 0, pending: 0, agents: 0, total: 0 },

            init() {
                this.fetchData();
                setInterval(() => this.fetchData(), 2000);
            },

            async fetchData() {
                try {
                    const res = await fetch('{{ route("blocked-ips.list") }}');
                    const data = await res.json();
                    this.ips = data;
                    this.calculateStats(data);
                } catch (e) { console.error(e); }
            },

            calculateStats(data) {
                this.stats.blocked = data.filter(d => d.status === 'blocked').length;
                this.stats.pending = data.filter(d => d.status.includes('pending')).length;
                this.stats.total = data.length;
                
                // Agents (Unique count based on name for this demo, or just count Online rows)
                // Since this view is "Blocked IPs", counting unique agents from this list is approximated.
                // A better way is if the controller sent it, but we can infer from the rows currently displayed.
                const uniqueAgents = new Set(data.map(d => d.agent_name + d.agent_status));
                let online = 0;
                uniqueAgents.forEach(str => { if(str.includes('Online')) online++; });
                this.stats.agents = online;
            },

            openModal() { this.modalOpen = true; },
            closeModal() { this.modalOpen = false; },

            unblockIp(ip) {
                Swal.fire({
                    title: 'UNLOCK TARGET?',
                    text: `Restore access for ${ip.ip_address}?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#1e293b',
                    confirmButtonText: 'CONFIRM UNLOCK',
                    background: '#0f172a',
                    color: '#fff'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `/blocked-ips/${ip.id}`;
                        
                        const method = document.createElement('input');
                        method.type = 'hidden';
                        method.name = '_method';
                        method.value = 'DELETE';
                        form.appendChild(method);

                        const csrf = document.createElement('input');
                        csrf.type = 'hidden';
                        csrf.name = '_token';
                        csrf.value = '{{ csrf_token() }}';
                        form.appendChild(csrf);

                        document.body.appendChild(form);
                        form.submit();
                    }
                })
            }
        }));
    });
</script>
@endsection
