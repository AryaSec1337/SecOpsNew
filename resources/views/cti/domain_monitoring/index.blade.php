@extends('layouts.dashboard')

@section('content')
<script>
    window.monitoredDomains = {!! json_encode($domains->items()) !!};
</script>
<div x-data="domainMonitor(window.monitoredDomains)" class="min-h-screen font-sans text-slate-300">
    
    <!-- Top Stats HUD -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <!-- Total Targets -->
        <div class="relative overflow-hidden bg-slate-900/50 backdrop-blur-md rounded-xl border border-slate-700/50 p-4 group hover:border-indigo-500/50 transition-all duration-500">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-indigo-500/10 rounded-full blur-2xl group-hover:bg-indigo-500/20 transition-all"></div>
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <p class="text-xs font-bold text-indigo-400 uppercase tracking-widest mb-1">Total Targets</p>
                    <h3 class="text-3xl font-black text-white font-mono tracking-tighter">{{ $stats['total_domains'] }}</h3>
                </div>
                <div class="p-2 bg-indigo-500/10 rounded-lg border border-indigo-500/20 text-indigo-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                </div>
            </div>
        </div>

        <!-- Active Monitoring -->
        <div class="relative overflow-hidden bg-slate-900/50 backdrop-blur-md rounded-xl border border-slate-700/50 p-4 group hover:border-emerald-500/50 transition-all duration-500">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-emerald-500/10 rounded-full blur-2xl group-hover:bg-emerald-500/20 transition-all"></div>
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <p class="text-xs font-bold text-emerald-400 uppercase tracking-widest mb-1">Active Satellites</p>
                    <h3 class="text-3xl font-black text-white font-mono tracking-tighter">{{ $stats['monitored'] }}</h3>
                </div>
                <div class="p-2 bg-emerald-500/10 rounded-lg border border-emerald-500/20 text-emerald-400">
                    <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Avg Reputation -->
        <div class="relative overflow-hidden bg-slate-900/50 backdrop-blur-md rounded-xl border border-slate-700/50 p-4 group hover:border-cyan-500/50 transition-all duration-500">
             <div class="absolute -right-6 -top-6 w-24 h-24 bg-cyan-500/10 rounded-full blur-2xl group-hover:bg-cyan-500/20 transition-all"></div>
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <p class="text-xs font-bold text-cyan-400 uppercase tracking-widest mb-1">Global Health</p>
                    <h3 class="text-3xl font-black text-white font-mono tracking-tighter">{{ number_format($stats['avg_reputation'], 1) }}</h3>
                </div>
                <div class="p-2 bg-cyan-500/10 rounded-lg border border-cyan-500/20 text-cyan-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>
        
        <!-- Scans -->
        <div class="relative overflow-hidden bg-slate-900/50 backdrop-blur-md rounded-xl border border-slate-700/50 p-4 group hover:border-orange-500/50 transition-all duration-500">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-orange-500/10 rounded-full blur-2xl group-hover:bg-orange-500/20 transition-all"></div>
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <p class="text-xs font-bold text-orange-400 uppercase tracking-widest mb-1">Intel Cycles (24h)</p>
                    <h3 class="text-3xl font-black text-white font-mono tracking-tighter">{{ $stats['recent_scans'] }}</h3>
                </div>
                <div class="p-2 bg-orange-500/10 rounded-lg border border-orange-500/20 text-orange-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- SSL Health Chart -->
        <div class="bg-slate-900/50 backdrop-blur-md rounded-xl border border-slate-700/50 p-6 shadow-lg">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4 border-b border-slate-700 pb-2 flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                SSL Certificate Integrity
            </h3>
            <div id="sslChart" class="h-64"></div>
        </div>

        <!-- Typosquatting Stats -->
        <div class="bg-slate-900/50 backdrop-blur-md rounded-xl border border-slate-700/50 p-6 shadow-lg">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4 border-b border-slate-700 pb-2 flex items-center gap-2">
                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                Impersonation Threats
            </h3>
            <div id="typoChart" class="h-64 w-full"></div>
        </div>
        
        <!-- RANSOMWARE CHARTS (NEW) -->
        <!-- Ransomware Groups -->
        <div class="bg-slate-900/50 backdrop-blur-md rounded-xl border border-slate-700/50 p-6 shadow-lg">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4 border-b border-slate-700 pb-2 flex items-center gap-2">
                <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                Active Ransomware Groups
            </h3>
            <div id="ransomGroupChart" class="h-64"></div>
        </div>

        <!-- Recent Victims List -->
        <div class="bg-slate-900/50 backdrop-blur-md rounded-xl border border-slate-700/50 p-6 shadow-lg overflow-hidden">
             <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4 border-b border-slate-700 pb-2 flex items-center justify-between">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Recent Victims
                </span>
                <span class="text-xs text-red-400 bg-red-900/20 px-2 py-0.5 rounded border border-red-900/30">{{ $ransomwareStats['total'] }} TOTAL</span>
            </h3>
            <div class="overflow-y-auto h-64 pr-2 custom-scrollbar">
                <table class="w-full text-left">
                    <tbody class="text-xs text-slate-300">
                        @forelse($ransomwareStats['recent'] as $victim)
                        <tr class="border-b border-slate-800 last:border-0 hover:bg-white/5 transition-colors">
                            <td class="py-3">
                                <div class="font-bold text-white">{{ Str::limit($victim->post_title, 20) }}</div>
                                <div class="text-[10px] text-slate-500">{{ $victim->group_name }}</div>
                            </td>
                            <td class="py-3 text-right">
                                <span class="block text-orange-400 font-mono text-[10px]">{{ $victim->discovered_at ? $victim->discovered_at->format('d M') : 'N/A' }}</span>
                                <a href="{{ $victim->post_url }}" target="_blank" class="text-slate-500 hover:text-white text-[10px] underline">Source</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="py-8 text-center text-slate-500 italic">No ransomware victims detected recently.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Main Toolbar -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                <span class="w-2 h-8 bg-indigo-500 rounded-sm shadow-[0_0_15px_indigo]"></span>
                TARGET WATCHLIST
            </h2>
            <p class="text-xs text-slate-500 mt-1 ml-4 font-mono">LIVE INTELLIGENCE FEED FROM VIRUSTOTAL</p>
        </div>
        
        <button onclick="document.getElementById('addDomainModal').showModal()" class="group relative px-6 py-2 bg-indigo-600/20 border border-indigo-500/50 text-indigo-400 rounded-lg overflow-hidden hover:bg-indigo-600 hover:text-white transition-all duration-300">
            <div class="absolute inset-0 w-full h-full bg-indigo-600/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
            <span class="relative font-bold flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                NEW ASSIGNMENT
            </span>
        </button>
    </div>

    <!-- The Watchlist (Table) -->
    <div class="bg-slate-900/80 backdrop-blur-xl rounded-2xl border border-slate-800 shadow-2xl overflow-hidden relative">
        <!-- Grid Background -->
        <div class="absolute inset-0 opacity-5 pointer-events-none" style="background-image: linear-gradient(rgba(99, 102, 241, 0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(99, 102, 241, 0.1) 1px, transparent 1px); background-size: 20px 20px;"></div>

        <div class="overflow-x-auto relative z-10">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-950/80 text-xxs uppercase tracking-widest text-slate-500 font-bold font-mono">
                        <th class="p-4 border-b border-slate-800">Target Domain</th>
                        <th class="p-4 border-b border-slate-800">Status</th>
                        <th class="p-4 border-b border-slate-800">Last Intel</th>
                        <th class="p-4 border-b border-slate-800">Reputation</th>
                        <th class="p-4 border-b border-slate-800">Threat Ratio</th>
                        <th class="p-4 border-b border-slate-800 text-right">Protocol</th>
                    </tr>
                </thead>
                <tbody class="font-mono text-xs">
                    <template x-for="domain in domains" :key="domain.id">
                        <tr class="border-b border-slate-800 hover:bg-slate-800/50 transition-all duration-200 group">
                            <td class="p-4">
                                <a :href="`/cti/domains/${domain.id}`" class="text-white font-bold hover:text-indigo-400 transition-colors flex items-center gap-2">
                                     <svg class="w-4 h-4 text-slate-600 group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                                     <span x-text="domain.name"></span>
                                </a>
                            </td>
                            <td class="p-4">
                                <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded bg-slate-950 border border-slate-800 text-xxs font-bold uppercase tracking-wide"
                                    :class="domain.status === 'Monitored' ? 'text-emerald-400 border-emerald-900/50' : 'text-slate-500'">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="domain.status === 'Monitored' ? 'bg-emerald-500 shadow-[0_0_5px_lime] animate-pulse' : 'bg-slate-500'"></span>
                                    <span x-text="domain.status"></span>
                                </div>
                            </td>
                            <td class="p-4 text-slate-500" x-text="domain.latest_scan ? formatDate(domain.latest_scan.scan_date) : 'PENDING INTEL'"></td>
                            
                             <td class="p-4">
                                <template x-if="domain.latest_scan">
                                    <span class="font-bold" :class="domain.latest_scan.reputation_score < 0 ? 'text-red-500' : 'text-cyan-400'"
                                        x-text="domain.latest_scan.reputation_score"></span>
                                </template>
                                <template x-if="!domain.latest_scan"><span class="text-slate-600">-</span></template>
                            </td>

                            <td class="p-4">
                                <template x-if="domain.latest_scan && domain.latest_scan.vt_stats">
                                    <div class="flex items-center gap-2 text-xxs">
                                        <span class="text-red-500 font-bold" x-text="`${domain.latest_scan.vt_stats.malicious || 0} BAD`"></span>
                                        <span class="text-slate-600">/</span>
                                        <span class="text-emerald-500 font-bold" x-text="`${domain.latest_scan.vt_stats.harmless || 0} GOOD`"></span>
                                    </div>
                                </template>
                                <template x-if="!domain.latest_scan"><span class="text-slate-600">-</span></template>
                            </td>

                            <td class="p-4 text-right flex items-center justify-end gap-2">
                                <template x-if="domain.latest_scan && domain.latest_scan.permalink">
                                    <a :href="domain.latest_scan.permalink" target="_blank" class="text-indigo-400 hover:text-white border border-indigo-900 hover:bg-indigo-900/50 px-2 py-1 rounded transition-colors text-xxs">REPORT</a>
                                </template>
                                
                                <form :action="`/cti/domains/${domain.id}/scan`" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-slate-500 hover:text-cyan-400 transition-colors p-1" title="Scan Now">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                    </button>
                                </form>

                                <form :action="`/cti/domains/${domain.id}`" method="POST" class="inline" onsubmit="return confirm('Terminate surveillance on this target?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-slate-500 hover:text-red-500 transition-colors p-1" title="Delete">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    </template>
                    <template x-if="domains.length === 0">
                        <tr><td colspan="6" class="p-8 text-center text-slate-500 italic">No targets currently under surveillance.</td></tr>
                    </template>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination (Server Renegerated if possible, but for JSON mode we might lose it or need manual handling. For now standard blade links below table) -->
        <div class="px-6 py-4 border-t border-slate-800 bg-slate-900/50">
             {{ $domains->links('vendor.pagination.premium') }}
        </div>
    </div>
