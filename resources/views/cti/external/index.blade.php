@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen font-sans text-slate-300 space-y-6">

    <!-- THREAT STREAM TICKER -->
    <div class="bg-black/80 border-y border-red-900/50 relative overflow-hidden h-10 flex items-center">
        <div class="absolute left-0 top-0 bottom-0 bg-red-900/20 px-4 flex items-center z-20 border-r border-red-900/50">
            <span class="text-xs font-black text-red-500 animate-pulse tracking-widest">LIVE THREAT STREAM</span>
        </div>
        <div class="marquee-container w-full overflow-hidden whitespace-nowrap relative z-10 masking-edges">
            <div class="inline-block animate-marquee text-xs font-mono">
                @foreach($victims->take(10) as $victim)
                    <span class="text-red-400 font-bold ml-6">[{{ $victim['group'] }}]</span>
                    <span class="text-slate-400">TARGETED</span>
                    <span class="text-white font-bold">{{ $victim['victim'] }}</span>
                    <span class="text-slate-600">({{ $victim['country'] }})</span>
                @endforeach
                @foreach($news->take(5) as $article)
                    <span class="text-indigo-400 font-bold ml-6">[NEWS]</span>
                    <span class="text-slate-300">{{ $article['title'] }}</span>
                @endforeach
            </div>
        </div>
    </div>

    <!-- MAIN HEADER -->
    <div class="flex flex-col md:flex-row justify-between items-end gap-4 border-b border-slate-800 pb-4">
        <div>
            <h1 class="text-3xl font-black text-white tracking-tighter flex items-center gap-3">
                <span class="w-2 h-10 bg-red-600 rounded-sm shadow-[0_0_20px_red]"></span>
                MEGA INSURANCE
            </h1>
            <p class="text-slate-500 text-sm mt-1 uppercase tracking-widest font-mono pl-5">External Threat Intelligence Division</p>
        </div>
        <div class="flex items-center gap-3">
             <div class="px-3 py-1 bg-slate-900 border border-slate-700 rounded flex items-center gap-2 text-xs font-mono text-emerald-500">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                LINK ESTABLISHED
            </div>
             <div class="text-right">
                <div class="text-xxs text-slate-500 font-mono">SYSTEM TIME (UTC)</div>
                <div class="text-xl font-mono text-white font-bold" x-data x-text="new Date().toLocaleTimeString('en-US', {hour12: false})">00:00:00</div>
            </div>
        </div>
    </div>

    <!-- WAR ROOM GRID -->
    <div class="grid grid-cols-12 gap-6">
        
        <!-- LEFT FLANK: INDONESIA WATCH & GLOBAL INCIDENTS (8 COLS) -->
        <div class="col-span-12 lg:col-span-8 space-y-6">
            
            <!-- SECTOR 1: INDONESIA WATCH -->
            @if(isset($indonesianVictims) && count($indonesianVictims) > 0)
            <div class="relative bg-slate-900/80 backdrop-blur-xl rounded-sm border-l-4 border-red-600 shadow-2xl overflow-hidden group">
                <!-- Scanline Effect -->
                <div class="absolute inset-0 bg-[linear-gradient(rgba(18,16,16,0)_50%,rgba(0,0,0,0.25)_50%),linear-gradient(90deg,rgba(255,0,0,0.06),rgba(0,255,0,0.02),rgba(0,0,255,0.06))] z-0 pointer-events-none bg-[length:100%_2px,3px_100%] opacity-20"></div>

                <div class="px-6 py-4 flex justify-between items-center bg-red-950/20 border-b border-red-900/20 relative z-10">
                    <h2 class="text-lg font-bold text-red-500 uppercase tracking-widest flex items-center gap-2">
                        <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        PRIORITY ALERT: INDONESIA SECTOR
                    </h2>
                    <span class="text-xs font-mono text-red-400 border border-red-900/50 px-2 py-1 bg-black/40">{{ count($indonesianVictims) }} ACTIVE THREATS</span>
                </div>

                <div class="p-0 relative z-10">
                    <table class="w-full text-left">
                        <thead class="bg-black/40 text-red-400 font-mono text-xs uppercase">
                            <tr>
                                <th class="px-6 py-2">Timestamp</th>
                                <th class="px-6 py-2">Victim Entity</th>
                                <th class="px-6 py-2">Actor</th>
                                <th class="px-6 py-2 text-right">Intel</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-red-900/10 font-mono text-sm">
                            @foreach(array_slice($indonesianVictims, 0, 5) as $victim)
                            <tr class="hover:bg-red-900/10 transition-colors group">
                                <td class="px-6 py-3 text-slate-500 group-hover:text-red-300">{{ \Carbon\Carbon::parse($victim['discovered'])->diffForHumans() }}</td>
                                <td class="px-6 py-3 font-bold text-white group-hover:text-red-200">{{ Str::limit($victim['victim'], 40) }}</td>
                                <td class="px-6 py-3 text-red-500 font-bold uppercase">{{ $victim['group'] }}</td>
                                <td class="px-6 py-3 text-right">
                                    <a href="{{ route('cti.external.group', ['name' => $victim['group']]) }}" class="text-xs text-slate-500 hover:text-white hover:underline">[ANALYZE]</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- SECTOR 2: GLOBAL INCIDENT GRID -->
            <div>
                 <h3 class="flex items-center gap-2 text-sm font-bold text-slate-500 uppercase tracking-widest mb-4">
                    <span class="w-1.5 h-1.5 bg-slate-500 rounded-full"></span>
                    Global Incident Feed (Realtime)
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($victims->take(8) as $victim)
                    <div class="bg-slate-900 border border-slate-700 hover:border-indigo-500/50 p-4 rounded-sm transition-all group relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-16 h-16 bg-white/5 -translate-y-8 translate-x-8 rotate-45 pointer-events-none group-hover:bg-indigo-500/10 transition-colors"></div>
                        
                        <div class="flex justify-between items-start mb-2">
                             <div class="flex flex-col">
                                <span class="text-xs font-bold text-indigo-400 font-mono">{{ $victim['country'] ?? 'UNK' }}</span>
                                <span class="text-[10px] text-slate-600">{{ \Carbon\Carbon::parse($victim['discovered'])->format('M d H:i') }}</span>
                            </div>
                            <span class="text-xs font-bold text-slate-300 bg-slate-800 px-2 py-0.5 rounded border border-slate-600 group-hover:border-indigo-500/30 group-hover:text-white transition-colors">
                                {{ $victim['group'] }}
                            </span>
                        </div>
                        
                        <h4 class="text-white font-bold leading-tight group-hover:text-indigo-300 transition-colors mb-2">{{ Str::limit($victim['victim'], 50) }}</h4>
                        
                        <div class="flex items-center justify-between mt-3 pt-3 border-t border-slate-800 group-hover:border-slate-700">
                             <span class="text-[10px] text-slate-500 font-mono">ID: {{ substr(md5($victim['victim']), 0, 8) }}</span>
                             <a href="{{ route('cti.external.group', ['name' => $victim['group']]) }}" class="text-xs font-bold text-slate-400 hover:text-white flex items-center gap-1">
                                 DOSSIER <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                             </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>

        <!-- RIGHT FLANK: INTEL & DOSSIERS (4 COLS) -->
        <div class="col-span-12 lg:col-span-4 space-y-6">
            
            <!-- DOSSIERS -->
            <div class="bg-slate-900 border border-slate-700 rounded-sm p-5 shadow-lg">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4 border-b border-slate-800 pb-2">Top Threat Actors</h3>
                <div class="space-y-3">
                    @foreach($topGroups as $group)
                    <div class="flex items-center gap-3 p-2 hover:bg-white/5 rounded-sm transition-colors group cursor-pointer" onclick="window.location='{{ route('cti.external.group', ['name' => $group['name'] ?? '']) }}'">
                        <div class="w-10 h-10 bg-slate-800 border border-slate-600 flex items-center justify-center text-lg font-black text-slate-400 group-hover:text-white group-hover:border-red-500 group-hover:bg-red-500/10 transition-all">
                            {{ substr($group['name'] ?? '?', 0, 1) }}
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-bold text-slate-200 group-hover:text-red-400 transition-colors">{{ $group['name'] }}</h4>
                            <div class="w-full bg-slate-800 h-1 mt-1 rounded-full overflow-hidden">
                                <div class="bg-red-600 h-full" style="width: {{ min(($group['count'] / 100) * 100, 100) }}%"></div>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="block text-lg font-black text-white">{{ $group['count'] }}</span>
                            <span class="block text-[9px] text-slate-500 uppercase">Victims</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            
            <!-- INTEL FEED -->
            <div class="bg-slate-900 border border-slate-700 rounded-sm p-5 shadow-lg max-h-[600px] overflow-y-auto custom-scrollbar">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4 border-b border-slate-800 pb-2">Intelligence Briefing</h3>
                <div class="space-y-6">
                    @foreach($news as $article)
                    <article class="group">
                        <a href="{{ $article['link'] }}" target="_blank" class="block">
                            <h4 class="text-sm font-bold text-slate-200 group-hover:text-indigo-400 leading-snug mb-2 transition-colors">{{ $article['title'] }}</h4>
                            <p class="text-xs text-slate-500 line-clamp-3 mb-2">{{ $article['description'] }}</p>
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-mono text-indigo-500/70 border border-indigo-900/50 px-1 rounded">{{ $article['source'] }}</span>
                                <span class="text-[10px] text-slate-600">{{ $article['pubDate'] }}</span>
                            </div>
                        </a>
                    </article>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    .marquee-container {
        mask-image: linear-gradient(to right, transparent, black 10%, black 90%, transparent);
        -webkit-mask-image: linear-gradient(to right, transparent, black 10%, black 90%, transparent);
    }
    @keyframes marquee {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }
    .animate-marquee {
        animation: marquee 30s linear infinite;
    }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #0f172a; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; }
</style>
@endsection
