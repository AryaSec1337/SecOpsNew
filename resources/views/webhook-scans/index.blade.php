@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header Area -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">File Upload Monitoring</h1>
            <p class="text-sm text-slate-400 mt-1">Monitor real-time security scans of files uploaded via Webhook.</p>
        </div>
        <div class="flex items-center gap-2 px-3 py-1.5 bg-emerald-500/10 border border-emerald-500/30 rounded-lg">
            <span class="relative flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
            </span>
            <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider">Live</span>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="glass-panel p-5 rounded-2xl relative overflow-hidden group">
            <div class="absolute inset-0 bg-blue-500/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-12 h-12 rounded-xl bg-blue-500/20 text-blue-400 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-slate-400">Total Scans</h3>
                    <div class="text-2xl font-black text-white" id="stat-total">{{ $scans->total() }}</div>
                </div>
            </div>
        </div>
        
        @php
            // Calculate a few simple stats from current page for demo, ideally these should come from DB queries
            $cleanCount = $scans->where('verdict', 'CLEAN')->count();
            $suspiciousCount = $scans->where('verdict', 'SUSPICIOUS')->count();
            $maliciousCount = $scans->where('verdict', 'MALICIOUS')->count();
        @endphp

        <div class="glass-panel p-5 rounded-2xl relative overflow-hidden group">
            <div class="absolute inset-0 bg-emerald-500/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-12 h-12 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-slate-400">Clean Files (Page)</h3>
                    <div class="text-2xl font-black text-white" id="stat-clean">{{ $cleanCount }}</div>
                </div>
            </div>
        </div>

        <div class="glass-panel p-5 rounded-2xl relative overflow-hidden group">
            <div class="absolute inset-0 bg-amber-500/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-12 h-12 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-slate-400">Suspicious (Page)</h3>
                    <div class="text-2xl font-black text-white" id="stat-suspicious">{{ $suspiciousCount }}</div>
                </div>
            </div>
        </div>

        <div class="glass-panel p-5 rounded-2xl relative overflow-hidden group">
            <div class="absolute inset-0 bg-rose-500/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-12 h-12 rounded-xl bg-rose-500/20 text-rose-400 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-slate-400">Malicious (Page)</h3>
                    <div class="text-2xl font-black text-white" id="stat-malicious">{{ $maliciousCount }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="glass-panel rounded-2xl overflow-hidden border border-white/5 shadow-xl">
        <div class="p-5 border-b border-white/5 flex items-center justify-between bg-slate-900/50">
            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                Recent Upload Log
            </h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="text-xs uppercase bg-slate-900/80 text-slate-400 font-bold tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Status & File</th>
                        <th class="px-6 py-4">Server</th>
                        <th class="px-6 py-4">Size</th>
                        <th class="px-6 py-4">Engines</th>
                        <th class="px-6 py-4">Timestamp</th>
                        <th class="px-6 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5" id="scans-tbody">
                    @forelse($scans as $scan)
                        <tr class="hover:bg-white/5 transition-colors group" data-scan-id="{{ $scan->id }}">
                            <!-- Status & File Info -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <!-- Status Indicator -->
                                    <div class="relative flex h-3 w-3">
                                        @if($scan->verdict === 'CLEAN')
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-20"></span>
                                            <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                                        @elseif($scan->verdict === 'SUSPICIOUS')
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-20"></span>
                                            <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                                        @elseif($scan->verdict === 'MALICIOUS')
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-20"></span>
                                            <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
                                        @else
                                            <span class="relative inline-flex rounded-full h-3 w-3 bg-slate-500"></span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-bold text-white group-hover:text-blue-400 transition-colors">
                                            {{ Str::limit($scan->original_filename, 30) }}
                                        </div>
                                        <div class="text-[10px] text-slate-500 font-mono tracking-wider mt-0.5">
                                            SHA256: <span title="{{ $scan->sha256 }}">{{ Str::limit($scan->sha256, 12) }}...</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Server Hostname -->
                            <td class="px-6 py-4">
                                @if($scan->server_hostname)
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"></path></svg>
                                        <span class="text-slate-300 text-xs font-mono">{{ $scan->server_hostname }}</span>
                                    </div>
                                @else
                                    <span class="text-slate-600 text-xs">—</span>
                                @endif
                            </td>

                            <!-- Size -->
                            <td class="px-6 py-4 text-slate-300">
                                {{ number_format($scan->size_bytes / 1024, 2) }} KB
                            </td>

                            <!-- Engines -->
                            <td class="px-6 py-4">
                                <div class="flex gap-1.5">
                                    @php
                                        $yaraSkipped = isset($scan->yara_result['message']) && str_starts_with($scan->yara_result['message'], 'Skipped');
                                        $clamSkipped = isset($scan->clamav_result['message']) && str_starts_with($scan->clamav_result['message'], 'Skipped');
                                    @endphp
                                    <span class="px-2 py-1 rounded text-[10px] font-bold tracking-wider 
                                          {{ $scan->yara_result && ($scan->yara_result['matches'] ?? false) ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 
                                             ($yaraSkipped ? 'bg-slate-700/50 text-slate-400 border border-slate-600/50' : 'bg-slate-800 text-slate-500') }}" title="YARA">
                                        YARA
                                    </span>
                                    <span class="px-2 py-1 rounded text-[10px] font-bold tracking-wider 
                                          {{ $scan->clamav_result && str_contains($scan->clamav_result['output'] ?? '', 'FOUND') ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : 
                                             ($clamSkipped ? 'bg-slate-700/50 text-slate-400 border border-slate-600/50' : 'bg-slate-800 text-slate-500') }}" title="ClamAV">
                                        CLAM
                                    </span>
                                    <span class="px-2 py-1 rounded text-[10px] font-bold tracking-wider 
                                          {{ $scan->vt_result && ($scan->vt_result['data']['attributes']['stats']['malicious'] ?? 0) > 0 ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : 'bg-slate-800 text-slate-500' }}" title="VirusTotal">
                                        VT
                                    </span>
                                </div>
                            </td>

                            <!-- Timestamp -->
                            <td class="px-6 py-4 text-slate-400">
                                <div class="text-white">{{ $scan->created_at->format('M d, Y') }}</div>
                                <div class="text-[10px] tracking-wider mt-0.5">{{ $scan->created_at->format('H:i:s') }}</div>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('webhook-scans.show', $scan->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-lg transition-colors border border-slate-700 text-xs font-medium">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    View Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                <svg class="w-12 h-12 mx-auto text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                <div>No webhook file scans recorded yet.</div>
                                <div class="text-xs mt-1">Files uploaded via Gateway/App API will appear here.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($scans->hasPages())
        <div class="px-6 py-4 border-t border-white/5 bg-slate-900/40">
            {{ $scans->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const POLL_INTERVAL = 5000; // 5 seconds
    let lastKnownIds = [];

    // Collect initially rendered IDs
    document.querySelectorAll('#scans-tbody tr[data-scan-id]').forEach(tr => {
        lastKnownIds.push(parseInt(tr.dataset.scanId));
    });

    function truncate(str, len) {
        if (!str) return '';
        return str.length > len ? str.substring(0, len) + '...' : str;
    }

    function verdictDot(verdict) {
        const colors = {
            'CLEAN': 'emerald', 'SUSPICIOUS': 'amber', 'MALICIOUS': 'rose'
        };
        const c = colors[verdict] || 'slate';
        if (verdict === 'CLEAN' || verdict === 'SUSPICIOUS' || verdict === 'MALICIOUS') {
            return `<div class="relative flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-${c}-400 opacity-20"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-${c}-500"></span>
            </div>`;
        }
        return `<div class="relative flex h-3 w-3"><span class="relative inline-flex rounded-full h-3 w-3 bg-slate-500"></span></div>`;
    }

    function engineBadge(scan) {
        const yaraHit = scan.yara_result && scan.yara_result.matches && scan.yara_result.matches.length > 0;
        const clamHit = scan.clamav_result && (scan.clamav_result.infected || (scan.clamav_result.output && scan.clamav_result.output.includes('FOUND')));
        const yaraSkip = scan.yara_result && scan.yara_result.message && scan.yara_result.message.startsWith('Skipped');
        const clamSkip = scan.clamav_result && scan.clamav_result.message && scan.clamav_result.message.startsWith('Skipped');

        let yClass = yaraHit ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : (yaraSkip ? 'bg-slate-700/50 text-slate-400 border border-slate-600/50' : 'bg-slate-800 text-slate-500');
        let cClass = clamHit ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : (clamSkip ? 'bg-slate-700/50 text-slate-400 border border-slate-600/50' : 'bg-slate-800 text-slate-500');
        let vClass = 'bg-slate-800 text-slate-500';

        return `<div class="flex gap-1.5">
            <span class="px-2 py-1 rounded text-[10px] font-bold tracking-wider ${yClass}">YARA</span>
            <span class="px-2 py-1 rounded text-[10px] font-bold tracking-wider ${cClass}">CLAM</span>
            <span class="px-2 py-1 rounded text-[10px] font-bold tracking-wider ${vClass}">VT</span>
        </div>`;
    }

    function buildRow(scan) {
        const sizeKB = (scan.size_bytes / 1024).toFixed(2);
        const serverHtml = scan.server_hostname
            ? `<div class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"></path></svg>
                <span class="text-slate-300 text-xs font-mono">${scan.server_hostname}</span>
              </div>`
            : `<span class="text-slate-600 text-xs">\u2014</span>`;

        return `<tr class="hover:bg-white/5 transition-colors group" data-scan-id="${scan.id}" style="animation: fadeIn 0.4s ease-out;">
            <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                    ${verdictDot(scan.verdict)}
                    <div>
                        <div class="font-bold text-white group-hover:text-blue-400 transition-colors">${truncate(scan.original_filename, 30)}</div>
                        <div class="text-[10px] text-slate-500 font-mono tracking-wider mt-0.5">SHA256: ${truncate(scan.sha256, 12)}...</div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4">${serverHtml}</td>
            <td class="px-6 py-4 text-slate-300">${sizeKB} KB</td>
            <td class="px-6 py-4">${engineBadge(scan)}</td>
            <td class="px-6 py-4 text-slate-400">
                <div class="text-white">${scan.created_at_date}</div>
                <div class="text-[10px] tracking-wider mt-0.5">${scan.created_at_time}</div>
            </td>
            <td class="px-6 py-4 text-right">
                <a href="${scan.show_url}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-lg transition-colors border border-slate-700 text-xs font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    View Detail
                </a>
            </td>
        </tr>`;
    }

    async function pollScans() {
        try {
            const res = await fetch('{{ route("webhook-scans.api.latest") }}');
            if (!res.ok) return;
            const data = await res.json();

            // Update stat counters
            document.getElementById('stat-total').textContent = data.total;
            document.getElementById('stat-clean').textContent = data.clean_count;
            document.getElementById('stat-suspicious').textContent = data.suspicious_count;
            document.getElementById('stat-malicious').textContent = data.malicious_count;

            // Check for new scans
            const newIds = data.scans.map(s => s.id);
            const hasNew = newIds.some(id => !lastKnownIds.includes(id));

            if (hasNew) {
                // Rebuild entire table body
                const tbody = document.getElementById('scans-tbody');
                tbody.innerHTML = data.scans.map(scan => buildRow(scan)).join('');
                lastKnownIds = newIds;
            }
        } catch (e) {
            console.warn('Polling error:', e);
        }
    }

    setInterval(pollScans, POLL_INTERVAL);
});
</script>
<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
@endpush
@endsection
