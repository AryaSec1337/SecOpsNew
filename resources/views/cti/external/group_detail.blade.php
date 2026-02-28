@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen font-sans text-slate-300 space-y-8" x-data="{ activeTab: 'ttp' }">

    <!-- CLASSIFIED HEADER (DOSSIER STYLE) -->
    <div class="relative bg-[#0c0c0c] border border-red-900/30 shadow-2xl overflow-hidden rounded-sm group">
        <!-- Watermark -->
        <div class="absolute inset-0 flex items-center justify-center opacity-[0.03] pointer-events-none select-none">
            <span class="text-[15rem] font-black font-mono text-red-500 -rotate-12">CONFIDENTIAL</span>
        </div>
        
        <!-- Top Stamp Bar -->
        <div class="bg-red-900/10 border-b border-red-900/30 px-6 py-2 flex justify-between items-center">
            <div class="text-red-600 font-black font-mono tracking-[0.2em] animate-pulse">TOP SECRET // EYES ONLY</div>
            <div class="text-xxs font-mono text-red-800">AUTH: CYBER_COMMAND_CENTRAL // ID: {{ substr(md5($group['group']), 0, 8) }}</div>
        </div>

        <div class="p-8 md:p-10 relative z-10">
            <div class="flex flex-col lg:flex-row gap-10">
                <!-- Mugshot / Identity -->
                <div class="w-full lg:w-64 flex-shrink-0 flex flex-col items-center">
                    <div class="w-48 h-48 bg-[#1a1a1a] border-4 border-slate-800 flex items-center justify-center relative overflow-hidden shadow-inner group-hover:border-red-900/50 transition-colors">
                        <!-- Profile Letter -->
                        <span class="text-8xl font-black text-[#2a2a2a] group-hover:text-red-900/20 transition-colors select-none">{{ substr($group['group'], 0, 1) }}</span>
                        
                        <!-- Overlay Text -->
                        <div class="absolute inset-x-0 bottom-0 bg-red-600 text-black text-center font-bold font-mono text-xs py-1 tracking-widest">
                            HIGH VALUE TARGET
                        </div>
                    </div>
                </div>

                <!-- Info Block -->
                <div class="flex-1">
                    <div class="flex items-start justify-between border-b-2 border-slate-800 pb-6 mb-6">
                        <div>
                            <h1 class="text-6xl font-black text-white tracking-widest uppercase font-mono mb-2 glitch-text" data-text="{{ $group['group'] }}">{{ $group['group'] }}</h1>
                            <div class="flex items-center gap-4 text-xs font-mono text-slate-500">
                                <span class="bg-slate-800 px-2 py-1 rounded text-red-400">THREAT_LEVEL: CRITICAL</span>
                                <span>LAST_ACTIVITY: <span class="text-white">{{ isset($group['lastseen']) ? \Carbon\Carbon::parse($group['lastseen'])->isoFormat('YYYY-MM-DD HH:mm:ss') : 'UNKNOWN' }}</span></span>
                            </div>
                        </div>
                        <div class="text-right hidden md:block">
                            <div class="text-5xl font-black text-red-500">{{ $group['victims'] ?? 0 }}</div>
                            <div class="text-xs font-mono text-red-800 tracking-widest">CONFIRMED HITS</div>
                        </div>
                    </div>

                    <p class="font-mono text-sm text-slate-400 leading-relaxed max-w-4xl border-l-[3px] border-red-900/50 pl-6 relative">
                        <span class="absolute -left-[3px] top-0 h-4 w-[3px] bg-red-600"></span>
                        {!! $group['description'] ?? 'INTELLIGENCE BRIEFING UNAVAILABLE. TARGET PROFILE INCOMPLETE. GATHERING ADDITIONAL SIGNAL INT (SIGINT)...' !!}
                    </p>

                    <!-- Profile Tools -->
                    <div class="mt-8 flex gap-4">
                        @if(!empty($group['url']))
                        <a href="{{ $group['url'] }}" target="_blank" class="px-6 py-2 bg-red-900/20 border border-red-900/50 text-red-400 font-mono text-xs font-bold hover:bg-red-900/40 hover:text-white transition-all flex items-center gap-2 group/btn">
                            <svg class="w-4 h-4 group-hover/btn:animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            ACCESS EXTERNAL DB
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABS NAV -->
    <div class="flex border-b border-slate-800">
        <button @click="activeTab = 'ttp'" :class="activeTab === 'ttp' ? 'border-red-500 text-red-500 bg-red-500/5' : 'border-transparent text-slate-500 hover:text-slate-300'" class="px-8 py-3 font-mono text-sm font-bold border-b-2 transition-all flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            TACTICAL MATRIX (TTPs)
        </button>
        <button @click="activeTab = 'ioc'" :class="activeTab === 'ioc' ? 'border-emerald-500 text-emerald-500 bg-emerald-500/5' : 'border-transparent text-slate-500 hover:text-slate-300'" class="px-8 py-3 font-mono text-sm font-bold border-b-2 transition-all flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
            EVIDENCE LOCKER (IOCs)
        </button>
        @if(isset($negotiations['chats']) && count($negotiations['chats']) > 0)
        <button @click="activeTab = 'comms'" :class="activeTab === 'comms' ? 'border-indigo-500 text-indigo-500 bg-indigo-500/5' : 'border-transparent text-slate-500 hover:text-slate-300'" class="px-8 py-3 font-mono text-sm font-bold border-b-2 transition-all flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
            INTERCEPTED COMMS
        </button>
        @endif
    </div>

    <!-- TTP MATRIX (TAB CONTENT) -->
    <div x-show="activeTab === 'ttp'" class="grid grid-cols-1 xl:grid-cols-4 gap-8 animate-fade-in-up">
        <!-- Main Matrix -->
        <div class="xl:col-span-3 bg-[#0f0f0f] border border-slate-800 rounded-sm p-1">
            <div class="h-[600px] overflow-y-auto scrollbar-thin scrollbar-thumb-slate-700 scrollbar-track-transparent">
                 @forelse($group['ttps'] ?? [] as $tactic)
                 <div class="mb-4 last:mb-0">
                    <div class="bg-slate-900/50 px-4 py-2 border-b border-slate-800 flex justify-between">
                         <h3 class="font-bold text-slate-300 text-xs uppercase">{{ $tactic['tactic_name'] }}</h3>
                         <span class="text-[10px] font-mono text-slate-600">{{ $tactic['tactic_id'] }}</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-1 p-1">
                        @foreach($tactic['techniques'] as $tech)
                        <div class="bg-slate-900 border border-slate-800 p-3 hover:bg-slate-800 hover:border-red-900/50 transition-all group/card cursor-help relative overflow-hidden">
                            <div class="absolute inset-0 bg-red-500/5 opacity-0 group-hover/card:opacity-100 transition-opacity pointer-events-none"></div>
                            
                            <div class="flex justify-between items-start mb-2 relative z-10">
                                <span class="font-mono text-[10px] text-red-500 font-bold group-hover/card:text-red-400">{{ $tech['technique_id'] }}</span>
                            </div>
                            <p class="text-xs font-bold text-slate-300 group-hover/card:text-white relative z-10">{{ $tech['technique_name'] }}</p>
                            
                            <!-- Tooltip/Popout could go here, for now just static text -->
                            <p class="text-[10px] text-slate-500 mt-2 leading-tight opacity-0 group-hover/card:opacity-100 transition-opacity h-0 group-hover/card:h-auto overflow-hidden">
                                {{ Str::limit($tech['technique_details'], 100) }}
                            </p>
                        </div>
                        @endforeach
                    </div>
                 </div>
                 @empty
                 <div class="flex items-center justify-center h-full text-slate-600 font-mono text-sm">
                     // NO TACTICAL INTELLIGENCE AVAILABLE
                 </div>
                 @endforelse
            </div>
        </div>

        <!-- Right Side: Arsenal & CVEs -->
        <div class="space-y-6">
            <!-- Weaponry -->
            <div class="bg-slate-900/50 border border-slate-800 p-4">
                 <h4 class="text-xs font-mono text-orange-500 font-bold uppercase mb-4 border-b border-slate-800 pb-2 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                    Known Weaponry
                </h4>
                <div class="flex flex-wrap gap-2">
                    @forelse($group['tools'] ?? [] as $category => $tools)
                        @foreach($tools as $tool)
                        <span class="px-2 py-1 bg-black border border-slate-700 text-slate-400 text-[10px] font-mono uppercase hover:border-orange-500/50 hover:text-white transition-colors cursor-help" title="{{ $category }}">
                            {{ $tool }}
                        </span>
                        @endforeach
                    @empty
                        <span class="text-xs text-slate-600 italic">No tools identified.</span>
                    @endforelse
                </div>
            </div>

             <!-- CVEs -->
             <div class="bg-slate-900/50 border border-slate-800 p-4">
                 <h4 class="text-xs font-mono text-red-500 font-bold uppercase mb-4 border-b border-slate-800 pb-2 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                    Targeted Vulnerabilities
                </h4>
                <ul class="space-y-2 max-h-[400px] overflow-y-auto scrollbar-thin scrollbar-thumb-slate-700">
                    @forelse($group['vulnerabilities'] ?? [] as $vuln)
                    <li class="bg-black/50 p-2 border-l-2 border-slate-700 hover:border-red-500 transition-colors">
                        <a href="https://nvd.nist.gov/vuln/detail/{{ $vuln['CVE'] }}" target="_blank" class="block group/cve">
                            <div class="flex justify-between items-center mb-1">
                                <span class="font-mono text-red-400 font-bold text-xs group-hover/cve:underline">{{ $vuln['CVE'] }}</span>
                                <span class="text-[9px] font-bold text-slate-500 bg-slate-900 px-1 border border-slate-800">{{ $vuln['severity'] ?? 'N/A' }}</span>
                            </div>
                            <div class="text-[10px] text-slate-500">{{ $vuln['Vendor'] }} {{ $vuln['Product'] }}</div>
                        </a>
                    </li>
                    @empty
                    <li class="text-xs text-slate-600 italic">No CVE data available.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <!-- IOC LOCKER (TAB CONTENT) -->
    <div x-show="activeTab === 'ioc'" class="animate-fade-in-up" x-cloak>
        <div class="bg-[#050505] border border-slate-800 rounded-sm font-mono relative overflow-hidden">
            <!-- Terminal Header -->
            <div class="bg-[#151515] px-4 py-2 border-b border-slate-800 flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-red-500/20 border border-red-500/50"></div>
                    <div class="w-3 h-3 rounded-full bg-yellow-500/20 border border-yellow-500/50"></div>
                    <div class="w-3 h-3 rounded-full bg-green-500/20 border border-green-500/50"></div>
                    <span class="text-xs text-slate-500 ml-2">user@cybercom:~/evidence/{{ strtolower(str_replace(' ', '_', $group['group'])) }}</span>
                </div>
                <button class="text-xs text-emerald-500 hover:text-white border border-emerald-900 hover:bg-emerald-900/20 px-3 py-1 transition-colors" onclick="alert('All IOCs copied to clipboard!')">
                    COPY_ALL_SIGNATURES
                </button>
            </div>
            
            <!-- Content -->
            <div class="p-6 text-sm grid grid-cols-1 lg:grid-cols-2 gap-8">
                @if(isset($iocs['iocs']) && count($iocs['iocs']) > 0)
                    @foreach($iocs['iocs'] as $type => $values)
                    <div>
                        <h5 class="text-emerald-600 font-bold mb-3 uppercase flex items-center gap-2">
                            <span>> {{ $type }}</span>
                            <span class="text-xxs bg-emerald-900/20 px-1 text-emerald-500">{{ count($values) }}</span>
                        </h5>
                        <div class="space-y-1 pl-4 border-l border-slate-800">
                             @foreach($values as $ioc)
                             <div class="text-slate-400 hover:text-white cursor-text select-all hover:bg-slate-900/50 truncate">{{ $ioc }}</div>
                             @endforeach
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="col-span-2 text-center py-20">
                        <div class="text-slate-700 text-xl font-bold opacity-50">NO_DIGITAL_FORENSICS_FOUND</div>
                        <p class="text-slate-600 text-xs mt-2">// The locker is empty. No indicators have been cataloged yet.</p>
                    </div>
                @endif
            </div>

            <!-- Terminal Cursor -->
            <div class="absolute bottom-4 left-6 flex items-center gap-2 text-emerald-500 pointer-events-none">
                <span>$</span>
                <span class="animate-pulse bg-emerald-500 w-2 h-4 block"></span>
            </div>
        </div>
    </div>

    <!-- COMMS LOGS (TAB CONTENT) -->
    @if(isset($negotiations['chats']) && count($negotiations['chats']) > 0)
    <div x-show="activeTab === 'comms'" class="animate-fade-in-up" x-cloak>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($negotiations['chats'] as $chat)
            <a href="{{ route('cti.external.negotiation', ['name' => $group['group'], 'chatId' => $chat['id']]) }}" class="block bg-slate-900/50 hover:bg-slate-900 border border-slate-700 hover:border-indigo-500 transition-all p-5 rounded-sm group relative overflow-hidden">
                <div class="absolute top-0 left-0 w-1 h-full bg-slate-800 group-hover:bg-indigo-500 transition-colors"></div>
                
                <div class="flex justify-between items-start mb-4 pl-3">
                    <span class="font-mono text-xs text-slate-500">LOG_ID: {{ $chat['id'] }}</span>
                    @if($chat['paid'])
                        <span class="text-[10px] font-bold text-black bg-red-500 px-2 py-0.5">RANSOM_PAID</span>
                    @else
                        <span class="text-[10px] font-bold text-slate-400 bg-slate-800 px-2 py-0.5">UNRESOLVED</span>
                    @endif
                </div>
                
                <div class="pl-3">
                    <h4 class="text-white font-bold group-hover:text-indigo-300 transition-colors mb-2">INTERCEPT #{{ substr($chat['id'], -4) }}</h4>
                    <div class="flex items-center gap-2 text-xs text-slate-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                        {{ $chat['message_count'] ?? 0 }} Messages Detected
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

</div>

<style>
    [x-cloak] { display: none !important; }
    .glitch-text {
        position: relative;
    }
    .glitch-text::before, .glitch-text::after {
        content: attr(data-text);
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: #0c0c0c;
    }
    .glitch-text::before {
        left: 2px;
        text-shadow: -1px 0 #ff00c1;
        clip: rect(44px, 450px, 56px, 0);
        animation: glitch-anim 5s infinite linear alternate-reverse;
    }
    .glitch-text::after {
        left: -2px;
        text-shadow: -1px 0 #00fff9;
        clip: rect(44px, 450px, 56px, 0);
        animation: glitch-anim2 5s infinite linear alternate-reverse;
    }
    @keyframes glitch-anim {
        0% { clip: rect(2px, 9999px, 2px, 0); }
        100% { clip: rect(80px, 9999px, 80px, 0); }
    }
    @keyframes glitch-anim2 {
        0% { clip: rect(65px, 9999px, 65px, 0); }
        100% { clip: rect(10px, 9999px, 10px, 0); }
    }
</style>
@endsection
