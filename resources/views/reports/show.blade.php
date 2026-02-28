@extends('layouts.dashboard')

@section('header', 'Security Report: ' . (is_array($report) ? $report['period'] : $report->period))

@php
    $isReportArray = is_array($report);
    $reportData = $isReportArray ? $report : $report; 
    
    // Normalize Data
    $summary = $isReportArray ? ($report['summary_json'] ?? []) : ($report->summary_json ?? []);
    if (is_string($summary)) {
        $summary = json_decode($summary, true) ?? [];
    }

    $reportId = $isReportArray ? ($report['id'] ?? '') : $report->id;
    $reportPeriod = $isReportArray ? ($report['period'] ?? '') : $report->period;
    $createdAtRaw = $isReportArray ? ($report['created_at'] ?? now()) : $report->created_at;
    $createdAt = \Carbon\Carbon::parse($createdAtRaw);

    $riskScore = $summary['executive']['risk_score'] ?? 'Low';
    $riskColor = match($riskScore) {
        'Critical' => 'red',
        'High' => 'orange',
        'Medium' => 'yellow',
        'Low' => 'emerald',
        default => 'slate'
    };

    // Unified Intel Sources
    $intelSources = collect([]);
    if (isset($socData) && is_countable($socData) && count($socData) > 0) {
        $intelSources = $socData;
    } elseif (!empty($summary['raw_data'])) {
        foreach ($summary['raw_data'] as $ip => $data) {
            $obj = new \stdClass();
            $obj->ip_address = $ip;
            $obj->risk_score = $data['risk_score'] ?? 0;
            $obj->ip_info = $data['ip_info'] ?? [];
            $obj->greynoise = $data['greynoise'] ?? [];
            $obj->virustotal = $data['virustotal'] ?? [];
            $obj->abuseipdb = $data['abuseipdb'] ?? [];
            $obj->alienvault = $data['alienvault'] ?? [];
            $intelSources->push($obj);
        }
    } 
    
    // FALLBACK: Legacy IOCs (If no Raw Data exists)
    if ($intelSources->isEmpty()) {
         $legacyIocs = $summary['forensics']['iocs'] ?? $summary['iocs'] ?? [];
         foreach ($legacyIocs as $ioc) {
             if (($ioc['type'] ?? '') === 'IPv4') {
                 $obj = new \stdClass();
                 $obj->ip_address = $ioc['value'];
                 $obj->risk_score = 'N/A'; // Legacy data has no score
                 $obj->ip_info = ['org' => 'Legacy Report Data', 'country' => 'Unknown'];
                 $obj->greynoise = [];
                 $obj->virustotal = [];
                 $obj->abuseipdb = []; // Empty, will show "No Data"
                 $obj->alienvault = [];
                 $intelSources->push($obj);
             }
         }
    }
    // FALLBACK: Construct from IOCs if no detailed data found
    if ($intelSources->isEmpty()) {
         // Support both new structure (forensics.iocs) and legacy structure (iocs)
         $legacyIocs = $summary['forensics']['iocs'] ?? $summary['iocs'] ?? [];
         
         foreach ($legacyIocs as $ioc) {
             // Extract IP from value if type is IPv4, or if it looks like an IP
             $ip = null;
             if (($ioc['type'] ?? '') === 'IPv4') {
                 $ip = $ioc['value'];
             } elseif (filter_var($ioc['value'] ?? '', FILTER_VALIDATE_IP)) {
                 $ip = $ioc['value'];
             }

             if ($ip) {
                 $cleanIp = trim($ip);
                 // Try to check if we have data in the socData variable that was passed but maybe not linked? 
                 // Actually, if we are here, socData was empty or failed.
                 
                 $obj = new \stdClass();
                 $obj->ip_address = $cleanIp;
                 $obj->risk_score = 'N/A'; 
                 // Mark as "Legacy/Basic" so user knows why it's less detailed
                 $obj->ip_info = ['org' => 'Basic Report Data', 'country' => 'Unknown', 'asn' => 'N/A'];
                 $obj->greynoise = [];
                 $obj->virustotal = [
                     'last_analysis_stats' => ['malicious' => 0, 'suspicious' => 0], 
                     'last_analysis_results' => []
                 ];
                 $obj->abuseipdb = ['abuseConfidenceScore' => 0, 'reports' => []];
                 $obj->alienvault = ['pulses' => []];
                 
                 // Push to sources so the card renders
                 $intelSources->push($obj);
             }
         }
    }
