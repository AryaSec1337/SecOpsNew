@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Webhook Alerts</h1>
            <p class="text-sm text-slate-400 mt-1">Automated threat alerts from File Upload Monitoring pipeline.</p>
        </div>
        <div class="flex items-center gap-2 px-3 py-1.5 bg-rose-500/10 border border-rose-500/30 rounded-lg">
            <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
            <span class="text-xs font-bold text-rose-400">{{ $stats['pending'] }} Pending</span>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <!-- Total -->
        <div class="glass-panel p-4 rounded-2xl relative overflow-hidden group">
            <div class="absolute inset-0 bg-blue-500/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="flex items-center gap-3 relative z-10">
                <div class="w-10 h-10 rounded-xl bg-blue-500/20 text-blue-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                </div>
                <div>
                    <h3 class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total</h3>
                    <div class="text-xl font-black text-white">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>
        <!-- Pending -->
        <div class="glass-panel p-4 rounded-2xl relative overflow-hidden group">
            <div class="absolute inset-0 bg-amber-500/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="flex items-center gap-3 relative z-10">
                <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h3 class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Pending</h3>
                    <div class="text-xl font-black text-amber-400">{{ $stats['pending'] }}</div>
                </div>
            </div>
        </div>
        <!-- In Progress -->
        <div class="glass-panel p-4 rounded-2xl relative overflow-hidden group">
            <div class="absolute inset-0 bg-blue-500/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="flex items-center gap-3 relative z-10">
                <div class="w-10 h-10 rounded-xl bg-blue-500/20 text-blue-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                </div>
                <div>
                    <h3 class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">In Progress</h3>
                    <div class="text-xl font-black text-blue-400">{{ $stats['in_progress'] }}</div>
                </div>
            </div>
        </div>
        <!-- Malicious -->
        <div class="glass-panel p-4 rounded-2xl relative overflow-hidden group">
            <div class="absolute inset-0 bg-rose-500/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="flex items-center gap-3 relative z-10">
                <div class="w-10 h-10 rounded-xl bg-rose-500/20 text-rose-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div>
                    <h3 class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Malicious</h3>
                    <div class="text-xl font-black text-rose-400">{{ $stats['malicious'] }}</div>
                </div>
            </div>
        </div>
        <!-- Resolved -->
        <div class="glass-panel p-4 rounded-2xl relative overflow-hidden group">
            <div class="absolute inset-0 bg-emerald-500/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="flex items-center gap-3 relative z-10">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h3 class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Resolved</h3>
                    <div class="text-xl font-black text-emerald-400">{{ $stats['resolved'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Buttons -->
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('webhook-alerts.index') }}" 
           class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ !request('status') && !request('verdict') ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/20' : 'bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700' }}">
            All Alerts
        </a>
        <a href="{{ route('webhook-alerts.index', ['status' => 'Pending']) }}" 
           class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ request('status') === 'Pending' ? 'bg-amber-600 text-white shadow-lg' : 'bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700' }}">
            Pending
        </a>
        <a href="{{ route('webhook-alerts.index', ['status' => 'In Progress']) }}" 
           class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ request('status') === 'In Progress' ? 'bg-blue-600 text-white shadow-lg' : 'bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700' }}">
            In Progress
        </a>
        <a href="{{ route('webhook-alerts.index', ['status' => 'Resolved']) }}" 
           class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ request('status') === 'Resolved' ? 'bg-emerald-600 text-white shadow-lg' : 'bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700' }}">
            Resolved
        </a>
        <span class="w-px h-6 bg-slate-700 mx-1"></span>
        <a href="{{ route('webhook-alerts.index', ['verdict' => 'MALICIOUS']) }}" 
           class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ request('verdict') === 'MALICIOUS' ? 'bg-rose-600 text-white shadow-lg' : 'bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700' }}">
            🔴 Malicious
        </a>
        <a href="{{ route('webhook-alerts.index', ['verdict' => 'SUSPICIOUS']) }}" 
           class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ request('verdict') === 'SUSPICIOUS' ? 'bg-amber-600 text-white shadow-lg' : 'bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700' }}">
            🟡 Suspicious
        </a>
    </div>

    <!-- Alerts Table -->
    <div class="glass-panel rounded-2xl overflow-hidden border border-white/5 shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="text-xs uppercase bg-slate-900/80 text-slate-400 font-bold tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Alert</th>
                        <th class="px-6 py-4">Server</th>
                        <th class="px-6 py-4">Verdict</th>
                        <th class="px-6 py-4">Detected By</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Time</th>
                        <th class="px-6 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($alerts as $alert)
                        <tr class="hover:bg-white/5 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="relative flex h-3 w-3">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-{{ $alert->verdict_color }}-400 opacity-20"></span>
                                        <span class="relative inline-flex rounded-full h-3 w-3 bg-{{ $alert->verdict_color }}-500"></span>
                                    </div>
                                    <div>
                                        <div class="font-bold text-white group-hover:text-blue-400 transition-colors">{{ Str::limit($alert->original_filename, 30) }}</div>
                                        <div class="text-[10px] text-slate-500 font-mono tracking-wider mt-0.5">SHA256: {{ Str::limit($alert->sha256, 16) }}...</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($alert->server_hostname)
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"></path></svg>
                                        <span class="text-slate-300 text-xs font-mono">{{ $alert->server_hostname }}</span>
                                    </div>
                                @else
                                    <span class="text-slate-600 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider
                                    {{ $alert->verdict === 'MALICIOUS' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : '' }}
                                    {{ $alert->verdict === 'SUSPICIOUS' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : '' }}
                                    {{ $alert->verdict === 'CLEAN' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : '' }}">
                                    {{ $alert->verdict }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-300 text-xs">
                                {{ Str::limit($alert->detected_by, 25) }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider
                                    {{ $alert->status === 'Pending' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : '' }}
                                    {{ $alert->status === 'In Progress' ? 'bg-blue-500/20 text-blue-400 border border-blue-500/30' : '' }}
                                    {{ $alert->status === 'Resolved' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : '' }}">
                                    {{ $alert->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-400">
                                <div class="text-white">{{ $alert->created_at->format('M d, Y') }}</div>
                                <div class="text-[10px] tracking-wider mt-0.5">{{ $alert->created_at->format('H:i:s') }}</div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('webhook-alerts.show', $alert->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-lg transition-colors border border-slate-700 text-xs font-medium">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                <svg class="w-12 h-12 mx-auto text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                <div>No webhook alerts yet.</div>
                                <div class="text-xs mt-1">Alerts will appear here when threats are detected from webhook file scans.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($alerts->hasPages())
        <div class="px-6 py-4 border-t border-white/5 bg-slate-900/40">
            {{ $alerts->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