</div>

<!-- Add Domain Modal -->
<dialog id="addDomainModal" class="m-auto bg-transparent p-0 rounded-2xl shadow-2xl backdrop:bg-slate-900/50 backdrop:backdrop-blur-sm open:animate-fade-in-up w-full max-w-md">
    <div class="bg-slate-900 border border-indigo-500/30 w-full rounded-xl overflow-hidden relative shadow-[0_0_50px_rgba(79,70,229,0.2)]">
        <!-- Decoration -->
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-indigo-500 to-transparent"></div>

        <div class="px-6 py-4 border-b border-indigo-900/30 flex justify-between items-center bg-indigo-900/10">
            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                 <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                NEW SURVEILLANCE TARGET
            </h3>
            <form method="dialog"><button class="text-indigo-400/50 hover:text-indigo-400 transition-colors">&times;</button></form>
        </div>
        <form action="{{ route('cti.domain.store') }}" method="POST" class="p-6 space-y-5">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Target Domain Name</label>
                <div class="relative">
                    <input type="text" name="domain" class="w-full bg-slate-950 border border-slate-700 text-white pl-4 pr-4 py-3 rounded-lg focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all font-mono text-sm shadow-inner" placeholder="example.com" required>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-indigo-900/20">
                 <form method="dialog"><button type="button" onclick="document.getElementById('addDomainModal').close()" class="px-4 py-2 text-slate-500 hover:text-white font-bold text-sm">CANCEL</button></form>
                <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold shadow-lg shadow-indigo-900/20 flex items-center gap-2 transition-all">
                    INITIATE
                </button>
            </div>
        </form>
    </div>
