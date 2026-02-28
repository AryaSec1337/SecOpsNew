@extends('layouts.dashboard')

@section('content')
<div class="space-y-6 min-h-screen" x-data="{ view: 'grid', filter: 'all' }">

    <!-- Header with Global Stats -->
    <div class="glass-panel p-6 rounded-2xl relative overflow-hidden">
        <!-- ... (background blobs unchanged) ... -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-blue-600/10 rounded-full blur-3xl -mr-16 -mt-16 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-indigo-600/10 rounded-full blur-3xl -ml-12 -mb-12 pointer-events-none"></div>
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 relative z-10">
            <div>
                <h1 class="text-3xl font-black text-white tracking-tight uppercase flex items-center gap-3">
                    <span class="w-2 h-10 bg-blue-500 rounded-full"></span>
                    Security Reports
                </h1>
                <p class="text-slate-400 text-sm font-mono mt-1">Archive of AI-generated and manual security assessments</p>
            </div>
            
            <!-- Quick Stats & View Toggle -->
            <div class="flex gap-4 items-center">
                
                <!-- Status Filter -->
                <div class="hidden md:flex bg-slate-800/50 rounded-lg border border-slate-700 px-3 py-1">
                    <span class="text-[10px] items-center flex font-bold text-slate-500 uppercase mr-2 tracking-wider">Filter:</span>
                    <select x-model="filter" class="bg-transparent text-xs font-bold text-white border-none focus:ring-0 cursor-pointer py-1 pr-8 pl-0">
                        <option value="all" class="bg-slate-900 text-slate-400">All Status</option>
                        <option value="Draft" class="bg-slate-900 text-yellow-500">Draft</option>
                        <option value="Success Mitigasi" class="bg-slate-900 text-emerald-500">Success Mitigasi</option>
                    </select>
                </div>

                <div class="hidden md:flex bg-slate-800/50 p-1 rounded-lg border border-slate-700">
                    <button @click="view = 'grid'" :class="{'bg-blue-600 text-white shadow-lg': view === 'grid', 'text-slate-400 hover:text-white': view !== 'grid'}" class="p-2 rounded-md transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    </button>
                    <button @click="view = 'list'" :class="{'bg-blue-600 text-white shadow-lg': view === 'list', 'text-slate-400 hover:text-white': view !== 'list'}" class="p-2 rounded-md transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                </div>
                
                <div class="text-right hidden md:block">
                    <div class="text-2xl font-black text-white">{{ $reports->count() }}</div>
                    <div class="text-[10px] text-slate-500 uppercase font-bold">Total Reports</div>
                </div>
                <div class="w-px h-10 bg-slate-700 hidden md:block"></div>
                <a href="{{ route('reports.create') }}" class="px-5 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white rounded-xl font-bold shadow-lg shadow-blue-500/20 transition-all flex items-center gap-2 group">
                    <svg class="w-5 h-5 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    New Report
                </a>
            </div>
        </div>
    </div>



    <!-- Reports Content -->
    <div>
        <!-- GRID VIEW -->
        <div x-show="view === 'grid'" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 transition-all duration-300">
            @forelse($reports as $report)
                @php
                    $isAiScan = Str::startsWith($report->period, 'AI-SCAN');
                    $meta = $report->summary_json['meta'] ?? [];
                    $exec = $report->summary_json['executive'] ?? [];
                    $title = $meta['title'] ?? ($isAiScan ? 'AI Security Analysis' : 'Monthly Report');
                    $riskScore = $exec['risk_score'] ?? 'Unknown';
                    $summary = $exec['summary'] ?? 'No executive summary available.';
                    $status = $meta['status'] ?? 'Draft';
                    $tlp = $meta['tlp'] ?? 'WHITE';

                    // Calculate IoCs (Forensics + OTX)
                    $forensicIocs = collect($report->summary_json['forensics']['iocs'] ?? $report->summary_json['iocs'] ?? []);
                    $rawIntel = $report->summary_json['technical']['raw_intelligence'] ?? [];
                    
                    $otxIocs = collect();
                    foreach($rawIntel as $ipData) {
                        if(isset($ipData['alienvault']['pulses'])) {
                            foreach($ipData['alienvault']['pulses'] as $pulse) {
                                if(isset($pulse['indicators'])) {
                                    foreach($pulse['indicators'] as $ioc) {
                                        $otxIocs->push([
                                            'value' => $ioc['indicator'],
                                            'type' => $ioc['type'] ?? 'OTX'
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                    
                    // Merge and unique
                    $totalIocs = $forensicIocs->concat($otxIocs)->unique('value')->count();
                    $totalIps = count($rawIntel);
                    
                    // Risk color mapping
                    $riskColors = [
                        'Critical' => 'bg-red-500/20 text-red-400 border-red-500/30',
                        'High' => 'bg-orange-500/20 text-orange-400 border-orange-500/30',
                        'Medium' => 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30',
                        'Low' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
                        'Unknown' => 'bg-slate-500/20 text-slate-400 border-slate-500/30'
                    ];
                    $riskClass = $riskColors[$riskScore] ?? $riskColors['Unknown'];
                    
                    // TLP color mapping
                    $tlpColors = [
                        'RED' => 'bg-red-600 text-white',
                        'AMBER' => 'bg-amber-500 text-black',
                        'GREEN' => 'bg-emerald-600 text-white',
                        'WHITE' => 'bg-slate-100 text-slate-800'
                    ];
                    $tlpClass = $tlpColors[$tlp] ?? 'bg-slate-600 text-white';
                @endphp
                
                <div x-show="filter === 'all' || filter === '{{ $status }}'" class="glass-panel rounded-xl border border-slate-800 hover:border-blue-500/50 transition-all group relative overflow-hidden flex flex-col">
                    <!-- Hover glow effect -->
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-600/5 to-indigo-600/5 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>
                    
                    <!-- Card Header -->
                    <div class="p-5 pb-3 relative z-10">
                        <!-- Badges Row -->
                        <div class="flex items-center gap-2 mb-3">
                            @if($isAiScan)
                                <span class="px-2 py-0.5 bg-indigo-500/20 text-indigo-400 text-[10px] font-bold uppercase rounded-full border border-indigo-500/30 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93s3.05-7.44 7-7.93v15.86zm2-15.86c1.03.13 2 .45 2.87.93H13v-.93zM13 7h5.24c.25.31.48.65.68 1H13V7zm0 3h6.74c.08.33.15.66.19 1H13v-1zm0 3h6.93c-.04.34-.11.67-.19 1H13v-1zm0 3h5.92c-.2.35-.43.69-.68 1H13v-1zm0 3h2.87c-.87.48-1.84.8-2.87.93V19z"/></svg>
                                    AI Scan
                                </span>
                            @else
                                <span class="px-2 py-0.5 bg-blue-500/20 text-blue-400 text-[10px] font-bold uppercase rounded-full border border-blue-500/30">
                                    Manual
                                </span>
                            @endif
                            <span class="px-2 py-0.5 {{ $tlpClass }} text-[10px] font-bold uppercase rounded">
                                TLP:{{ $tlp }}
                            </span>
                            <span class="px-2 py-0.5 {{ $riskClass }} text-[10px] font-bold uppercase rounded border">
                                {{ $riskScore }}
                            </span>
                        </div>
                        
                        <!-- Title -->
                        <h3 class="text-lg font-bold text-white group-hover:text-blue-400 transition-colors line-clamp-2 leading-tight mb-1">
                            {{ Str::limit($title, 60) }}
                        </h3>
                        
                        <!-- Date -->
                        <div class="text-xs text-slate-500 font-mono flex items-center gap-2">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ $report->created_at->diffForHumans() }}
                            @if(!$isAiScan)
                                <span class="text-slate-600">•</span>
                                <span class="text-slate-400">{{ \Carbon\Carbon::createFromFormat('Y-m', $report->period)->format('F Y') }}</span>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Card Body - Summary -->
                    <div class="px-5 pb-4 flex-1 relative z-10">
                        <p class="text-sm text-slate-400 line-clamp-3 leading-relaxed">
                            {{ Str::limit($summary, 150) }}
                        </p>
                    </div>
                    
                    <!-- Card Footer - Stats & Action -->
                    <div class="p-5 pt-0 relative z-10">
                        <!-- Quick Stats Grid -->
                        <div class="grid grid-cols-3 gap-2 mb-4">
                            <div class="bg-black/30 p-2 rounded-lg border border-white/5 text-center">
                                <div class="text-[9px] text-slate-500 uppercase font-bold tracking-wider">IoCs</div>
                                <div class="text-lg font-black text-white">
                                    {{ $totalIocs }}
                                </div>
                            </div>
                            <div class="bg-black/30 p-2 rounded-lg border border-white/5 text-center">
                                <div class="text-[9px] text-slate-500 uppercase font-bold tracking-wider">IPs</div>
                                <div class="text-lg font-black text-blue-400">
                                    {{ $totalIps }}
                                </div>
                            </div>
                            <div class="bg-black/30 p-2 rounded-lg border border-white/5 text-center">
                                <div class="text-[9px] text-slate-500 uppercase font-bold tracking-wider">Status</div>
                                @php
                                    $statusColors = [
                                        'Success Mitigasi' => 'text-emerald-400',
                                        'Success Mitigation' => 'text-emerald-400',
                                        'Final' => 'text-blue-400',
                                        'Draft' => 'text-yellow-400',
                                        'Review' => 'text-orange-400'
                                    ];
                                    $statusColor = $statusColors[$status] ?? 'text-slate-400';
                                @endphp
                                <div class="text-sm font-bold {{ $statusColor }}">
                                    {{ $status }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Actions -->
                        <div class="flex gap-2">
                            <a href="{{ route('reports.show', $report->id) }}" class="flex-1 py-2.5 bg-slate-800 hover:bg-blue-600 text-slate-300 hover:text-white text-center rounded-lg font-bold transition-all border border-slate-700 hover:border-blue-500 flex items-center justify-center gap-2 group/btn">
                                <svg class="w-4 h-4 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                View
                            </a>
                            <a href="{{ route('reports.edit', $report->id) }}" class="px-4 py-2.5 bg-slate-800 hover:bg-yellow-600 text-slate-400 hover:text-white rounded-lg font-bold transition-all border border-slate-700 hover:border-yellow-500 flex items-center justify-center" title="Edit Report">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </a>
                            <a href="{{ route('reports.exportPdf', $report->id) }}" class="px-4 py-2.5 bg-slate-800 hover:bg-emerald-600 text-slate-400 hover:text-white rounded-lg font-bold transition-all border border-slate-700 hover:border-emerald-500 flex items-center justify-center" title="Export PDF">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </a>
                            <form action="{{ route('reports.destroy', $report->id) }}" method="POST" class="delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="confirmDelete(this)" class="px-4 py-2.5 bg-slate-800 hover:bg-red-600 text-slate-400 hover:text-white rounded-lg font-bold transition-all border border-slate-700 hover:border-red-500 flex items-center justify-center" title="Delete Report">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 text-center border border-dashed border-slate-800 rounded-2xl bg-slate-900/30">
                    <svg class="w-16 h-16 mx-auto text-slate-700 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <p class="text-slate-500 font-medium mb-4">No security reports generated yet</p>
                    <a href="{{ route('reports.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg font-bold transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Create Your First Report
                    </a>
                </div>
            @endforelse
        </div>

        <!-- TABLE VIEW -->
        <div x-show="view === 'list'" class="glass-panel rounded-2xl border border-slate-800 overflow-hidden" style="display: none;">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-black/30 border-b border-slate-700 text-xs uppercase text-slate-400 font-bold tracking-wider">
                            <th class="px-6 py-4">Title / Report ID</th>
                            <th class="px-6 py-4">Type</th>
                            <th class="px-6 py-4">Risk Level</th>
                            <th class="px-6 py-4">TLP</th>
                            <th class="px-6 py-4">Stats</th>
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @foreach($reports as $report)
                            @php
                                $isAiScan = Str::startsWith($report->period, 'AI-SCAN');
                                $meta = $report->summary_json['meta'] ?? [];
                                $exec = $report->summary_json['executive'] ?? [];
                                $title = $meta['title'] ?? ($isAiScan ? 'AI Security Analysis' : 'Monthly Report');
                                $riskScore = $exec['risk_score'] ?? 'Unknown';
                                $tlp = $meta['tlp'] ?? 'WHITE';

                                // Calculate IoCs (Forensics + OTX)
                                $forensicIocs = collect($report->summary_json['forensics']['iocs'] ?? $report->summary_json['iocs'] ?? []);
                                $rawIntel = $report->summary_json['technical']['raw_intelligence'] ?? [];
                                
                                $otxIocs = collect();
                                foreach($rawIntel as $ipData) {
                                    if(isset($ipData['alienvault']['pulses'])) {
                                        foreach($ipData['alienvault']['pulses'] as $pulse) {
                                            if(isset($pulse['indicators'])) {
                                                foreach($pulse['indicators'] as $ioc) {
                                                    $otxIocs->push([
                                                        'value' => $ioc['indicator']
                                                    ]);
                                                }
                                            }
                                        }
                                    }
                                }
                                $totalIocs = $forensicIocs->concat($otxIocs)->unique('value')->count();
                                $totalIps = count($rawIntel);
                                
                                // Risk color mapping
                                $riskColors = [
                                    'Critical' => 'bg-red-500/10 text-red-400 border-red-500/30',
                                    'High' => 'bg-orange-500/10 text-orange-400 border-orange-500/30',
                                    'Medium' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/30',
                                    'Low' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                                    'Unknown' => 'bg-slate-500/10 text-slate-400 border-slate-500/30'
                                ];
                                $riskClass = $riskColors[$riskScore] ?? $riskColors['Unknown'];
                                
                                $tlpColors = ['RED' => 'text-red-500', 'AMBER' => 'text-amber-500', 'GREEN' => 'text-emerald-500', 'WHITE' => 'text-slate-300'];
                                $tlpColor = $tlpColors[$tlp] ?? 'text-slate-500';

                                // Calculate IoCs (Forensics + OTX)
                                $forensicIocs = collect($report->summary_json['forensics']['iocs'] ?? $report->summary_json['iocs'] ?? []);
                                $rawIntel = $report->summary_json['technical']['raw_intelligence'] ?? [];
                                
                                $otxIocs = collect();
                                foreach($rawIntel as $ipData) {
                                    if(isset($ipData['alienvault']['pulses'])) {
                                        foreach($ipData['alienvault']['pulses'] as $pulse) {
                                            if(isset($pulse['indicators'])) {
                                                foreach($pulse['indicators'] as $ioc) {
                                                    $otxIocs->push([
                                                        'value' => $ioc['indicator']
                                                    ]);
                                                }
                                            }
                                        }
                                    }
                                }
                                $totalIocs = $forensicIocs->concat($otxIocs)->unique('value')->count();
                                $totalIps = count($rawIntel);
                            @endphp
                            <tr x-show="filter === 'all' || filter === '{{ $status }}'" class="hover:bg-slate-800/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-white group-hover:text-blue-400 transition-colors">{{ Str::limit($title, 40) }}</div>
                                    <div class="text-xs text-slate-500 font-mono">{{ $report->period }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($isAiScan)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93s3.05-7.44 7-7.93v15.86zm2-15.86c1.03.13 2 .45 2.87.93H13v-.93zM13 7h5.24c.25.31.48.65.68 1H13V7zm0 3h6.74c.08.33.15.66.19 1H13v-1zm0 3h6.93c-.04.34-.11.67-.19 1H13v-1zm0 3h5.92c-.2.35-.43.69-.68 1H13v-1zm0 3h2.87c-.87.48-1.84.8-2.87.93V19z"/></svg>
                                            AI Scan
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                            Manual
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded text-xs font-bold border {{ $riskClass }}">
                                        {{ $riskScore }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-xs font-bold {{ $tlpColor }}">TLP:{{ $tlp }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex gap-3 text-xs font-mono text-slate-400">
                                        <div title="IoCs"><span class="text-white">{{ $totalIocs }}</span> IoCs</div>
                                        <div title="IPs"><span class="text-blue-400">{{ $totalIps }}</span> IPs</div>
                                    </div>
                                    @php
                                        $statusColors = [
                                            'Success Mitigasi' => 'text-emerald-400',
                                            'Success Mitigation' => 'text-emerald-400',
                                            'Final' => 'text-blue-400',
                                            'Draft' => 'text-yellow-400',
                                            'Review' => 'text-orange-400'
                                        ];
                                        $statusColor = $statusColors[$status] ?? 'text-slate-400';
                                    @endphp
                                    <div class="mt-1 text-[10px] font-bold {{ $statusColor }} uppercase">{{ $status }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-400">
                                    {{ $report->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a href="{{ route('reports.show', $report->id) }}" class="p-2 text-slate-400 hover:text-white hover:bg-slate-700 rounded transition-colors" title="View">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </a>
                                        <a href="{{ route('reports.edit', $report->id) }}" class="p-2 text-slate-400 hover:text-yellow-400 hover:bg-slate-700 rounded transition-colors" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>
                                        <a href="{{ route('reports.exportPdf', $report->id) }}" class="p-2 text-slate-400 hover:text-emerald-400 hover:bg-slate-700 rounded transition-colors" title="Download PDF">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        </a>
                                        <form action="{{ route('reports.destroy', $report->id) }}" method="POST" class="delete-form inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="confirmDelete(this)" class="p-2 text-slate-400 hover:text-red-400 hover:bg-slate-700 rounded transition-colors" title="Delete">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
    function confirmDelete(button) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            background: '#0f172a',
            color: '#cbd5e1',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#334155',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                button.closest('form').submit();
            }
        })
    }
</script>
@endsection
