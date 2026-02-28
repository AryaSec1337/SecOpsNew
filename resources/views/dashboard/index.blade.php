@extends('layouts.dashboard')

@section('content')
<div class="space-y-6 relative" x-data="sentinelDashboard()">
    
    <!-- HEADER & STATUS BAR -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 glass-panel p-6 rounded-2xl relative overflow-hidden group">
        <!-- Decoration -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-blue-500/10 rounded-full blur-3xl -mr-16 -mt-16 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-purple-500/10 rounded-full blur-3xl -ml-16 -mb-16 pointer-events-none"></div>

        <div class="relative z-10">
            <h1 class="text-3xl font-black text-white tracking-tight flex items-center gap-3">
                <span class="text-blue-500 text-4xl">🛡️</span>
                Security Operations
                <span class="text-xs font-mono px-2 py-1 rounded bg-blue-500/20 text-blue-300 border border-blue-500/30">CENTER</span>
            </h1>
            <p class="text-slate-400 mt-1 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                System Operational • Threat Monitoring Active
            </p>
        </div>

        <div class="flex items-center gap-6 relative z-10">
            <!-- DEFCON Indicator -->
            <div class="text-center">
                <div class="text-[10px] uppercase tracking-widest text-slate-500 font-bold mb-1">DEFCON</div>
                <div class="text-3xl font-black px-4 py-1 rounded border-2 transition-all duration-500"
                     :class="{
                        'border-emerald-500 text-emerald-400 bg-emerald-500/10': defcon === 5,
                        'border-yellow-500 text-yellow-400 bg-yellow-500/10': defcon === 4,
                        'border-orange-500 text-orange-400 bg-orange-500/10 animate-pulse': defcon === 3,
                        'border-red-600 text-red-500 bg-red-600/10 animate-bounce': defcon <= 2
                     }"
                     x-text="defcon">5</div>
            </div>
            
            <div class="hidden md:block w-px h-12 bg-white/10"></div>

            <!-- Clock -->
            <div class="text-right hidden md:block">
                <div class="text-2xl font-mono text-slate-200" x-text="clock">00:00:00</div>
                <div class="text-xs text-slate-500 uppercase tracking-widest">Local Time</div>
            </div>
        </div>
    </div>

    <!-- STATS GRID - ROW 1: OPERATIONS (4 Cols) -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
        <!-- Count Reporter -->
        <div class="glass-panel p-4 rounded-xl border border-slate-800/60 flex flex-col justify-center">
             <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 flex items-center gap-2">
                <svg class="w-4 h-4 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                Total Reporters
             </div>
             <div class="text-2xl font-black text-white ml-6">{{ number_format($reporterCount) }}</div>
        </div>

        <!-- Count Pending -->
        <div class="glass-panel p-4 rounded-xl border border-slate-800/60 flex flex-col justify-center border-l-4 border-l-slate-500">
             <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Tickets Pending</div>
             <div class="text-2xl font-black text-white">{{ number_format($investigationStats['pending']) }}</div>
        </div>

        <!-- Count In Progress -->
        <div class="glass-panel p-4 rounded-xl border border-slate-800/60 flex flex-col justify-center border-l-4 border-l-blue-500">
             <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">In Progress</div>
             <div class="text-2xl font-black text-blue-400">{{ number_format($investigationStats['in_progress']) }}</div>
        </div>

        <!-- Count Resolved -->
        <div class="glass-panel p-4 rounded-xl border border-slate-800/60 flex flex-col justify-center border-l-4 border-l-emerald-500">
             <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Resolved</div>
             <div class="text-2xl font-black text-emerald-400">{{ number_format($investigationStats['resolved']) }}</div>
        </div>
    </div>

    <!-- STATS GRID - ROW 2: TOOLS (5 Cols) -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <!-- Count IP Analyzer -->
        <div class="glass-panel p-4 rounded-xl border border-slate-800/60 relative overflow-hidden">
            <div class="flex justify-between items-start">
                <div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">IP Analyzer</div>
                    <div class="text-xl font-black text-white mt-1">{{ number_format($toolStats['ip_analyzer']) }}</div>
                </div>
                <div class="p-1.5 bg-blue-500/10 rounded-lg text-blue-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg></div>
            </div>
        </div>

        <!-- Count Email Analyzer -->
        <div class="glass-panel p-4 rounded-xl border border-slate-800/60 relative overflow-hidden">
            <div class="flex justify-between items-start">
               <div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Email Analyzer</div>
                    <div class="text-xl font-black text-white mt-1">{{ number_format($toolStats['email_analyzer']) }}</div>
                </div>
                <div class="p-1.5 bg-amber-500/10 rounded-lg text-amber-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg></div>
            </div>
        </div>

        <!-- Count URL Scanner -->
        <div class="glass-panel p-4 rounded-xl border border-slate-800/60 relative overflow-hidden">
             <div class="flex justify-between items-start">
               <div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">URL Scanner</div>
                    <div class="text-xl font-black text-white mt-1">{{ number_format($toolStats['url_scanner']) }}</div>
                </div>
                <div class="p-1.5 bg-purple-500/10 rounded-lg text-purple-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg></div>
            </div>
        </div>

        <!-- Count File Analysis -->
        <div class="glass-panel p-4 rounded-xl border border-slate-800/60 relative overflow-hidden">
             <div class="flex justify-between items-start">
               <div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">File Analysis</div>
                    <div class="text-xl font-black text-white mt-1">{{ number_format($toolStats['file_analysis']) }}</div>
                </div>
                <div class="p-1.5 bg-teal-500/10 rounded-lg text-teal-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg></div>
            </div>
        </div>
        
        <!-- Count Blocked IPs -->
        <div class="glass-panel p-4 rounded-xl border border-slate-800/60 relative overflow-hidden">
             <div class="flex justify-between items-start">
               <div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Blocked IPs</div>
                    <div class="text-xl font-black text-white mt-1">{{ number_format($toolStats['blocked_ips']) }}</div>
                </div>
                <div class="p-1.5 bg-red-500/10 rounded-lg text-red-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg></div>
            </div>
        </div>
    </div>

    <!-- CHARTS ROW (Moved to Top) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Investigation Types Chart -->
        <div class="glass-panel p-5 rounded-xl border border-slate-800/60 h-80">
            <h3 class="font-bold text-slate-300 text-xs uppercase tracking-wider mb-4">Investigation Types</h3>
            <div class="relative h-64">
                <canvas id="invTypeChart"></canvas>
            </div>
        </div>

        <!-- Attack Classification Chart -->
        <div class="glass-panel p-5 rounded-xl border border-slate-800/60 h-80">
            <h3 class="font-bold text-slate-300 text-xs uppercase tracking-wider mb-4">Attack Class</h3>
            <div class="relative h-64">
                <canvas id="attackTypeChart"></canvas>
            </div>
        </div>

        <!-- Top Departments Chart -->
        <div class="glass-panel p-5 rounded-xl border border-slate-800/60 h-80">
            <h3 class="font-bold text-slate-300 text-xs uppercase tracking-wider mb-4">Top Departments</h3>
            <div class="relative h-64">
                <canvas id="deptChart"></canvas>
            </div>
        </div>
    </div>

    <!-- OPERATIONS ROW (Status & Reporters) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        
        <!-- Investigation Status Summary -->
        <div class="glass-panel rounded-xl p-5 border border-slate-800/60 h-[320px] flex flex-col">
            <h3 class="font-bold text-slate-300 text-xs uppercase tracking-wider mb-4">Current Ticket Status</h3>
            <div class="space-y-4 flex-1 overflow-y-auto pr-1 custom-scrollbar">
                <div class="flex justify-between items-center p-3 bg-slate-900/50 rounded-lg border border-slate-700/50 group hover:border-slate-600 transition-colors">
                    <span class="text-sm text-slate-400">Pending</span>
                    <div class="flex items-center gap-2">
                            <div class="h-1.5 w-16 bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full bg-slate-500 w-[20%]"></div>
                            </div>
                            <span class="font-bold text-white text-xs">{{ $investigationStats['pending'] }}</span>
                    </div>
                </div>
                <div class="flex justify-between items-center p-3 bg-blue-900/10 rounded-lg border border-blue-500/20 group hover:border-blue-500/40 transition-colors">
                    <span class="text-sm text-blue-400">In Progress</span>
                        <div class="flex items-center gap-2">
                            <div class="h-1.5 w-16 bg-blue-900/50 rounded-full overflow-hidden">
                                <div class="h-full bg-blue-500 w-[50%]"></div>
                            </div>
                            <span class="font-bold text-blue-400 text-xs">{{ $investigationStats['in_progress'] }}</span>
                    </div>
                </div>
                <div class="flex justify-between items-center p-3 bg-emerald-900/10 rounded-lg border border-emerald-500/20 group hover:border-emerald-500/40 transition-colors">
                    <span class="text-sm text-emerald-400">Resolved</span>
                        <div class="flex items-center gap-2">
                            <div class="h-1.5 w-16 bg-emerald-900/50 rounded-full overflow-hidden">
                                <div class="h-full bg-emerald-500 w-[80%]"></div>
                            </div>
                            <span class="font-bold text-emerald-400 text-xs">{{ $investigationStats['resolved'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Reporters -->
        <div class="glass-panel rounded-xl border border-slate-800/60 flex flex-col overflow-hidden h-[320px]">
            <div class="p-4 border-b border-white/5 bg-slate-900/50">
                <h3 class="font-bold text-slate-300 text-sm uppercase tracking-wider">Top Reporters</h3>
            </div>
            <div class="p-4 space-y-4 overflow-y-auto flex-1 custom-scrollbar">
                @forelse($topReporters as $reporter)
                <div class="flex items-center gap-3 group">
                    <div class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center text-xs font-bold text-slate-300 border border-slate-700 group-hover:border-blue-500 group-hover:text-blue-500 transition-colors">
                        {{ substr($reporter->reporter_email, 0, 2) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-bold text-slate-200 truncate group-hover:text-white transition-colors">{{ $reporter->reporter_email }}</div>
                        <div class="text-[10px] text-slate-500">{{ $reporter->total }} Reports</div>
                    </div>
                </div>
                @empty
                <div class="text-center text-slate-500 text-xs py-4">No data available.</div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- TOOL INTELLIGENCE GRID (Moved Below Charts) -->
    <!-- Row 1: IPs, Emails, URLs -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        
        <!-- IP Analyzer Card -->
        <div class="glass-panel p-5 rounded-xl border border-slate-800/60 relative overflow-hidden flex flex-col h-[320px]">
            <div class="flex justify-between items-center mb-4 border-b border-white/5 pb-3">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span class="font-bold text-slate-300 text-sm uppercase tracking-wider">IP Analyzer</span>
                </div>
                <div class="text-2xl font-black text-white">{{ number_format($toolStats['ip_analyzer']) }}</div>
            </div>
            
            <div class="flex-1 overflow-y-auto pr-1 custom-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead class="text-[10px] text-slate-500 uppercase sticky top-0 bg-slate-900/90 backdrop-blur z-10">
                        <tr>
                            <th class="pb-2">IP Address</th>
                            <th class="pb-2 text-right">Risk Score</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs divide-y divide-slate-800/50">
                        @forelse($recentIps as $ip)
                        <tr class="group hover:bg-white/5 transition-colors">
                            <td class="py-2.5 font-mono text-blue-300">{{ $ip->ip_address }}</td>
                            <td class="py-2.5 text-right">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $ip->risk_score > 50 ? 'bg-red-500/20 text-red-400' : 'bg-emerald-500/20 text-emerald-400' }}">
                                    {{ $ip->risk_score }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="py-4 text-center text-slate-600 italic">No data yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Email Analyzer Card -->
        <div class="glass-panel p-5 rounded-xl border border-slate-800/60 relative overflow-hidden flex flex-col h-[320px]">
             <div class="flex justify-between items-center mb-4 border-b border-white/5 pb-3">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    <span class="font-bold text-slate-300 text-sm uppercase tracking-wider">Email Analyzer</span>
                </div>
                <div class="text-2xl font-black text-white">{{ number_format($toolStats['email_analyzer']) }}</div>
            </div>

             <div class="flex-1 overflow-y-auto pr-1 custom-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead class="text-[10px] text-slate-500 uppercase sticky top-0 bg-slate-900/90 backdrop-blur z-10">
                        <tr>
                            <th class="pb-2">Sender</th>
                            <th class="pb-2 text-right">Risk</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs divide-y divide-slate-800/50">
                        @forelse($recentEmails as $email)
                        <tr class="group hover:bg-white/5 transition-colors">
                            <td class="py-2.5 max-w-[150px] truncate text-slate-300" title="{{ $email->sender }}">{{ \Illuminate\Support\Str::limit($email->sender, 20) }}</td>
                            <td class="py-2.5 text-right">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold 
                                    {{ $email->risk_level == 'High' ? 'bg-red-500/20 text-red-400' : 
                                      ($email->risk_level == 'Medium' ? 'bg-amber-500/20 text-amber-400' : 'bg-emerald-500/20 text-emerald-400') }}">
                                    {{ $email->risk_level }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="py-4 text-center text-slate-600 italic">No data yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- URL Scanner Card -->
        <div class="glass-panel p-5 rounded-xl border border-slate-800/60 relative overflow-hidden flex flex-col h-[320px]">
             <div class="flex justify-between items-center mb-4 border-b border-white/5 pb-3">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                    <span class="font-bold text-slate-300 text-sm uppercase tracking-wider">URL Scanner</span>
                </div>
                <div class="text-2xl font-black text-white">{{ number_format($toolStats['url_scanner']) }}</div>
            </div>

             <div class="flex-1 overflow-y-auto pr-1 custom-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead class="text-[10px] text-slate-500 uppercase sticky top-0 bg-slate-900/90 backdrop-blur z-10">
                        <tr>
                            <th class="pb-2">URL</th>
                            <th class="pb-2 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs divide-y divide-slate-800/50">
                        @forelse($recentUrls as $url)
                        <tr class="group hover:bg-white/5 transition-colors">
                            <td class="py-2.5 max-w-[150px] truncate text-indigo-300 font-mono" title="{{ $url->url }}">{{ \Illuminate\Support\Str::limit($url->url, 25) }}</td>
                            <td class="py-2.5 text-right">
                                <span class="text-[10px] font-bold text-slate-400">{{ $url->status }}</span>
                            </td>
                        </tr>
                         @empty
                        <tr><td colspan="2" class="py-4 text-center text-slate-600 italic">No data yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Row 2: Files, Blocked IPs -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        
        <!-- File Analysis Card -->
         <div class="glass-panel p-5 rounded-xl border border-slate-800/60 relative overflow-hidden flex flex-col h-[320px]">
             <div class="flex justify-between items-center mb-4 border-b border-white/5 pb-3">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span class="font-bold text-slate-300 text-sm uppercase tracking-wider">File Analysis</span>
                </div>
                <div class="text-2xl font-black text-white">{{ number_format($toolStats['file_analysis']) }}</div>
            </div>

             <div class="flex-1 overflow-y-auto pr-1 custom-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead class="text-[10px] text-slate-500 uppercase sticky top-0 bg-slate-900/90 backdrop-blur z-10">
                        <tr>
                            <th class="pb-2">File Name</th>
                            <th class="pb-2 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs divide-y divide-slate-800/50">
                        @forelse($recentFiles as $file)
                        <tr class="group hover:bg-white/5 transition-colors">
                            <td class="py-2.5 font-mono text-teal-300 truncate max-w-[200px]" title="{{ $file->file_name }}">{{ \Illuminate\Support\Str::limit($file->file_name, 30) }}</td>
                            <td class="py-2.5 text-right">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold border border-white/10 text-slate-300">
                                    {{ $file->status }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="py-4 text-center text-slate-600 italic">No data yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Blocked IPs Card -->
        <div class="glass-panel p-5 rounded-xl border border-slate-800/60 relative overflow-hidden flex flex-col h-[320px]">
             <div class="flex justify-between items-center mb-4 border-b border-white/5 pb-3">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                    <span class="font-bold text-slate-300 text-sm uppercase tracking-wider">Blocked IPs</span>
                </div>
                <div class="text-2xl font-black text-white">{{ number_format($toolStats['blocked_ips']) }}</div>
            </div>

             <div class="flex-1 overflow-y-auto pr-1 custom-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead class="text-[10px] text-slate-500 uppercase sticky top-0 bg-slate-900/90 backdrop-blur z-10">
                        <tr>
                            <th class="pb-2">IP Address</th>
                            <th class="pb-2 text-right">Reason</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs divide-y divide-slate-800/50">
                        @forelse($recentBlocked as $block)
                        <tr class="group hover:bg-white/5 transition-colors">
                            <td class="py-2.5 font-mono text-red-400">{{ $block->ip_address }}</td>
                            <td class="py-2.5 text-right text-slate-400 truncate max-w-[150px]" title="{{ $block->reason }}">
                                {{ $block->reason ?? 'Manual Block' }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="py-4 text-center text-slate-600 italic">No data yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>



    <!-- MAIN DASHBOARD GRID (Bottom Section) -->
    <div class="grid grid-cols-1 gap-6">
        
        <!-- NETWORK TRAFFIC (Full Width) -->
        <div class="flex flex-col gap-6">
             <!-- Traffic Chart (Placeholder / Future) -->
             <div class="glass-panel rounded-xl h-80 p-5 border border-slate-800/60">
                 <h3 class="font-bold text-slate-300 text-xs uppercase tracking-wider mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                    Network Traffic Analysis (7 Days)
                 </h3>
                 <div class="relative h-64">
                    <canvas id="trafficChart"></canvas>
                 </div>
            </div>
        </div>

    </div>
</div>

<!-- CHART SCRIPT -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    Chart.defaults.color = '#94a3b8';
    Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.05)';
    Chart.defaults.font.family = "'Inter', sans-serif";

    // Helper: Create Gradients
    function createGradient(ctx, colorStart, colorEnd) {
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, colorStart);
        gradient.addColorStop(1, colorEnd);
        return gradient;
    }

    // 1. Investigation Type Chart (Doughnut)
    const ctxInv = document.getElementById('invTypeChart').getContext('2d');
    const gradBlue = createGradient(ctxInv, '#60a5fa', '#2563eb');
    const gradPurple = createGradient(ctxInv, '#c084fc', '#7c3aed');
    const gradTeal = createGradient(ctxInv, '#2dd4bf', '#0d9488');
    const gradAmber = createGradient(ctxInv, '#fbbf24', '#d97706');
    const gradRed = createGradient(ctxInv, '#f87171', '#dc2626');

    new Chart(ctxInv, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_keys($investigationTypes)) !!},
            datasets: [{
                data: {!! json_encode(array_values($investigationTypes)) !!},
                backgroundColor: [gradBlue, gradAmber, gradTeal, gradPurple, gradRed],
                hoverOffset: 15,
                borderWidth: 0,
                borderRadius: 5,
                spacing: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    position: 'bottom', 
                    labels: { 
                        boxWidth: 8, 
                        padding: 15, 
                        font: { size: 10, weight: 'bold' },
                        color: '#cbd5e1',
                        usePointStyle: true
                    } 
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    titleColor: '#e2e8f0',
                    bodyColor: '#cbd5e1',
                    borderColor: 'rgba(255,255,255,0.1)',
                    borderWidth: 1,
                    cornerRadius: 8,
                    padding: 10
                }
            },
            cutout: '75%',
            layout: { padding: 10 }
        }
    });

    // 2. Attack Classification Chart (Doughnut)
    const ctxAttack = document.getElementById('attackTypeChart').getContext('2d');
    const gradDanger = createGradient(ctxAttack, '#ef4444', '#b91c1c');
    const gradSafe = createGradient(ctxAttack, '#10b981', '#059669');

    new Chart(ctxAttack, {
        type: 'doughnut', 
        data: {
            labels: {!! json_encode(array_keys($attackStats)) !!},
            datasets: [{
                data: {!! json_encode(array_values($attackStats)) !!},
                backgroundColor: [gradDanger, gradSafe],
                hoverOffset: 15,
                borderWidth: 0,
                borderRadius: 5,
                spacing: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    position: 'bottom', 
                    labels: { 
                        boxWidth: 8, 
                        padding: 15, 
                        font: { size: 10, weight: 'bold' },
                        color: '#cbd5e1',
                        usePointStyle: true
                    } 
                }
            },
            cutout: '60%', 
            layout: { padding: 10 }
        }
    });

    // 3. Departments Chart (Doughnut)
    const ctxDept = document.getElementById('deptChart').getContext('2d');
    const deptData = {!! json_encode($topDepartments) !!};
    
    // Generate gradients for departments dynamically if possible, or correct static list
    const deptColors = [
        createGradient(ctxDept, '#3b82f6', '#1d4ed8'), // Blue
        createGradient(ctxDept, '#10b981', '#047857'), // Emerald
        createGradient(ctxDept, '#f59e0b', '#b45309'), // Amber
        createGradient(ctxDept, '#8b5cf6', '#6d28d9'), // Purple
        createGradient(ctxDept, '#ec4899', '#be185d')  // Pink
    ];

    new Chart(ctxDept, {
        type: 'doughnut',
        data: {
            labels: deptData.map(d => d.reporter_department),
            datasets: [{
                label: 'Reports',
                data: deptData.map(d => d.total),
                backgroundColor: deptColors,
                hoverOffset: 15,
                borderWidth: 0,
                borderRadius: 5, 
                spacing: 5 
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                 legend: { 
                    display: true, 
                    position: 'right', 
                    labels: { 
                        boxWidth: 8, 
                        padding: 10, 
                        font: { size: 9 },
                        color: '#94a3b8',
                        usePointStyle: true
                    } 
                } 
            },
            cutout: '70%',
            layout: { padding: 10 }
        }
    });

    // ALPINE JS DASHBOARD LOGIC
    document.addEventListener('alpine:init', () => {
        Alpine.data('sentinelDashboard', () => ({
            clock: new Date().toLocaleTimeString(),
            defcon: 5,
            stats: {
                logs: {{ $totalLogs }},
                open_incidents: {{ $openIncidentsCount }},
                blocked_ips: {{ $blockedIpsCount }},
                critical_fim: {{ $criticalFim }}
            },
            feed: [],

            init() {
                // Clock Loop
                setInterval(() => {
                    this.clock = new Date().toLocaleTimeString();
                }, 1000);

                // Polling Loop (Every 3s)
                setInterval(() => {
                    this.fetchStats();
                }, 3000);

                // Initial Fetch
                this.fetchStats();
            },

            async fetchStats() {
                try {
                    const res = await fetch("{{ route('dashboard.live') }}");
                    const data = await res.json();
                    
                    this.stats = data.counters;
                    this.feed = data.feed;
                    this.defcon = data.defcon;
                } catch (e) {
                    console.error("Sentinel Link Lost:", e);
                }
            },
            
            formatNumber(num) {
                return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            }
        }))
    });
</script>

<style>
    @keyframes scan {
        0% { transform: translateY(-100%); }
        100% { transform: translateY(100%); }
    }
    .animate-scan {
        animation: scan 4s linear infinite;
    }
</style>
@endsection
