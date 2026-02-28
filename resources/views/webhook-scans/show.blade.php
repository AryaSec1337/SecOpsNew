@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">

    <!-- Header & Back Button -->
    <div class="flex items-center justify-between pb-4 border-b border-white/10">
        <div class="flex items-center gap-4">
            <a href="{{ route('webhook-scans.index') }}" class="p-2 rounded-lg bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h1 class="text-2xl font-black text-white tracking-tight flex items-center gap-3">
                    Scan Report: {{ $scan->original_filename }}
                    
                    @if($scan->verdict === 'CLEAN')
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold tracking-widest bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 uppercase">Clean</span>
                    @elseif($scan->verdict === 'SUSPICIOUS')
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold tracking-widest bg-amber-500/20 text-amber-400 border border-amber-500/30 uppercase">Suspicious</span>
                    @elseif($scan->verdict === 'MALICIOUS')
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold tracking-widest bg-rose-500/20 text-rose-400 border border-rose-500/30 uppercase">Malicious</span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold tracking-widest bg-slate-500/20 text-slate-400 border border-slate-500/30 uppercase">{{ $scan->verdict }}</span>
                    @endif
                </h1>
                <p class="text-sm text-slate-400 mt-1">Scanned at {{ $scan->created_at->format('M d, Y - H:i:s') }}</p>
            </div>
        </div>
        
        @if($scan->mitigationLog)
        <a href="{{ route('mitigation-logs.show', $scan->mitigationLog->id) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-bold rounded-lg shadow-lg shadow-blue-500/20 transition-all flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
            View Investigation Case
        </a>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column: Metadata -->
        <div class="lg:col-span-1 space-y-6">
            <div class="glass-panel p-5 rounded-2xl border border-white/5">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    File Metadata
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <div class="text-[10px] text-slate-500 uppercase tracking-wider mb-1">File ID</div>
                        <div class="text-sm text-slate-300 font-mono bg-slate-900/50 p-2 rounded">{{ $scan->file_id }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] text-slate-500 uppercase tracking-wider mb-1">SHA-256 Hash</div>
                        <div class="text-sm text-slate-300 font-mono bg-slate-900/50 p-2 rounded break-all">{{ $scan->sha256 }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] text-slate-500 uppercase tracking-wider mb-1">Server Hostname</div>
                        <div class="text-sm text-slate-300 font-mono bg-slate-900/50 p-2 rounded flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"></path></svg>
                            {{ $scan->server_hostname ?? 'N/A' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-[10px] text-slate-500 uppercase tracking-wider mb-1">Full Path</div>
                        <div class="text-sm text-slate-300 font-mono bg-slate-900/50 p-2 rounded break-all flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                            {{ $scan->fullpath ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-[10px] text-slate-500 uppercase tracking-wider mb-1">Size</div>
                            <div class="text-sm text-slate-300 font-mono">{{ number_format($scan->size_bytes / 1024, 2) }} KB</div>
                        </div>
                        <div>
                            <div class="text-[10px] text-slate-500 uppercase tracking-wider mb-1">Pipeline Duration</div>
                            @php
                                $start = \Carbon\Carbon::parse($scan->timestamps_stages['start'] ?? now());
                                $end = \Carbon\Carbon::parse($scan->timestamps_stages['end'] ?? now());
                                $diff = $start->diffInSeconds($end);
                            @endphp
                            <div class="text-sm text-slate-300 font-mono">{{ $diff }} seconds</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="glass-panel p-5 rounded-2xl border border-white/5">
                 <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Scan Timeline
                </h3>
                <div class="space-y-3 relative before:absolute before:inset-0 before:ml-2 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-slate-700 before:to-transparent">
                    <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                        <div class="flex items-center justify-center w-5 h-5 rounded-full border border-blue-500 bg-slate-900 group-[.is-active]:bg-blue-500 text-white shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 shadow flex-col relative z-20"></div>
                        <div class="w-[calc(100%-1.5rem)] md:w-[calc(50%-1.25rem)] p-2 rounded border border-white/5 bg-slate-900/50">
                            <div class="flex items-center justify-between mb-0.5">
                                <div class="font-bold text-slate-300 text-xs text-blue-400">Initialize</div>
                                <div class="text-[10px] font-mono text-slate-500">{{ \Carbon\Carbon::parse($scan->timestamps_stages['start'] ?? now())->format('H:i:s') }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                        <div class="flex items-center justify-center w-5 h-5 rounded-full border border-slate-600 bg-slate-900 group-[.is-active]:bg-slate-600 text-white shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 shadow flex-col relative z-20"></div>
                        <div class="w-[calc(100%-1.5rem)] md:w-[calc(50%-1.25rem)] p-2 rounded border border-white/5 bg-slate-900/50">
                            <div class="flex items-center justify-between mb-0.5">
                                <div class="font-bold text-slate-300 text-xs">YARA Scan</div>
                                <div class="text-[10px] font-mono text-slate-500">{{ \Carbon\Carbon::parse($scan->timestamps_stages['yara_scan'] ?? now())->format('H:i:s') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                        <div class="flex items-center justify-center w-5 h-5 rounded-full border border-slate-600 bg-slate-900 group-[.is-active]:bg-slate-600 text-white shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 shadow flex-col relative z-20"></div>
                        <div class="w-[calc(100%-1.5rem)] md:w-[calc(50%-1.25rem)] p-2 rounded border border-white/5 bg-slate-900/50">
                            <div class="flex items-center justify-between mb-0.5">
                                <div class="font-bold text-slate-300 text-xs">ClamAV Scan</div>
                                <div class="text-[10px] font-mono text-slate-500">{{ \Carbon\Carbon::parse($scan->timestamps_stages['clamav_scan'] ?? now())->format('H:i:s') }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                        <div class="flex items-center justify-center w-5 h-5 rounded-full border border-slate-600 bg-slate-900 group-[.is-active]:bg-slate-600 text-white shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 shadow flex-col relative z-20"></div>
                        <div class="w-[calc(100%-1.5rem)] md:w-[calc(50%-1.25rem)] p-2 rounded border border-white/5 bg-slate-900/50">
                            <div class="flex items-center justify-between mb-0.5">
                                <div class="font-bold text-slate-300 text-xs">VirusTotal</div>
                                <div class="text-[10px] font-mono text-slate-500">{{ \Carbon\Carbon::parse($scan->timestamps_stages['vt_lookup'] ?? now())->format('H:i:s') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                        <div class="flex items-center justify-center w-5 h-5 rounded-full border border-emerald-500 bg-slate-900 group-[.is-active]:bg-emerald-500 text-white shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 shadow flex-col relative z-20"></div>
                        <div class="w-[calc(100%-1.5rem)] md:w-[calc(50%-1.25rem)] p-2 rounded border border-white/5 bg-emerald-900/20">
                            <div class="flex items-center justify-between mb-0.5">
                                <div class="font-bold text-emerald-400 text-xs">Completed</div>
                                <div class="text-[10px] font-mono text-emerald-500/70">{{ \Carbon\Carbon::parse($scan->timestamps_stages['end'] ?? now())->format('H:i:s') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Engine Details -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- YARA Results -->
            <div class="glass-panel p-5 rounded-2xl border border-white/5 relative overflow-hidden">
                <div class="absolute right-0 top-0 p-4 opacity-10 pointer-events-none">
                    <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                </div>
                <h3 class="text-sm font-bold text-white uppercase tracking-widest mb-4 flex items-center justify-between">
                    <span>YARA Rule Engine</span>
                    @if($scan->yara_result && !empty($scan->yara_result['matches']))
                        <span class="px-2 py-0.5 bg-rose-500/20 text-rose-400 border border-rose-500/30 rounded text-[10px] uppercase">Matches Found</span>
                    @elseif($scan->yara_result && isset($scan->yara_result['message']) && str_starts_with($scan->yara_result['message'], 'Skipped'))
                        <span class="px-2 py-0.5 bg-slate-500/20 text-slate-400 border border-slate-500/30 rounded text-[10px] uppercase">Skipped</span>
                    @else
                        <span class="px-2 py-0.5 bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 rounded text-[10px] uppercase">Clean</span>
                    @endif
                </h3>
                
                @if($scan->yara_result && !empty($scan->yara_result['matches']))
                    <div class="bg-slate-900/50 rounded-lg p-3">
                        <ul class="space-y-2 text-sm text-slate-300 font-mono">
                        @foreach($scan->yara_result['matches'] as $match)
                            <li class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                <span>{{ is_array($match) ? ($match['rule'] ?? json_encode($match)) : $match }}</span>
                            </li>
                        @endforeach
                        </ul>
                    </div>
                @elseif($scan->yara_result && isset($scan->yara_result['error']))
                     <div class="p-3 rounded-lg bg-slate-800/50 border border-slate-700 text-slate-400 text-sm italic">
                        Error running YARA: {{ $scan->yara_result['error'] }}
                    </div>
                @elseif($scan->yara_result && isset($scan->yara_result['message']) && str_starts_with($scan->yara_result['message'], 'Skipped'))
                    <div class="p-3 rounded-lg bg-slate-800/50 border border-slate-700 text-slate-400 text-sm italic">
                        {{ $scan->yara_result['message'] }}
                    </div>
                @else
                    <div class="p-3 rounded-lg bg-slate-800/50 border border-slate-700 text-slate-400 text-sm">
                        No YARA matches were triggered by this file.
                    </div>
                @endif
            </div>

            <!-- ClamAV Results -->
             <div class="glass-panel p-5 rounded-2xl border border-white/5 relative overflow-hidden">
                <div class="absolute right-0 top-0 p-4 opacity-10 pointer-events-none">
                    <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                </div>
                <h3 class="text-sm font-bold text-white uppercase tracking-widest mb-4 flex items-center justify-between">
                    <span>ClamAV Engine</span>
                    @php
                        $clamStr = $scan->clamav_result['output'] ?? '';
                        $clamSkipped = isset($scan->clamav_result['message']) && str_starts_with($scan->clamav_result['message'], 'Skipped');
                        $clamInfected = ($scan->clamav_result['infected'] ?? false) === true;
                        $isFound = str_contains($clamStr, 'FOUND') || $clamInfected;
                    @endphp
                    @if($isFound)
                        <span class="px-2 py-0.5 bg-rose-500/20 text-rose-400 border border-rose-500/30 rounded text-[10px] uppercase">Threat Found</span>
                    @elseif($clamSkipped)
                        <span class="px-2 py-0.5 bg-slate-500/20 text-slate-400 border border-slate-500/30 rounded text-[10px] uppercase">Skipped</span>
                    @else
                        <span class="px-2 py-0.5 bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 rounded text-[10px] uppercase">Clean</span>
                    @endif
                </h3>
                
                @if($clamSkipped)
                    <div class="p-3 rounded-lg bg-slate-800/50 border border-slate-700 text-slate-400 text-sm italic">
                        {{ $scan->clamav_result['message'] }}
                    </div>
                @else
                    <div class="bg-slate-900/50 rounded-lg p-3">
                        <pre class="text-xs text-slate-300 font-mono overflow-x-auto whitespace-pre-wrap">{{ $clamStr ?: 'No Output' }}</pre>
                    </div>
                @endif
            </div>

            <!-- VirusTotal Results -->
            <div class="glass-panel p-5 rounded-2xl border border-white/5 relative overflow-hidden">
                <div class="absolute right-0 top-0 p-4 opacity-10 pointer-events-none">
                    <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
                <h3 class="text-sm font-bold text-white uppercase tracking-widest mb-4 flex items-center justify-between">
                    <span>VirusTotal Analysis</span>
                    @php
                        // VT data stored without 'data' wrapper (stripped in runVirusTotal)
                        // Stats are under 'attributes.last_analysis_stats'
                        $vtStats = $scan->vt_result['attributes']['last_analysis_stats'] 
                                   ?? $scan->vt_result['data']['attributes']['last_analysis_stats']
                                   ?? $scan->vt_result['data']['attributes']['stats']
                                   ?? null;
                        $vtMalicious = $vtStats['malicious'] ?? 0;
                        $vtSuspicious = $vtStats['suspicious'] ?? 0;
                        $vtUndetected = $vtStats['undetected'] ?? 0;
                        $vtHarmless = $vtStats['harmless'] ?? 0;
                        $vtTotal = $vtMalicious + $vtUndetected + $vtHarmless;
                        $vtSkipped = isset($scan->vt_result['message']) && str_starts_with($scan->vt_result['message'], 'Skipped');
                    @endphp
                    
                    @if($vtSkipped)
                        <span class="px-2 py-0.5 bg-slate-500/20 text-slate-400 border border-slate-500/30 rounded text-[10px] uppercase font-bold">Skipped</span>
                    @elseif($vtMalicious > 0)
                        <span class="px-2 py-0.5 bg-rose-500/20 text-rose-400 border border-rose-500/30 rounded text-[10px] uppercase font-bold">{{ $vtMalicious }} / {{ $vtTotal }} Detections</span>
                    @elseif($vtStats)
                        <span class="px-2 py-0.5 bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 rounded text-[10px] uppercase font-bold">0 / {{ $vtTotal }} Detections</span>
                    @else
                        <span class="px-2 py-0.5 bg-slate-500/20 text-slate-400 border border-slate-500/30 rounded text-[10px] uppercase font-bold">Unknown / Not Found</span>
                    @endif
                </h3>
                
                @if($vtSkipped)
                    <div class="p-3 rounded-lg bg-slate-800/50 border border-slate-700 text-slate-400 text-sm italic">
                        {{ $scan->vt_result['message'] }}
                    </div>
                @elseif($vtStats)
                    <div class="grid grid-cols-4 gap-4 mb-4">
                        <div class="bg-slate-900/50 rounded-lg p-3 text-center border-t-2 border-rose-500">
                            <div class="text-[10px] text-slate-500 uppercase tracking-wider mb-1">Malicious</div>
                            <div class="text-xl font-bold text-rose-500">{{ $vtMalicious }}</div>
                        </div>
                        <div class="bg-slate-900/50 rounded-lg p-3 text-center border-t-2 border-emerald-500">
                            <div class="text-[10px] text-slate-500 uppercase tracking-wider mb-1">Undetected</div>
                            <div class="text-xl font-bold text-emerald-500">{{ $vtUndetected }}</div>
                        </div>
                        <div class="bg-slate-900/50 rounded-lg p-3 text-center border-t-2 border-blue-500">
                            <div class="text-[10px] text-slate-500 uppercase tracking-wider mb-1">Harmless</div>
                            <div class="text-xl font-bold text-blue-500">{{ $vtHarmless }}</div>
                        </div>
                        <div class="bg-slate-900/50 rounded-lg p-3 text-center border-t-2 border-amber-500">
                            <div class="text-[10px] text-slate-500 uppercase tracking-wider mb-1">Suspicious</div>
                            <div class="text-xl font-bold text-amber-500">{{ $vtSuspicious }}</div>
                        </div>
                    </div>
                @endif
                
                <div x-data="{ expanded: false }">
                    <button @click="expanded = !expanded" class="text-xs text-blue-400 hover:text-blue-300 flex items-center gap-1 mb-2">
                        <svg class="w-3 h-3 transition-transform" :class="expanded ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        View Raw JSON Response
                    </button>
                    <div x-show="expanded" x-collapse>
                        <div class="bg-slate-900/80 rounded-lg p-4 overflow-x-auto">
                            <pre class="text-[10px] text-slate-300 font-mono">{{ json_encode($scan->vt_result, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
