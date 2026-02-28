@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('webhook-alerts.index') }}" class="text-slate-400 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <div class="flex items-center gap-3">
                    <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider
                        {{ $webhookAlert->verdict === 'MALICIOUS' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : '' }}
                        {{ $webhookAlert->verdict === 'SUSPICIOUS' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : '' }}
                        {{ $webhookAlert->verdict === 'CLEAN' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : '' }}">
                        {{ $webhookAlert->verdict }}
                    </span>
                    <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider
                        {{ $webhookAlert->status === 'Pending' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : '' }}
                        {{ $webhookAlert->status === 'In Progress' ? 'bg-blue-500/20 text-blue-400 border border-blue-500/30' : '' }}
                        {{ $webhookAlert->status === 'Resolved' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : '' }}">
                        {{ $webhookAlert->status }}
                    </span>
                </div>
                <h1 class="text-xl font-black text-white tracking-tight mt-2">{{ $webhookAlert->title }}</h1>
                <p class="text-xs text-slate-400 mt-1">
                    Created {{ $webhookAlert->created_at->format('M d, Y H:i:s') }}
                    @if($webhookAlert->server_hostname)
                        &bull; Server: <span class="text-blue-400 font-mono">{{ $webhookAlert->server_hostname }}</span>
                    @endif
                </p>
            </div>
        </div>

        <!-- Status Update -->
        <div class="flex items-center gap-2">
            <form method="POST" action="{{ route('webhook-alerts.update-status', $webhookAlert->id) }}" class="flex items-center gap-2">
                @csrf
                @method('PATCH')
                <select name="status" class="bg-slate-800 border border-slate-700 text-slate-300 rounded-lg text-xs px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="Pending" {{ $webhookAlert->status === 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="In Progress" {{ $webhookAlert->status === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="Resolved" {{ $webhookAlert->status === 'Resolved' ? 'selected' : '' }}>Resolved</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-xs font-bold transition-colors">
                    Update
                </button>
            </form>

            @if($webhookAlert->webhookFileScan)
                <a href="{{ route('webhook-scans.show', $webhookAlert->webhook_file_scan_id) }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-lg text-xs font-medium border border-slate-700 transition-colors">
                    View Full Scan →
                </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: File Metadata -->
        <div class="lg:col-span-1 space-y-6">
            <div class="glass-panel p-5 rounded-2xl border border-white/5">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    File Details
                </h3>
                <div class="space-y-4">
                    <div>
                        <div class="text-[10px] text-slate-500 uppercase tracking-wider mb-1">Filename</div>
                        <div class="text-sm text-slate-300 font-mono bg-slate-900/50 p-2 rounded break-all">{{ $webhookAlert->original_filename }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] text-slate-500 uppercase tracking-wider mb-1">SHA-256</div>
                        <div class="text-sm text-slate-300 font-mono bg-slate-900/50 p-2 rounded break-all text-[11px]">{{ $webhookAlert->sha256 }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] text-slate-500 uppercase tracking-wider mb-1">Server Hostname</div>
                        <div class="text-sm text-slate-300 font-mono bg-slate-900/50 p-2 rounded flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"></path></svg>
                            {{ $webhookAlert->server_hostname ?? 'N/A' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-[10px] text-slate-500 uppercase tracking-wider mb-1">Full Path</div>
                        <div class="text-sm text-slate-300 font-mono bg-slate-900/50 p-2 rounded break-all flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                            {{ $webhookAlert->fullpath ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-[10px] text-slate-500 uppercase tracking-wider mb-1">Size</div>
                            <div class="text-sm text-slate-300 font-mono">{{ $webhookAlert->size_bytes ? number_format($webhookAlert->size_bytes / 1024, 2) . ' KB' : 'N/A' }}</div>
                        </div>
                        <div>
                            <div class="text-[10px] text-slate-500 uppercase tracking-wider mb-1">Detected By</div>
                            <div class="text-sm text-orange-400 font-bold">{{ $webhookAlert->detected_by ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Scan Results + Description -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Description -->
            <div class="glass-panel p-5 rounded-2xl border border-white/5">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Threat Analysis
                </h3>
                <pre class="text-sm text-slate-300 font-mono bg-slate-900/50 p-4 rounded-lg overflow-x-auto whitespace-pre-wrap leading-relaxed">{{ $webhookAlert->description }}</pre>
            </div>

            <!-- Scan Results Summary -->
            @if($webhookAlert->scan_results)
            <div class="glass-panel p-5 rounded-2xl border border-white/5">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    Engine Results
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- YARA -->
                    <div class="bg-slate-900/50 rounded-lg p-4 border border-white/5">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">YARA</span>
                            @if(isset($webhookAlert->scan_results['yara_matches']) && $webhookAlert->scan_results['yara_matches'] > 0)
                                <span class="px-1.5 py-0.5 bg-amber-500/20 text-amber-400 rounded text-[10px] font-bold">{{ $webhookAlert->scan_results['yara_matches'] }} RULES</span>
                            @else
                                <span class="px-1.5 py-0.5 bg-slate-700 text-slate-500 rounded text-[10px] font-bold">CLEAN</span>
                            @endif
                        </div>
                        @if(isset($webhookAlert->scan_results['yara_rules']) && is_array($webhookAlert->scan_results['yara_rules']))
                            <div class="space-y-1">
                                @foreach($webhookAlert->scan_results['yara_rules'] as $rule)
                                    <div class="text-[10px] font-mono text-amber-400 bg-amber-500/10 px-2 py-1 rounded">{{ $rule }}</div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <!-- ClamAV -->
                    <div class="bg-slate-900/50 rounded-lg p-4 border border-white/5">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">ClamAV</span>
                            @if(isset($webhookAlert->scan_results['clamav_infected']) && $webhookAlert->scan_results['clamav_infected'])
                                <span class="px-1.5 py-0.5 bg-rose-500/20 text-rose-400 rounded text-[10px] font-bold">INFECTED</span>
                            @else
                                <span class="px-1.5 py-0.5 bg-slate-700 text-slate-500 rounded text-[10px] font-bold">CLEAN</span>
                            @endif
                        </div>
                        @if(isset($webhookAlert->scan_results['clamav_output']))
                            <div class="text-[10px] font-mono text-rose-400">{{ $webhookAlert->scan_results['clamav_output'] }}</div>
                        @endif
                    </div>
                    <!-- VirusTotal -->
                    <div class="bg-slate-900/50 rounded-lg p-4 border border-white/5">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">VirusTotal</span>
                            @if(isset($webhookAlert->scan_results['vt_malicious']) && $webhookAlert->scan_results['vt_malicious'] > 0)
                                <span class="px-1.5 py-0.5 bg-rose-500/20 text-rose-400 rounded text-[10px] font-bold">{{ $webhookAlert->scan_results['vt_malicious'] }} DETECTIONS</span>
                            @else
                                <span class="px-1.5 py-0.5 bg-slate-700 text-slate-500 rounded text-[10px] font-bold">N/A</span>
                            @endif
                        </div>
                        @if(isset($webhookAlert->scan_results['vt_malicious']))
                            <div class="text-[10px] text-slate-400">
                                {{ $webhookAlert->scan_results['vt_malicious'] ?? 0 }} malicious &bull;
                                {{ $webhookAlert->scan_results['vt_undetected'] ?? 0 }} undetected
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