</dialog>

<!-- ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('domainMonitor', (initialData) => ({
            domains: initialData,
            
            init() {
                // Poll every 5s for status updates
                setInterval(() => this.pollData(), 5000);
            },

            async pollData() {
                try {
                    const res = await fetch('{{ route("cti.domain.index") }}', {
                        headers: { 'Accept': 'application/json' }
                    });
                    if(res.ok) {
                        const data = await res.json();
                        this.domains = data.data; // Paginated response usually wraps in 'data'
                    }
                } catch (e) { console.error('Poll error', e); }
            },

            formatDate(dateStr) {
                 return new Date(dateStr).toLocaleString('id-ID', {
                        year: 'numeric', month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit'
                 });
            }
        }));
    });

    document.addEventListener('DOMContentLoaded', function () {
        // Shared Chart Config
        const commonOptions = {
            chart: { background: 'transparent', toolbar: { show: false } },
            theme: { mode: 'dark' },
            stroke: { show: false },
            dataLabels: { enabled: true, style: { fontWeight: 'bold' } },
            legend: { position: 'bottom', labels: { colors: '#94a3b8' } }
        };

        // SSL Chart
        new ApexCharts(document.querySelector("#sslChart"), {
            ...commonOptions,
            series: [{{ $sslStats['valid'] }}, {{ $sslStats['expiring'] }}, {{ $sslStats['expired'] }}],
            chart: { type: 'donut', height: 250,  background: 'transparent' },
            labels: ['Secure', 'Warning', 'Critical'],
            colors: ['#10B981', '#F59E0B', '#EF4444'],
            plotOptions: { pie: { donut: { size: '65%', labels: { show: true, total: { show: true, label: 'Certificates', color: '#fff' } } } } }
        }).render();

        // Typosquat Chart
        new ApexCharts(document.querySelector("#typoChart"), {
            ...commonOptions,
            series: [{ name: 'Domains', data: [{{ $typoStats['suspicious'] }}, {{ $typoStats['clean'] }}] }],
            chart: { type: 'bar', height: 250, background: 'transparent', toolbar: { show: false } },
            plotOptions: { bar: { borderRadius: 4, horizontal: true, barHeight: '50%' } },
            colors: ['#EF4444', '#10B981'],
            xaxis: { categories: ['Detected Threats', 'Clean Variations'], labels: { style: { colors: '#94a3b8' } } },
            grid: { borderColor: '#334155' }
        }).render();

        // Ransomware Groups Chart
        new ApexCharts(document.querySelector("#ransomGroupChart"), {
            ...commonOptions,
            series: [{ name: 'Victims', data: {!! json_encode(array_values($ransomwareStats['groups']->toArray())) !!} }],
            xaxis: { categories: {!! json_encode(array_keys($ransomwareStats['groups']->toArray())) !!}, labels: { style: { colors: '#94a3b8' } } },
            chart: { type: 'bar', height: 250, background: 'transparent', toolbar: { show: false } },
            colors: ['#F97316'],
            plotOptions: { bar: { borderRadius: 4, horizontal: false, columnWidth: '55%' } },
            grid: { borderColor: '#334155' }
        }).render();
    });
</script>
@endsection