@endphp

@section('content')
<style>
    /* CRITICAL VISIBILITY CONTROLS */
    .web-only { display: block !important; }
    .pdf-only { display: none !important; }
    
    @media print {
        /* 1. HIDE WEB ELEMENTS STRONGLY */
        .web-only, .no-print, header, nav, aside, .animate-pulse { 
            display: none !important; 
        }

        /* 2. SHOW PDF ELEMENTS STRONGLY */
        .pdf-only { 
            display: block !important; 
        }
        
        /* 3. NUCLEAR COLOR RESET (Overrides Dark Mode) */
        *, *::before, *::after {
            background-color: white !important; /* Force White Background */
            color: black !important; /* Force Black Text */
            box-shadow: none !important;
            text-shadow: none !important;
            border-color: #000 !important; /* Force Black Borders */
            background-image: none !important; /* Remove Gradients */
        }

        /* 4. EXCEPTIONS */
        img { 
            filter: none !important; /* Don't invert images */
        }
        
        /* 5. TABLE SPECIFICS */
        table.formal-table th {
            background-color: #f0f0f0 !important; /* Restore Grey Header */
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* 6. PAGE SETUP */
        @page { margin: 1.5cm; size: A4; }
        body { 
            visibility: visible !important; 
            margin: 0; 
            padding: 0;
            font-family: Georgia, "Times New Roman", serif !important;
        }
    }
</style>
<div class="max-w-[1600px] mx-auto space-y-8 font-sans text-slate-300 animate-fade-in print:text-black print:space-y-4">

    <!-- PRINT HEADER (Mega Insurance) -->
    <div class="hidden print:flex flex-row justify-between items-center border-b-4 border-slate-900 pb-6 mb-8">
        <div class="flex items-center gap-6">
            <img src="https://upload.wikimedia.org/wikipedia/commons/3/3f/Mega_Insurance_Logo.png" class="h-20 w-auto object-contain" alt="Mega Insurance">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 uppercase tracking-widest leading-none">IT Security</h1>
                <p class="text-lg text-slate-600 font-bold uppercase tracking-wide">Mega Insurance</p>
            </div>
        </div>
        <div class="text-right">
            <div class="text-xs text-slate-500 uppercase font-bold tracking-widest mb-1">Incident Report Ref</div>
            <div class="text-xl font-mono font-bold text-slate-900">{{ $reportPeriod }}-{{ $reportId }}</div>
            <div class="text-sm text-slate-600 mt-1 font-medium">{{ $createdAt->format('d F Y, H:i') }}</div>
        </div>
    </div>

    <!-- DIGITAL HERO (Hidden on Print) -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl overflow-hidden relative transition-all hover:shadow-2xl duration-500 stagger-1 print:hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-{{ $riskColor }}-500 to-{{ $riskColor }}-600"></div>
        <div class="p-8 md:p-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <span class="px-3 py-1 bg-{{ $riskColor }}-500/10 text-{{ $riskColor }}-600 dark:text-{{ $riskColor }}-400 text-[11px] font-bold uppercase tracking-widest rounded-full animate-pulse">
                        TLP:{{ $summary['meta']['tlp'] ?? 'WHITE' }}
                    </span>
                    <span class="text-[11px] text-slate-500 font-mono uppercase tracking-wider">
                        REPORT ID: {{ $reportPeriod }}-{{ $reportId }}
                    </span>
                </div>
                <div>
                    <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-2">
                        {{ $summary['meta']['title'] ?? 'Security Incident Report' }}
                    </h1>
                    <div class="flex items-center gap-6 text-sm text-slate-500">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            {{ $createdAt->format('F d, Y \a\t H:i') }}
                        </span>
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            {{ $summary['meta']['author_role'] ?? 'Automated System' }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex flex-col items-end gap-6 no-print">
                <div class="flex items-center gap-3">
                    <a href="{{ route('reports.edit', $reportId) }}" class="px-4 py-2 bg-yellow-500/10 hover:bg-yellow-500/20 text-yellow-600 dark:text-yellow-400 rounded-lg text-xs font-bold uppercase tracking-wide flex items-center gap-2 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Edit Report
                    </a>
                    <a href="{{ route('reports.exportPdf', $reportId) }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-lg text-xs font-bold uppercase tracking-wide flex items-center gap-2 transition-colors" target="_blank">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Export PDF
                    </a>

                </div>
                <div class="flex items-center gap-3 px-6 py-3 bg-{{ $riskColor }}-50 dark:bg-{{ $riskColor }}-900/10 rounded-xl border border-{{ $riskColor }}-100 dark:border-{{ $riskColor }}-900/30 animate-slide-in-right">
                    <span class="text-3xl font-black text-{{ $riskColor }}-600 dark:text-{{ $riskColor }}-400">{{ strtoupper($riskScore) }}</span>
                    <div class="h-8 w-[1px] bg-{{ $riskColor }}-200 dark:bg-{{ $riskColor }}-800"></div>
                     <span class="text-[10px] uppercase font-bold text-slate-400 tracking-widest leading-tight text-right w-16">Risk<br>Level</span>
                </div>
            </div>
        </div>
    </div>



    <!-- MAIN GRID -->
    <div class="grid grid-cols-12 gap-8 print:block">
        
        <!-- LEFT CONTENT -->
        <div class="col-span-12 lg:col-span-8 space-y-8 print:w-full print:mb-8">
            
            <!-- EXECUTIVE SUMMARY -->
            <section class="bg-white dark:bg-slate-900 rounded-2xl p-8 border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden transition-all hover:translate-y-[-2px] hover:shadow-lg duration-500 stagger-2 print:shadow-none print:border-none print:p-0 print:mb-8">
                <div class="no-print absolute top-0 right-0 p-6 opacity-5 pointer-events-none">
                     <svg class="w-24 h-24 text-slate-900 dark:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                </div>
                
                <h2 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2 print:text-slate-900 print:text-lg print:border-b print:border-slate-300 print:pb-2">
                    <span class="w-2 h-2 rounded-full bg-blue-500 animate-ping-slow no-print"></span> Executive Summary
                </h2>

                <div class="prose prose-lg prose-slate dark:prose-invert max-w-none print:prose-p:text-justify print:text-sm">
                    {!! Str::markdown($summary['executive']['summary'] ?? 'Executive summary not available.') !!}
                </div>

                @if(!empty($summary['executive']['impact_analysis']))
                    <div class="mt-8 p-5 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 rounded-r-lg animate-fade-in-delayed print:bg-white print:border-none print:p-0">
                        <h4 class="text-xs font-bold text-red-700 dark:text-red-400 uppercase mb-2 print:text-slate-900">Business Impact</h4>
                        <div class="text-sm text-red-800 dark:text-red-200 prose prose-sm prose-red dark:prose-invert print:text-slate-700">
                            {!! Str::markdown($summary['executive']['impact_analysis']) !!}
                        </div>
                    </div>
                @endif
                
                @if(isset($summary['recommendations']))
                    <div class="mt-8 no-break-inside">
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white mb-3 print:text-lg print:uppercase print:mt-6">Strategic Recommendations</h4>
                         <div class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-xl border border-slate-100 dark:border-slate-800 print:bg-white print:border-l-4 print:border-slate-300 print:p-4">
                             <div class="prose prose-sm prose-slate dark:prose-invert max-w-none print:text-sm">
                                {!! Str::markdown($summary['recommendations']) !!}
                             </div>
                         </div>
                    </div>
                @endif
            </section>

            <!-- TECHNICAL ANALYSIS -->
            <section class="bg-white dark:bg-slate-900 rounded-2xl p-8 border border-slate-200 dark:border-slate-800 shadow-sm transition-all hover:translate-y-[-2px] hover:shadow-lg duration-500 stagger-3 print:shadow-none print:border-none print:p-0 print:mb-8">
                <h2 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2 print:text-slate-900 print:text-lg print:border-b print:border-slate-300 print:pb-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping-slow no-print"></span> Technical Findings
                </h2>

                <div class="prose prose-slate dark:prose-invert max-w-none mb-8 print:text-sm print:text-justify">
                    {!! Str::markdown($summary['technical']['analysis'] ?? 'Technical analysis detailed below.') !!}
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 dark:bg-black/20 p-6 rounded-xl border border-slate-100 dark:border-slate-800 print:bg-white print:border print:border-slate-300">
                    <div>
                        <div class="text-xs font-bold text-slate-400 uppercase mb-2 print:text-slate-900">Root Cause (Hypothesis)</div>
                        <div class="font-mono text-sm text-slate-800 dark:text-slate-200">
                             {{ $summary['technical']['root_cause'] ?? $summary['root_cause'] ?? 'Pending Analysis' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-slate-400 uppercase mb-2 print:text-slate-900">MITRE ATT&CK Mapping</div>
                        <div class="flex flex-wrap gap-2">
                             {{-- Detailed Techniques --}}
                             @if(!empty($summary['technical']['mitre_techniques']))
                                 @foreach($summary['technical']['mitre_techniques'] as $tech)
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-bold font-mono bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 border border-purple-100 dark:border-purple-500/30 print:border-slate-400 print:bg-white print:text-slate-800">
                                        {{ $tech }}
                                    </span>
                                 @endforeach
                             {{-- Fallback to Tactics --}}
                             @elseif(!empty($summary['technical']['mitre_tactics']))
                                 @foreach($summary['technical']['mitre_tactics'] as $tactic)
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-500/30 print:border-slate-400 print:bg-white print:text-slate-800">
                                        {{ $tactic }}
                                    </span>
                                 @endforeach
                             @else
                                <span class="text-xs text-slate-400 italic">None determined</span>
                             @endif
                        </div>
                    </div>
                </div>
            </section>

            <!-- THREAT INTELLIGENCE (UNIFIED) -->
            @if($intelSources->count() > 0)
            <section class="space-y-6 stagger-4 print:space-y-8">
                <div class="flex items-center justify-between px-2 print:px-0">
                    <h2 class="text-sm font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2 print:text-slate-900 print:text-lg print:border-b print:border-slate-300 print:pb-2 print:w-full">
                        <span class="w-2 h-2 rounded-full bg-purple-500 animate-ping-slow no-print"></span> Threat Intelligence Detail
                        <span class="no-print text-xs text-slate-500 ml-auto">{{ $intelSources->count() }} Public IPs Analyzed</span>
                    </h2>
                </div>

                <div class="space-y-6 print:block">
                    @foreach($intelSources as $intel)
                        <!-- WEB LAYOUT (Screen Only) -->
                        <div class="web-only bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden transition-all hover:shadow-lg hover:scale-[1.01] duration-300">
                            <!-- ... content ... -->
                            <!-- Header -->
                            <div class="bg-slate-50 dark:bg-slate-800/50 px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center group cursor-pointer print:bg-slate-100 print:border-slate-300">
                                <div class="flex items-center gap-3">
                                    <span class="font-mono text-lg font-bold text-slate-800 dark:text-white">{{ $intel->ip_address }}</span>
                                    @if(isset($intel->ip_info['country']))
                                        <span class="text-xs px-2 py-1 bg-white dark:bg-slate-700 rounded border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-300 print:border-slate-400">
                                            {{ $intel->ip_info['country'] }}
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                     <div class="text-[10px] uppercase font-bold text-slate-400">Threat Score</div>
                                     <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold {{ $intel->risk_score > 70 ? 'bg-red-100 text-red-600 dark:bg-red-900/50 dark:text-red-400' : 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400' }} print:bg-white print:border print:border-slate-400 print:text-black">
                                         {{ $intel->risk_score }}
                                     </div>
                                </div>
                            </div>
                            
                            <!-- Body -->
                            <div class="p-6 space-y-8">
                                <!-- Row 1 -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 print:block print:space-y-4">
                                    <!-- Identity -->
                                    <div class="space-y-6">
                                        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-800 print:bg-white print:border-slate-300">
                                            <div class="text-[10px] uppercase font-bold text-slate-400 mb-3 flex items-center gap-2 print:text-slate-900">Network Identity</div>
                                            <div class="space-y-2 text-sm">
                                                <div class="flex justify-between border-b border-slate-100 dark:border-slate-800 pb-1">
                                                    <span class="text-slate-500">Organization</span>
                                                    <span class="text-slate-800 dark:text-slate-200 font-bold text-right">{{ $intel->ip_info['org'] ?? 'Unknown' }}</span>
                                                </div>
                                                <div class="flex justify-between border-b border-slate-100 dark:border-slate-800 pb-1">
                                                    <span class="text-slate-500">ASN</span>
                                                    <span class="text-slate-800 dark:text-slate-200 font-mono text-right">{{ $intel->ip_info['asn'] ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Detections -->
                                    <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-800 print:bg-white print:border-slate-300">
                                        <div class="flex justify-between items-center mb-4">
                                            <div class="text-[10px] uppercase font-bold text-slate-400 print:text-slate-900">Detections</div>
                                            <div class="text-xs font-bold text-slate-700 dark:text-slate-300">
                                                VT: {{ $intel->virustotal['last_analysis_stats']['malicious'] ?? 0 }} | Abuse: {{ $intel->abuseipdb['abuseConfidenceScore'] ?? 0 }}%
                                            </div>
                                        </div>
                                        <div class="space-y-1 max-h-48 overflow-y-auto custom-scrollbar pr-1 print:max-h-none print:overflow-visible">
                                            @php $vtDetections = 0; @endphp
                                            @if(isset($intel->virustotal['last_analysis_results']))
                                                @foreach($intel->virustotal['last_analysis_results'] as $vendor => $res)
                                                    @if(in_array($res['category'], ['malicious', 'suspicious']))
                                                        @php $vtDetections++; @endphp
                                                        <div class="text-[10px] flex justify-between items-center bg-white dark:bg-slate-900 px-3 py-2 rounded border border-red-100 dark:border-red-900/30 print:bg-white print:border-slate-300">
                                                            <span class="font-bold text-slate-700 dark:text-slate-300">{{ $vendor }}</span>
                                                            <span class="text-red-500 uppercase tracking-wider text-[9px]">{{ $res['result'] }}</span>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            @endif
                                            @if($vtDetections === 0)
                                                 <div class="text-center py-4 text-xs text-slate-400 italic">No threats requested from supported vendors.</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 2: AbuseIPDB -->
                                @if(isset($intel->abuseipdb['reports']) && count($intel->abuseipdb['reports']) > 0)
                                <div class="border-t border-slate-100 dark:border-slate-800 pt-6 print:border-slate-300">
                                    <div class="flex justify-between items-center mb-4">
                                        <div class="text-[10px] uppercase font-bold text-slate-500 tracking-widest print:text-black">AbuseIPDB Reports</div>
                                    </div>
                                    <div class="bg-slate-50 dark:bg-slate-800/30 rounded-xl border border-slate-100 dark:border-slate-800 overflow-hidden print:overflow-visible print:bg-white print:border-slate-300">
                                        <div class="max-h-60 overflow-y-auto custom-scrollbar divide-y divide-slate-100 dark:divide-slate-800 print:max-h-none print:overflow-visible">
                                            @foreach($intel->abuseipdb['reports'] as $report)
                                                <div class="p-3 text-[11px] hover:bg-white dark:hover:bg-slate-700/50 transition-colors grid grid-cols-12 gap-4 items-start print:grid-cols-6">
                                                    <div class="col-span-3 md:col-span-2 text-slate-500 font-mono text-[10px]">
                                                        {{ \Carbon\Carbon::parse($report['reportedAt'])->format('Y-m-d') }}
                                                    </div>
                                                    <div class="col-span-7 md:col-span-9 print:col-span-4 text-slate-600 dark:text-slate-300 font-mono leading-relaxed bg-white dark:bg-slate-900/50 p-2 rounded border border-slate-100 dark:border-slate-800 print:border-none print:p-0">
                                                        {{ $report['comment'] }}
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- Row 3: OTX (Restored Rich Detail) -->
                                @if(isset($intel->alienvault['pulses']) && count($intel->alienvault['pulses']) > 0)
                                    <div class="border-t border-slate-100 dark:border-slate-800 pt-6 print:border-slate-300">
                                        <div class="text-[10px] uppercase font-bold text-slate-500 mb-4 tracking-widest print:text-black">OTX Pulses</div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 print:grid-cols-2">
                                            @foreach($intel->alienvault['pulses'] as $pulse)
                                                <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 print:border-slate-300 break-inside-avoid shadow-sm hover:shadow-md transition-all relative group">
                                                    <!-- TLP Badge -->
                                                    <div class="absolute top-3 right-3">
                                                         <span class="text-[9px] font-bold px-2 py-0.5 rounded {{ ($pulse['TLP']??'white') == 'red' ? 'bg-red-100 text-red-600' : 'bg-slate-100 text-slate-500' }}">
                                                             {{ strtoupper($pulse['TLP'] ?? 'WHITE') }}
                                                         </span>
                                                    </div>

                                                    <div class="pr-12">
                                                        <a href="https://otx.alienvault.com/pulse/{{ $pulse['id'] }}" target="_blank" class="text-sm font-bold text-slate-800 dark:text-white hover:text-blue-500 line-clamp-1 mb-1" title="{{ $pulse['name'] }}">
                                                            {{ $pulse['name'] }}
                                                        </a>
                                                        <div class="text-[10px] text-slate-500 mb-3 text-xs w-full">
                                                            {{ \Carbon\Carbon::parse($pulse['created'])->format('M d, Y') }}
                                                        </div>
                                                    </div>
                                                    
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400 mb-4 line-clamp-2 print:line-clamp-none h-auto leading-relaxed">
                                                {{ $pulse['description'] ?? 'No description.' }}
                                            </div>
                                            
                                            <!-- Tags -->
                                            <div class="flex flex-wrap gap-1 mb-3">
                                                @foreach($pulse['tags'] ?? [] as $tag)
                                                    <span class="text-[9px] bg-slate-50 dark:bg-slate-800 text-slate-500 px-1.5 py-0.5 rounded border border-slate-100 dark:border-slate-700 print:border-slate-400">{{ $tag }}</span>
                                                @endforeach
                                            </div>

                                            <!-- Indicators Preview (Full List for Print) -->
                                            @if(isset($pulse['indicators']) && count($pulse['indicators']) > 0)
                                                <div class="pt-2 border-t border-slate-100 dark:border-slate-800 print:border-slate-300">
                                                    <div class="text-[9px] font-bold text-slate-400 uppercase mb-1">Associated IOCs ({{ count($pulse['indicators']) }})</div>
                                                    <div class="space-y-1">
                                                        @foreach(array_slice($pulse['indicators'], 0, 10) as $ioc)
                                                            <div class="flex justify-between text-[10px] font-mono text-slate-500 dark:text-slate-400">
                                                                <span class="text-yellow-600 dark:text-yellow-500 opacity-75">{{ $ioc['type'] }}</span>
                                                                <span class="truncate ml-2 max-w-[150px] print:max-w-none print:whitespace-normal">{{ $ioc['indicator'] }}</span>
                                                            </div>
                                                        @endforeach
                                                        @if(count($pulse['indicators']) > 10)
                                                             <div class="text-[9px] text-slate-400 italic text-center pt-1">...and {{ count($pulse['indicators']) - 10 }} more indicators</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                        </div>

                        <!-- FORMAL PRINT LAYOUT (PDF Only) -->
                        <div class="pdf-only mb-8 break-inside-avoid border-b border-black pb-4">
                            <!-- IP Header -->
                            <h3 class="text-xl font-bold border-b-2 border-black mb-4 pb-1">
                                {{ $intel->ip_address }} 
                                <span class="text-sm font-normal ml-2">({{ $intel->ip_info['country'] ?? 'Unknown' }})</span>
                                <span class="float-right text-sm">Risk Score: {{ $intel->risk_score }}</span>
                            </h3>

                            <!-- Identity Table -->
                            <table class="formal-table">
                                <tr>
                                    <th width="20%">Organization</th>
                                    <td>{{ $intel->ip_info['org'] ?? 'Unknown' }}</td>
                                    <th width="15%">ASN</th>
                                    <td>{{ $intel->ip_info['asn'] ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Geo Location</th>
                                    <td>{{ $intel->ip_info['city'] ?? '-' }}, {{ $intel->ip_info['region'] ?? '-' }}</td>
                                    <th>Coordinates</th>
                                    <td>{{ $intel->ip_info['loc'] ?? 'N/A' }}</td>
                                </tr>
                            </table>

                            <!-- AbuseIPDB Table -->
                            @if(isset($intel->abuseipdb['reports']) && count($intel->abuseipdb['reports']) > 0)
                                <h4 class="text-sm font-bold uppercase mt-4 mb-2">AbuseIPDB Reports ({{ count($intel->abuseipdb['reports']) }})</h4>
                                <table class="formal-table">
                                    <thead>
                                        <tr>
                                            <th width="15%">Date</th>
                                            <th>Comment</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($intel->abuseipdb['reports'] as $report)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($report['reportedAt'])->format('Y-m-d') }}</td>
                                            <td>{{ $report['comment'] }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif

                            <!-- VirusTotal Table -->
                            @if(isset($intel->virustotal['last_analysis_results']) && count($intel->virustotal['last_analysis_results']) > 0)
                                <h4 class="text-sm font-bold uppercase mt-4 mb-2">VirusTotal Detections</h4>
                                <table class="formal-table">
                                    <thead>
                                        <tr>
                                            <th width="20%">Vendor</th>
                                            <th width="20%">Result</th>
                                            <th>Details</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $vtCount = 0; @endphp
                                        @foreach($intel->virustotal['last_analysis_results'] as $vendor => $res)
                                            @if(in_array($res['category'], ['malicious', 'suspicious']))
                                            <tr>
                                                <td>{{ $vendor }}</td>
                                                <td class="text-red-700 font-bold uppercase">{{ $res['result'] }}</td>
                                                <td>{{ $res['category'] }}</td>
                                            </tr>
                                            @php $vtCount++; @endphp
                                            @endif
                                        @endforeach
                                        @if($vtCount === 0)
                                            <tr><td colspan="3" class="text-center italic">No malicious flags found.</td></tr>
                                        @endif
                                    </tbody>
                                </table>
                            @endif
                            
                            <!-- OTX Highlights -->
                            @if(isset($intel->alienvault['pulses']) && count($intel->alienvault['pulses']) > 0)
                                <h4 class="text-sm font-bold uppercase mt-4 mb-2">OTX Threat Pulses</h4>
                                <ul style="list-style-type: disc; margin-left: 1.5em; font-size: 10pt;">
                                    @foreach($intel->alienvault['pulses'] as $pulse)
                                        <li style="margin-bottom: 0.5em;">
                                            <strong>{{ $pulse['name'] }}</strong> ({{ \Carbon\Carbon::parse($pulse['created'])->format('Y-m-d') }})
                                            <br>
                                            <span style="font-style: italic;">{{ $pulse['description'] ?? 'No description' }}</span>
                                            @if(isset($pulse['indicators']) && count($pulse['indicators']) > 0)
                                                <div style="margin-top: 4px; font-size: 9pt; color: #444;">
                                                    <strong>Indicators:</strong> 
                                                    @foreach(array_slice($pulse['indicators'], 0, 5) as $ioc)
                                                        {{ $ioc['indicator'] }} ({{ $ioc['type'] }}), 
                                                    @endforeach
                                                    @if(count($pulse['indicators']) > 5) ... @endif
                                                </div>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
            @endif
        </div>

        <!-- RIGHT SIDEBAR -->
        <div class="col-span-12 lg:col-span-4 space-y-8 stagger-5 print:w-full print:mt-4 print:break-before-page">


            <!-- IOCs Card -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm max-h-[600px] flex flex-col print:max-h-none print:shadow-none print:border print:border-slate-300">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest print:text-black">Indicators (IoCs)</h3>
                </div>
                <div class="overflow-y-auto flex-1 space-y-2 pr-1 custom-scrollbar print:overflow-visible">
                     @php
                        $allIocs = collect($summary['forensics']['iocs'] ?? $summary['iocs'] ?? []);
                        
                        // Merge OTX Indicators from all Intel Sources
                        if(isset($intelSources)) {
                            foreach($intelSources as $intel) {
                                if(isset($intel->alienvault['pulses'])) {
                                    foreach($intel->alienvault['pulses'] as $pulse) {
                                        if(isset($pulse['indicators'])) {
                                            foreach($pulse['indicators'] as $ioc) {
                                                $allIocs->push([
                                                    'type' => $ioc['type'] ?? 'OTX IOC',
                                                    'value' => $ioc['indicator'],
                                                    'description' => $pulse['name'] ?? 'OTX Pulse'
                                                ]);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        
                        // Unique by value to avoid duplicates
                        $allIocs = $allIocs->unique('value')->values();
                     @endphp

                     @forelse($allIocs as $ioc)
                        <div class="p-3 bg-slate-50 dark:bg-slate-800 rounded-lg border border-slate-100 dark:border-slate-700 print:bg-white print:border-slate-300 print:break-inside-avoid">
                             <div class="flex justify-between mb-1">
                                 <span class="text-[10px] font-bold text-slate-500 uppercase">{{ $ioc['type'] ?? 'Unknown' }}</span>
                             </div>
                             <div class="font-mono text-xs text-slate-700 dark:text-slate-300 break-all">{{ $ioc['value'] ?? $ioc['indicator'] ?? 'N/A' }}</div>
                        </div>
                     @empty
                        <div class="text-center py-4 text-xs text-slate-400">No IoCs found</div>
                     @endforelse
                </div>
            </div>

            <!-- Evidence Artifacts -->
            @if(!empty($summary['forensics']['artifacts']))
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm print:shadow-none print:border print:border-slate-300">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest print:text-black">Evidence Artifacts</h3>
                </div>
                <div class="space-y-3">
                     @foreach($summary['forensics']['artifacts'] as $artifact)
                        @php
                            $extension = pathinfo($artifact['name'], PATHINFO_EXTENSION);
                            $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                            $url = Storage::url($artifact['path']);
                        @endphp
                        <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800 rounded-lg border border-slate-100 dark:border-slate-700 group hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-colors">
                             
                             {{-- Thumbnail / Icon --}}
                             <button type="button" 
                                     @click="$dispatch('open-preview', { src: '{{ $url }}', type: '{{ $isImage ? 'image' : ($extension === 'pdf' ? 'pdf' : 'other') }}' })"
                                     class="w-10 h-10 flex-shrink-0 bg-slate-200 dark:bg-slate-700 rounded overflow-hidden flex items-center justify-center text-slate-500 hover:opacity-80 transition-opacity cursor-pointer">
                                 @if($isImage)
                                    <img src="{{ $url }}" class="w-full h-full object-cover" alt="Preview">
                                 @else
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                 @endif
                             </button>

                             {{-- Details --}}
                             <div class="min-w-0 flex-1">
                                 <button type="button" 
                                         @click="$dispatch('open-preview', { src: '{{ $url }}', type: '{{ $isImage ? 'image' : ($extension === 'pdf' ? 'pdf' : 'other') }}' })"
                                         class="block text-left text-xs font-bold text-slate-700 dark:text-slate-300 truncate hover:text-blue-500 hover:underline">
                                    {{ $artifact['name'] }}
                                 </button>
                                 <div class="text-[10px] text-slate-500 flex items-center gap-2">
                                     <span>{{ number_format($artifact['size'] / 1024, 1) }} KB</span>
                                     <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                     <span class="uppercase">{{ $extension }}</span>
                                 </div>
                             </div>

                             {{-- Actions --}}
                             <div class="flex items-center gap-1 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity">
                                 <button type="button" 
                                         @click="$dispatch('open-preview', { src: '{{ $url }}', type: '{{ $isImage ? 'image' : ($extension === 'pdf' ? 'pdf' : 'other') }}' })"
                                         class="p-1.5 text-slate-400 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded transition-colors" title="Open / Preview">
                                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                 </button>
                                 <a href="{{ $url }}" download class="p-1.5 text-slate-400 hover:text-green-500 hover:bg-green-50 dark:hover:bg-green-900/20 rounded transition-colors" title="Download">
                                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                 </a>
                             </div>
                        </div>
                     @endforeach
                </div>
            </div>
            @endif
            
             <!-- Raw Data Link (Hidden Print) -->
            <div class="no-print" x-data="{ show: false }">
                <!-- ... existing raw toggle ... -->
            </div>
        </div>
    </div>

    <!-- PREVIEW MODAL -->
    <div x-data="{ open: false, src: '', type: '' }" 
         @open-preview.window="open = true; src = $event.detail.src; type = $event.detail.type"
         @keydown.escape.window="open = false"
         x-show="open" 
         class="relative z-[100]" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true"
         style="display: none;">
        
        <!-- Backdrop -->
        <div x-show="open" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             class="fixed inset-0 bg-slate-900/90 backdrop-blur-sm transition-opacity"></div>

        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div x-show="open" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     @click.away="open = false"
                     class="relative transform overflow-hidden rounded-lg bg-transparent text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-5xl">
                    
                    <!-- Close Button -->
                    <div class="absolute right-0 top-0 pr-4 pt-4 z-50">
                        <button type="button" @click="open = false" class="rounded-md bg-black/50 text-slate-400 hover:text-white focus:outline-none">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Content -->
                    <div class="bg-black/50 p-1 flex items-center justify-center min-h-[50vh]">
                        <template x-if="type === 'image'">
                            <img :src="src" class="max-h-[85vh] w-auto object-contain rounded-md shadow-2xl" alt="Preview">
                        </template>
                        <template x-if="type === 'pdf'">
                            <iframe :src="src" class="w-full h-[85vh] rounded-md shadow-2xl bg-white"></iframe>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Raw Modal (Hidden Print) -->
    <div id="ai-modal" class="fixed inset-0 z-50 hidden bg-black/80 backdrop-blur-sm flex items-center justify-center p-4 no-print">
         <!-- ... existing modal ... -->
    </div>

</div>

@endsection
