@extends('layouts.dashboard')

@section('content')
<!-- Custom Styles -->
<style>
    .glass-panel {
        background: rgba(10, 10, 15, 0.6);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: rgba(0,0,0,0.2); }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 2px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }
    
    .bg-grid-pattern {
        background-image: linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        background-size: 30px 30px;
    }
</style>

<div class="min-h-[calc(100vh-80px)] font-sans text-slate-300 relative overflow-hidden flex flex-col pt-6 pb-20 px-6 lg:px-10 z-10" x-data="{ expandedId: null, statusFilter: '{{ request('status', 'All') }}' }">
    
    <!-- Background -->
    <div class="fixed inset-0 bg-slate-950 bg-grid-pattern pointer-events-none z-0"></div>

    <div class="relative z-10 max-w-7xl mx-auto w-full">
        <!-- HEADER -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-black text-white tracking-widest flex items-center gap-3">
                    <svg class="w-8 h-8 text-rose-500 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                    LIST IP BLOCK
                </h1>
                <p class="text-sm text-slate-400 font-mono mt-1">> WEEK {{ $week }} / {{ $year }}</p>
            </div>
            
            <!-- Filters -->
            <div class="flex gap-3 text-xs font-mono w-full md:w-auto">
                <form action="{{ route('ip-block-lists.index') }}" method="GET" class="flex gap-2 w-full">
                    <!-- Week Select -->
                    <select name="week" class="bg-slate-800/80 border border-slate-700 text-slate-300 rounded px-3 py-2 outline-none focus:border-rose-500 w-full md:w-auto" onchange="this.form.submit()">
                        <option value="{{ current_week() }}" {{ $week == current_week() && $year == current_year() ? 'selected' : '' }}>This Week</option>
                        @foreach($availablePeriods as $period)
                            <option value="{{ $period->week_number }}" {{ $week == $period->week_number && $year == $period->year ? 'selected' : '' }}>Week {{ $period->week_number }}</option>
                        @endforeach
                    </select>

                    <input type="hidden" name="year" value="{{ $year }}">

                    <!-- Status Select -->
                    <select name="status" class="bg-slate-800/80 border border-slate-700 text-slate-300 rounded px-3 py-2 outline-none focus:border-rose-500 w-full md:w-auto" onchange="this.form.submit()">
                        <option value="All" {{ request('status') == 'All' ? 'selected' : '' }}>All Status</option>
                        <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Blocked" {{ request('status') == 'Blocked' ? 'selected' : '' }}>Blocked</option>
                        <option value="Ignored" {{ request('status') == 'Ignored' ? 'selected' : '' }}>Ignored</option>
                    </select>
                </form>
            </div>
        </div>

        <!-- STATS CARDS -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
             <div class="glass-panel rounded-xl p-5 border-l-4 border-l-blue-500 flex justify-between items-center relative overflow-hidden group">
                 <div class="absolute right-0 top-0 opacity-10 transform translate-x-4 -translate-y-4 group-hover:scale-110 transition-transform duration-500">
                    <svg class="w-24 h-24 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 4v16m8-8H4"></path></svg>
                 </div>
                 <div>
                     <div class="text-[10px] uppercase text-slate-500 font-bold tracking-widest mb-1">Total IPs</div>
                     <div class="text-3xl font-black text-white font-mono">{{ $stats['total'] }}</div>
                 </div>
             </div>
             <div class="glass-panel rounded-xl p-5 border-l-4 border-l-amber-500 flex justify-between items-center relative overflow-hidden group">
                 <div class="absolute right-0 top-0 opacity-10 transform translate-x-4 -translate-y-4 group-hover:scale-110 transition-transform duration-500">
                    <svg class="w-24 h-24 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                 </div>
                 <div>
                     <div class="text-[10px] uppercase text-amber-500 font-bold tracking-widest mb-1">Pending</div>
                     <div class="text-3xl font-black text-white font-mono">{{ $stats['pending'] }}</div>
                 </div>
             </div>
             <div class="glass-panel rounded-xl p-5 border-l-4 border-l-emerald-500 flex justify-between items-center relative overflow-hidden group">
                 <div class="absolute right-0 top-0 opacity-10 transform translate-x-4 -translate-y-4 group-hover:scale-110 transition-transform duration-500">
                    <svg class="w-24 h-24 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                 </div>
                 <div>
                     <div class="text-[10px] uppercase text-emerald-500 font-bold tracking-widest mb-1">Blocked</div>
                     <div class="text-3xl font-black text-white font-mono">{{ $stats['blocked'] }}</div>
                 </div>
             </div>
        </div>

        <!-- MAIN TABLE -->
        <div class="glass-panel rounded-xl border border-white/5 overflow-hidden flex flex-col">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead class="bg-black/40 text-[10px] text-slate-500 uppercase tracking-widest border-b border-white/10">
                        <tr>
                            <th class="p-4 font-bold">IP Address</th>
                            <th class="p-4 font-bold">Source</th>
                            <th class="p-4 font-bold">Description</th>
                            <th class="p-4 font-bold text-center">Status</th>
                            <th class="p-4 font-bold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-white/5">
                        @forelse($list as $ip)
                            <tr class="hover:bg-white/5 transition-colors group cursor-pointer" @click="expandedId = expandedId === {{ $ip->id }} ? null : {{ $ip->id }}">
                                <td class="p-4 font-mono font-bold text-slate-300">
                                    <div class="flex items-center gap-2">
                                        {{ $ip->ip_address }}
                                    </div>
                                    <div class="text-[10px] text-slate-500 mt-1 font-sans">{{ $ip->created_at->format('M d, Y H:i') }}</div>
                                </td>
                                <td class="p-4 text-xs font-mono text-slate-400">
                                    {{ $ip->source }}
                                </td>
                                <td class="p-4 text-xs text-slate-300 max-w-xs truncate" title="{{ $ip->description }}">
                                    {{ Str::limit($ip->description, 50) }}
                                </td>
                                <td class="p-4 text-center">
                                    @if($ip->status === 'Pending')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-amber-500/10 text-amber-500 border border-amber-500/20">Pending</span>
                                    @elseif($ip->status === 'Blocked')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Blocked</span>
                                    @elseif($ip->status === 'Ignored')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-500/10 text-slate-400 border border-slate-500/20">Ignored</span>
                                    @endif
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex justify-end items-center gap-2" @click.stop>
                                        <!-- Actions dropdown or direct buttons -->
                                        @if($ip->status === 'Pending')
                                        <form action="{{ route('ip-block-lists.update', $ip) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="Blocked">
                                            <button type="submit" class="p-1.5 text-slate-400 hover:text-emerald-400 hover:bg-emerald-400/10 rounded transition-colors" title="Mark as Blocked">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            </button>
                                        </form>
                                        @endif
                                        
                                        <a href="{{ route('investigation.ip-analyzer.index', ['ip' => $ip->ip_address]) }}" class="p-1.5 text-slate-400 hover:text-amber-400 hover:bg-amber-400/10 rounded transition-colors" title="IP Analyzer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                        </a>

                                        <form action="{{ route('ip-block-lists.destroy', $ip) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to remove this IP from the list?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-slate-400 hover:text-red-400 hover:bg-red-400/10 rounded transition-colors" title="Remove">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Expandable Row -->
                            <tr x-show="expandedId === {{ $ip->id }}" x-collapse style="display: none;">
                                <td colspan="5" class="p-0 border-b border-white/5">
                                    <div class="p-6 bg-slate-900/50 border-y border-white/5 shadow-inner">
                                        <h4 class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-3">Full Context / Description</h4>
                                        <div class="bg-black/50 p-4 rounded border border-white/5 font-mono text-xs text-amber-500 break-words whitespace-normal leading-relaxed">
                                            {{ $ip->description }}
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-500 italic">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        No IP Block records found for the selected week.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

@endsection

@php
function current_week() { return now()->weekOfYear; }
function current_year() { return now()->year; }
@endphp
