@extends('layouts.dashboard')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header Section -->
    <div class="space-y-4">
        <!-- Top Navigation -->
        <div class="flex items-center justify-between">
            <a href="{{ route('mitigation-logs.index') }}" class="group flex items-center gap-2 text-sm font-medium text-slate-400 hover:text-white transition-colors">
                <div class="p-1.5 rounded-md bg-slate-800/50 group-hover:bg-slate-800 text-slate-500 group-hover:text-white transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </div>
                Back to Incidents
            </a>
            <div class="flex items-center gap-3">
                @if(in_array($mitigationLog->status, ['In Progress', 'Resolved']))
                <a href="{{ route('mitigation-logs.report', $mitigationLog) }}" target="_blank" class="flex items-center gap-2 px-4 py-2 {{ $mitigationLog->status === 'In Progress' ? 'bg-amber-600 hover:bg-amber-500 shadow-amber-900/20' : 'bg-emerald-600 hover:bg-emerald-500 shadow-emerald-900/20' }} text-white text-sm font-semibold rounded-lg shadow-lg transition-all transform hover:scale-[1.02]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    {{ $mitigationLog->status === 'In Progress' ? 'Download Progress Report' : 'Download Final Report' }}
                </a>
                @endif
                <a href="{{ route('mitigation-logs.edit', $mitigationLog) }}" class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-lg shadow-lg shadow-blue-900/20 transition-all transform hover:scale-[1.02]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Edit Incident
                </a>
            </div>
        </div>

        <!-- Title & Badges -->
        <div>
            <!-- Badge Row -->
            <div class="flex flex-wrap items-center gap-2 mb-3">
                <!-- Type Badge -->
                 @if($mitigationLog->type)
                    @php
                        $typeColors = [
                            'File Check' => 'bg-teal-500/10 text-teal-400 border-teal-500/20',
                            'Domain Check' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                            'Email Phishing' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                            'General' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                        ];
                    @endphp
                    <span class="px-2.5 py-1 rounded-md text-xs font-bold border {{ $typeColors[$mitigationLog->type] ?? $typeColors['General'] }}">
                        {{ $mitigationLog->type ?? 'General' }}
                    </span>
                @endif

                <!-- Status Badge -->
                 @php
                    $statusColors = [
                        'Pending' => 'bg-amber-500/10 text-amber-500 border-amber-500/20',
                        'In Progress' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                        'Resolved' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                    ];
                    $statusLabels = [
                        'Pending' => 'Pending Review',
                        'In Progress' => 'In Progress',
                        'Resolved' => 'Resolved',
                    ];
                @endphp
                <span class="px-2.5 py-1 rounded-md text-xs font-bold border {{ $statusColors[$mitigationLog->status] ?? 'bg-slate-700 text-slate-300' }}">
                    {{ $statusLabels[$mitigationLog->status] ?? $mitigationLog->status }}
                </span>

                <!-- Priority Badge -->
                @if($mitigationLog->priority)
                    @php
                        $priorityColors = [
                            'Low' => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
                            'Medium' => 'bg-yellow-500/10 text-yellow-500 border-yellow-500/20',
                            'High' => 'bg-orange-500/10 text-orange-400 border-orange-500/20',
                            'Critical' => 'bg-red-500/10 text-red-400 border-red-500/20',
                        ];
                    @endphp
                    <span class="px-2.5 py-1 rounded-md text-xs font-bold border {{ $priorityColors[$mitigationLog->priority] ?? 'bg-slate-700 text-slate-300' }}">
                        {{ $mitigationLog->priority }} Priority
                    </span>
                @endif
                
                <!-- Severity Badge -->
                @if($mitigationLog->severity)
                    <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-slate-800 border border-slate-700 text-slate-300 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full {{ match($mitigationLog->severity) { 'Critical' => 'bg-red-500', 'High' => 'bg-orange-500', 'Medium' => 'bg-yellow-500', default => 'bg-slate-500' } }}"></span>
                        {{ $mitigationLog->severity }} Severity
                    </span>
                @endif

                <!-- Attack Classification -->
                @if($mitigationLog->attack_classification)
                    @if($mitigationLog->attack_classification === 'True Attack')
                        <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-red-500/10 text-red-400 border border-red-500/20">
                            ⚠ True Attack
                        </span>
                    @elseif($mitigationLog->attack_classification === 'False Attack')
                        <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            ✓ False Attack
                        </span>
                    @endif
                @endif
            </div>

            <!-- Title -->
            <h1 class="text-3xl md:text-4xl font-extrabold text-white tracking-tight leading-tight mb-4 shadow-slate-900 drop-shadow-sm">
                {{ $mitigationLog->title }}
            </h1>

            <!-- Meta Information -->
            <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-slate-400 border-t border-slate-800 pt-4 mt-2">
                <!-- Reporter -->
                <div class="flex items-center gap-2" title="Reported By">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    <span>{{ $mitigationLog->reporter_email ?? 'Unknown Reporter' }}</span>
                </div>
                
                <!-- Created At -->
                <div class="flex items-center gap-2" title="Incident Date">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span>{{ $mitigationLog->created_at->format('M d, Y • H:i') }}</span>
                </div>

                <!-- Legacy System Affected (Only if present) -->
                @if($mitigationLog->system_affected)
                <div class="flex items-center gap-2" title="Legacy System Info">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 01-2 2v4a2 2 0 012 2h14a2 2 0 012-2v-4a2 2 0 01-2-2m-2-4h.01M17 16h.01"></path></svg>
                    <span class="font-mono text-slate-300">{{ $mitigationLog->system_affected }}</span>
                </div>
                @endif
                
                <!-- Logged By -->
                <div class="ml-auto flex items-center gap-2 text-xs text-slate-500">
                    <span>Logged by <span class="text-slate-400 font-medium">{{ $mitigationLog->user->name ?? 'System' }}</span></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Mitigation Details -->
    <div class="glass-panel p-6 rounded-xl border border-slate-800 space-y-6">
        <div>
            <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-2">Description</h3>
            <p class="text-slate-300 leading-relaxed whitespace-pre-line">{{ $mitigationLog->description }}</p>
        </div>

        @if($mitigationLog->analyst_decision)
        <div>
            <h3 class="text-sm font-bold text-blue-400 uppercase tracking-wider mb-2">Decision / Analysis Result</h3>
            <div class="bg-blue-500/10 border border-blue-500/20 p-4 rounded-lg">
                <p class="text-blue-100 leading-relaxed whitespace-pre-line">{{ $mitigationLog->analyst_decision }}</p>
            </div>
        </div>
        @endif

        @if($mitigationLog->ipAnalysis)
        @php $ipData = $mitigationLog->ipAnalysis; @endphp
        <div>
            <div class="bg-purple-500/5 p-4 rounded-lg border border-purple-500/20">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                        <h4 class="text-sm font-bold text-purple-400 uppercase tracking-wider">IP Analyzer Result</h4>
                    </div>
                    @php
                        $riskScore = $ipData->risk_score ?? 0;
                        $riskColor = $riskScore >= 70 ? 'bg-red-500/10 text-red-400 border-red-500/20' : ($riskScore >= 40 ? 'bg-amber-500/10 text-amber-400 border-amber-500/20' : 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20');
                    @endphp
                    <span class="text-xs font-mono px-2.5 py-1 rounded-full border {{ $riskColor }}">
                        Risk: {{ $riskScore }}/100
                    </span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">IP Address</h4>
                        <p class="text-white font-mono text-sm">{{ $ipData->ip_address }}</p>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Country</h4>
                        <p class="text-white text-sm">{{ $ipData->geo_data['country'] ?? $ipData->geo_data['country_name'] ?? '-' }}</p>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">ISP / Org</h4>
                        <p class="text-white text-sm">{{ $ipData->geo_data['isp'] ?? $ipData->geo_data['org'] ?? '-' }}</p>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">AbuseIPDB</h4>
                        @php $abuseConf = $ipData->abuseipdb_data['abuseConfidenceScore'] ?? $ipData->abuseipdb_data['confidence_score'] ?? null; @endphp
                        <p class="text-white text-sm">{{ $abuseConf !== null ? $abuseConf . '% Confidence' : '-' }}</p>
                    </div>
                </div>

                @php
                    $vtData = $ipData->virustotal_data ?? [];
                    $vtStats = $vtData['last_analysis_stats'] ?? $vtData['stats'] ?? null;
                @endphp
                @if($vtStats)
                <div class="bg-slate-950 rounded-lg p-3 border border-slate-800">
                    <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">VirusTotal Detection</h4>
                    <div class="flex items-center gap-6 text-sm">
                        <span class="text-red-400 font-medium">Malicious: {{ $vtStats['malicious'] ?? 0 }}</span>
                        <span class="text-amber-400 font-medium">Suspicious: {{ $vtStats['suspicious'] ?? 0 }}</span>
                        <span class="text-emerald-400 font-medium">Clean: {{ $vtStats['harmless'] ?? 0 }}</span>
                        <span class="text-slate-400">Undetected: {{ $vtStats['undetected'] ?? 0 }}</span>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Affected Assets (General Incident) -->
        @if($mitigationLog->type === 'General' && ($mitigationLog->hostname || $mitigationLog->internal_ip))
        <div class="bg-slate-900/50 p-4 rounded-lg border border-slate-800">
             <div class="flex items-center gap-2 mb-3">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 01-2 2v4a2 2 0 012 2h14a2 2 0 012-2v-4a2 2 0 01-2-2m-2-4h.01M17 16h.01"></path></svg>
                <h4 class="text-sm font-bold text-blue-400 uppercase tracking-wider">Affected Asset Details</h4>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Hostname</h4>
                    <p class="text-white font-mono text-sm">{{ $mitigationLog->hostname ?? '-' }}</p>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Internal IP</h4>
                    <p class="text-white font-mono text-sm">{{ $mitigationLog->internal_ip ?? '-' }}</p>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">OS / Platform</h4>
                    <p class="text-white font-mono text-sm">{{ $mitigationLog->os ?? '-' }}</p>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Network Zone</h4>
                    <p class="text-white font-mono text-sm">{{ $mitigationLog->network_zone ?? '-' }}</p>
                </div>
            </div>
        </div>
        @endif

        @if($mitigationLog->type === 'Email Phishing')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-900/50 p-4 rounded-lg border border-slate-800">
            <div class="col-span-1 md:col-span-2">
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Email Subject</h4>
                <p class="text-white font-medium">{{ $mitigationLog->email_subject }}</p>
            </div>
            <div>
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Sender</h4>
                <p class="text-white font-mono text-sm">{{ $mitigationLog->email_sender }}</p>
            </div>
            <div>
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Recipient</h4>
                <p class="text-white font-mono text-sm">{{ $mitigationLog->email_recipient }}</p>
            </div>
            @if($mitigationLog->email_headers)
            <div class="col-span-1 md:col-span-2 mt-2">
                <div x-data="{ expanded: false }">
                    <button @click="expanded = !expanded" class="text-xs text-blue-400 hover:text-blue-300 flex items-center gap-1">
                        <span x-text="expanded ? 'Hide Headers' : 'Show Email Headers'"></span>
                        <svg class="w-3 h-3 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="expanded" x-transition class="mt-2 bg-black rounded p-3 border border-slate-800 overflow-x-auto">
                        <pre class="text-[10px] text-slate-400 font-mono">{{ $mitigationLog->email_headers }}</pre>
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endif

        @if($mitigationLog->type === 'File Check' && $mitigationLog->fileAnalysis)
        <div class="bg-teal-500/5 p-4 rounded-lg border border-teal-500/20">
            <div class="flex items-center gap-2 mb-3">
                <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <h4 class="text-sm font-bold text-teal-400 uppercase tracking-wider">File Analysis Reference</h4>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">File Name</h4>
                    <p class="text-white font-mono text-sm">{{ $mitigationLog->fileAnalysis->file_name }}</p>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">SHA-256 Hash</h4>
                    <p class="text-slate-300 font-mono text-xs break-all">{{ $mitigationLog->fileAnalysis->file_hash_sha256 }}</p>
                </div>
            </div>
            @if($mitigationLog->analysis_summary)
            <div class="mt-3 bg-slate-950 rounded-lg border border-slate-800 p-4">
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Analysis Verdict</h4>
                <pre class="text-sm text-teal-300 font-mono whitespace-pre-line">{{ $mitigationLog->analysis_summary }}</pre>
            </div>
            @endif
        </div>
        @endif

        @if($mitigationLog->type === 'Domain Check' && $mitigationLog->urlAnalysis)
        <div class="bg-purple-500/5 p-4 rounded-lg border border-purple-500/20">
            <div class="flex items-center gap-2 mb-3">
                <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                <h4 class="text-sm font-bold text-purple-400 uppercase tracking-wider">Domain / URL Analysis Reference</h4>
            </div>
            <div>
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">URL</h4>
                <p class="text-white font-mono text-sm break-all">{{ $mitigationLog->urlAnalysis->url }}</p>
            </div>
            @if($mitigationLog->analysis_summary)
            <div class="mt-3 bg-slate-950 rounded-lg border border-slate-800 p-4">
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Analysis Verdict</h4>
                <pre class="text-sm text-purple-300 font-mono whitespace-pre-line">{{ $mitigationLog->analysis_summary }}</pre>
            </div>
            @endif
        </div>
        @endif

        @if($mitigationLog->event_log)
        <div x-data="{ open: false }" class="border border-slate-800 rounded-lg bg-slate-900/30 overflow-hidden">
            <button @click="open = !open" class="w-full flex items-center justify-between p-4 cursor-pointer hover:bg-slate-800/50 transition-colors text-left focus:outline-none">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-500 transition-transform duration-200" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider">Event Log / Evidence</h3>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-[10px] bg-slate-800 text-slate-400 px-2 py-1 rounded border border-slate-700">RAW DATA</span>
                </div>
            </button>
            <div x-show="open" x-collapse x-transition class="bg-slate-950 border-t border-slate-800 p-4 overflow-x-auto">
                <pre class="text-xs text-emerald-400 font-mono leading-relaxed">{{ $mitigationLog->event_log }}</pre>
            </div>
        </div>
        @endif

        <!-- Evidence Images / Files -->
        <div>
            @if($mitigationLog->files->count() > 0)
            <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-4">Attachments & Evidence</h3>
            <div class="space-y-4 mb-8">
                {{-- Inline Image Previews --}}
                @php $imageFiles = $mitigationLog->files->filter(fn($f) => Str::startsWith($f->file_type, 'image/')); @endphp
                @php $otherFiles = $mitigationLog->files->filter(fn($f) => !Str::startsWith($f->file_type, 'image/')); @endphp

                @if($imageFiles->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($imageFiles as $file)
                    <div class="group relative rounded-xl overflow-hidden border border-slate-700/50 bg-slate-900/50">
                        <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank">
                            <img src="{{ asset('storage/' . $file->file_path) }}" alt="{{ $file->original_name }}" class="w-full h-auto object-contain max-h-[400px] bg-slate-950" loading="lazy">
                        </a>
                        <div class="px-3 py-2 bg-slate-900/80 border-t border-slate-800 flex items-center justify-between">
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-slate-300 truncate" title="{{ $file->original_name }}">{{ $file->original_name }}</p>
                                <p class="text-[10px] text-slate-500">{{ number_format($file->file_size / 1024, 2) }} KB</p>
                            </div>
                            <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="text-xs text-blue-400 hover:text-blue-300 flex items-center gap-1 shrink-0 ml-2">
                                Open
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Non-image files (PDF, EML, etc) --}}
                @if($otherFiles->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($otherFiles as $file)
                    <div class="group relative bg-slate-900 border border-slate-700/50 rounded-lg p-4 hover:border-blue-500/30 transition-all">
                        <div class="flex items-start gap-3">
                            <div class="shrink-0 w-10 h-10 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-blue-400 transition-colors">
                                @if($file->file_type === 'application/pdf')
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 2H7a2 2 0 00-2 2v15a2 2 0 002 2z"></path></svg>
                                @else
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-white truncate" title="{{ $file->original_name }}">{{ $file->original_name }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ number_format($file->file_size / 1024, 2) }} KB</p>
                            </div>
                        </div>
                        <div class="mt-3 flex items-center justify-end gap-2">
                            <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="text-xs text-blue-400 hover:text-blue-300 flex items-center gap-1">
                                View
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endif

            @if($mitigationLog->evidence_before || $mitigationLog->evidence_after)
            <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-4">Visual Evidence</h3>
            <!-- General: Before/After Images (Legacy) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if($mitigationLog->evidence_before)
                <div class="space-y-2">
                    <span class="text-xs font-semibold text-slate-400 uppercase">Before Mitigation/Fix</span>
                    <div class="group relative rounded-xl overflow-hidden border border-slate-700/50 bg-slate-900/50">
                        @if(Str::endsWith($mitigationLog->evidence_before, '.pdf'))
                            <div class="p-8 flex flex-col items-center justify-center text-slate-400">
                                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <span class="text-xs">PDF Document</span>
                                <a href="{{ asset('storage/' . $mitigationLog->evidence_before) }}" target="_blank" class="mt-4 px-4 py-2 bg-slate-800 hover:bg-slate-700 rounded text-xs text-white transition-colors">View Document</a>
                            </div>
                        @else
                            <img src="{{ asset('storage/' . $mitigationLog->evidence_before) }}" alt="Before" class="w-full h-auto object-cover group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <a href="{{ asset('storage/' . $mitigationLog->evidence_before) }}" target="_blank" class="px-4 py-2 bg-slate-900/80 rounded-lg text-white text-xs font-medium backdrop-blur-sm">View Fullscreen</a>
                            </div>
                        @endif
                    </div>
                </div>
                @endif

                @if($mitigationLog->evidence_after)
                <div class="space-y-2">
                    <span class="text-xs font-semibold text-emerald-500/80 uppercase">After Mitigation/Fix</span>
                    <div class="group relative rounded-xl overflow-hidden border border-emerald-500/20 bg-emerald-500/5">
                        @if(Str::endsWith($mitigationLog->evidence_after, '.pdf'))
                            <div class="p-8 flex flex-col items-center justify-center text-emerald-400">
                                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <span class="text-xs">PDF Document</span>
                                <a href="{{ asset('storage/' . $mitigationLog->evidence_after) }}" target="_blank" class="mt-4 px-4 py-2 bg-emerald-900/50 hover:bg-emerald-900/80 rounded text-xs text-emerald-100 transition-colors border border-emerald-500/30">View Document</a>
                            </div>
                        @else
                            <img src="{{ asset('storage/' . $mitigationLog->evidence_after) }}" alt="After" class="w-full h-auto object-cover group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute inset-0 bg-emerald-900/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <a href="{{ asset('storage/' . $mitigationLog->evidence_after) }}" target="_blank" class="px-4 py-2 bg-emerald-950/80 rounded-lg text-emerald-100 text-xs font-medium backdrop-blur-sm border border-emerald-500/30">View Fullscreen</a>
                            </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>

    <!-- Timeline Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Timeline List -->
        <div class="lg:col-span-2 space-y-6">
            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Investigation Activity Log
            </h3>

            <div class="relative border-l border-slate-800 ml-3 space-y-8 pb-4">
                @forelse($mitigationLog->details as $detail)
                <div class="relative pl-8">
                    <!-- Timeline Dot -->
                    <div class="absolute -left-1.5 top-1.5 w-3 h-3 rounded-full bg-slate-900 border-2 border-blue-500"></div>
                    
                    <div class="glass-panel p-4 rounded-lg border border-slate-800/50 hover:border-slate-700 transition-colors">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-white font-medium">{{ $detail->action }}</h4>
                            <span class="text-xs text-slate-500 font-mono">{{ $detail->log_date->format('M d, H:i') }}</span>
                        </div>
                        <p class="text-slate-400 text-sm mb-3">{{ $detail->description }}</p>
                        <div class="flex items-center gap-2 border-t border-white/5 pt-2">
                            <div class="w-5 h-5 rounded-full bg-slate-700 flex items-center justify-center text-[10px] text-white font-bold">
                                {{ substr($detail->user->name ?? '?', 0, 1) }}
                            </div>
                            <span class="text-xs text-slate-500">{{ $detail->user->name ?? 'Unknown' }}</span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="pl-8 text-slate-500 text-sm italic">No activity logs recorded yet.</div>
                @endforelse
            </div>
        </div>

        <!-- Add Detail Form -->
        <div class="lg:col-span-1">
            <div class="glass-panel p-5 rounded-xl border border-slate-800 sticky top-24">
                <h3 class="text-sm font-bold text-white mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Log Entry
                </h3>
                
                <form action="{{ route('mitigation-logs.details.store', $mitigationLog) }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Action</label>
                        <input type="text" name="action" required 
                            class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-all"
                            placeholder="e.g. Config Updated">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Description</label>
                        <textarea name="description" required rows="3"
                            class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-all resize-none"
                            placeholder="Details of action..."></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Date</label>
                        <input type="datetime-local" name="log_date" required
                            class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-all font-mono"
                            value="{{ now()->format('Y-m-d\TH:i') }}">
                    </div>

                    <button type="submit" class="w-full py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-lg shadow-lg shadow-blue-500/20 transition-all">
                        Add Record
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
