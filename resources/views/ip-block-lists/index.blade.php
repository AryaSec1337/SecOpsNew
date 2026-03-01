@extends('layouts.dashboard')

@section('content')
<div class="mb-8" x-data="ipBlockLists()">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-rose-500/10 rounded-lg border border-rose-500/20">
                <svg class="w-6 h-6 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
            </div>
            <div>
                <h1 class="text-2xl font-black text-white tracking-tight">List IP Block (Weekly)</h1>
                <p class="text-sm text-slate-400 font-medium">Weekly aggregation of malicious IPs for infrastructure blocking. Week {{ $week }} / {{ $year }}</p>
            </div>
        </div>
        
        <div class="flex gap-2">
            <a href="{{ route('ip-block-lists.export', ['week' => $week, 'year' => $year, 'status' => request('status', 'All')]) }}" target="_blank" class="px-4 py-2 bg-indigo-500/10 hover:bg-indigo-500/20 border border-indigo-500/20 hover:border-indigo-500/40 text-indigo-400 text-sm font-bold rounded-lg transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Export IPs (.TXT)
            </a>
        </div>
    </div>

    <!-- Stats Dashboard -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="glass-panel p-5 rounded-2xl border border-white/5 flex items-center justify-between group hover:bg-slate-800/50 transition-colors">
            <div>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Total IPs</p>
                <p class="text-3xl font-black text-white leading-none">{{ number_format($stats['total']) }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-slate-800 flex items-center justify-center text-slate-400 border border-slate-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
        </div>

        <div class="glass-panel p-5 rounded-2xl border border-white/5 flex items-center justify-between group hover:bg-slate-800/50 transition-colors">
            <div>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Pending</p>
                <p class="text-3xl font-black text-amber-400 leading-none">{{ number_format($stats['pending']) }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-400 border border-amber-500/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>

        <div class="glass-panel p-5 rounded-2xl border border-white/5 flex items-center justify-between group hover:bg-slate-800/50 transition-colors">
            <div>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Blocked</p>
                <p class="text-3xl font-black text-emerald-400 leading-none">{{ number_format($stats['blocked']) }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-400 border border-emerald-500/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>
    </div>

    <!-- Main Table Panel -->
    <div class="glass-panel rounded-2xl border border-white/5 overflow-hidden">
        <!-- Navbar Filters -->
        <div class="p-4 border-b border-white/5 bg-slate-900/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <form method="GET" action="{{ route('ip-block-lists.index') }}" class="flex flex-wrap items-center gap-3 w-full">
                <!-- Select Week -->
                <select name="week" class="bg-slate-800 border border-slate-700 text-slate-200 rounded-lg text-sm px-4 py-2 focus:ring-2 focus:ring-rose-500 focus:border-rose-500 outline-none appearance-none pr-8">
                    <option value="{{ current_week() }}" {{ $week == current_week() && $year == current_year() ? 'selected' : '' }}>This Week</option>
                    @foreach($availablePeriods as $period)
                        <option value="{{ $period->week_number }}" {{ $week == $period->week_number && $year == $period->year ? 'selected' : '' }}>Week {{ $period->week_number }} ({{ $period->year }})</option>
                    @endforeach
                </select>
                <input type="hidden" name="year" value="{{ $year }}">

                <!-- Status Select -->
                <select name="status" class="bg-slate-800 border border-slate-700 text-slate-200 rounded-lg text-sm px-4 py-2 focus:ring-2 focus:ring-rose-500 focus:border-rose-500 outline-none appearance-none pr-8">
                    <option value="All" {{ request('status') == 'All' ? 'selected' : '' }}>All Status</option>
                    <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="Blocked" {{ request('status') == 'Blocked' ? 'selected' : '' }}>Blocked</option>
                    <option value="Ignored" {{ request('status') == 'Ignored' ? 'selected' : '' }}>Ignored</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 text-sm font-medium rounded-lg transition-colors border border-slate-600">
                    Filter
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-900/80 border-b border-white/5 text-[10px] uppercase tracking-widest text-slate-500">
                        <th class="px-5 py-4 w-12 text-center">No.</th>
                        <th class="px-5 py-4 font-bold">Source IP Info</th>
                        <th class="px-5 py-4 font-bold">Destination</th>
                        <th class="px-5 py-4 font-bold">Context / Reason</th>
                        <th class="px-5 py-4 font-bold text-center">Status</th>
                        <th class="px-5 py-4 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($list as $index => $ip)
                        <tr class="hover:bg-white/[0.02] transition-colors group">
                            <td class="px-5 py-3 text-center text-slate-500 text-xs">{{ $index + 1 }}</td>
                            
                            <!-- IP Address & Source -->
                            <td class="px-5 py-3">
                                <div class="flex flex-col gap-1">
                                    <span class="px-2 py-1 rounded bg-rose-500/10 text-rose-400 border border-rose-500/20 text-xs font-mono w-max">{{ $ip->ip_address }}</span>
                                    <span class="text-[10px] text-slate-400 font-medium tracking-wide uppercase">{{ $ip->source }}</span>
                                </div>
                            </td>

                            <!-- Destination -->
                            <td class="px-5 py-3">
                                @if($ip->dest_ip)
                                    <div>
                                        <div class="text-xs font-mono text-slate-300">{{ $ip->dest_ip }}{{ $ip->dest_port ? ':' . $ip->dest_port : '' }}</div>
                                        <div class="text-[10px] text-slate-500 uppercase mt-0.5">{{ $ip->proto ?? 'TCP' }}</div>
                                    </div>
                                @else
                                    <span class="text-slate-600 text-xs">—</span>
                                @endif
                            </td>

                            <!-- Rule / Reason -->
                            <td class="px-5 py-3 whitespace-normal min-w-[250px]">
                                <div class="text-xs font-medium text-slate-200 line-clamp-2" title="{{ $ip->description }}">{{ $ip->description }}</div>
                                @if($ip->reason)
                                    <div class="mt-2 text-[10px] bg-slate-800/80 border border-slate-700 p-2 rounded text-slate-300">
                                        <span class="text-indigo-400 font-bold uppercase mr-1">Reason:</span> {{ $ip->reason }}
                                    </div>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="px-5 py-3 text-center">
                                @if($ip->status === 'Pending')
                                    <span class="px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider border bg-amber-500/20 text-amber-400 border-amber-500/30">Pending</span>
                                @elseif($ip->status === 'Blocked')
                                    <span class="px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider border bg-emerald-500/20 text-emerald-400 border-emerald-500/30">Blocked</span>
                                @elseif($ip->status === 'Ignored')
                                    <span class="px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider border bg-slate-500/20 text-slate-400 border-slate-500/30">Ignored</span>
                                @endif
                            </td>

                            <!-- Action -->
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" @click="openEditModal({{ $ip->id }}, '{{ $ip->status }}', '{{ addslashes($ip->reason) }}')" class="p-1.5 text-slate-400 hover:text-indigo-400 hover:bg-indigo-400/10 rounded transition-colors" title="Edit Reason/Status">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>

                                    <a href="{{ route('investigation.ip-analyzer.index', ['ip' => $ip->ip_address]) }}" class="p-1.5 text-slate-400 hover:text-amber-400 hover:bg-amber-400/10 rounded transition-colors" title="IP Analyzer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    </a>

                                    <form action="{{ route('ip-block-lists.destroy', $ip) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this log?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-red-400 hover:bg-red-400/10 rounded transition-colors" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-800 border border-slate-700 mb-4">
                                    <svg class="w-8 h-8 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <h3 class="text-sm font-bold text-slate-300 mb-1">No IP Blocks listed</h3>
                                <p class="text-xs text-slate-500">The list for this week is currently empty.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Modal -->
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-slate-950/80 backdrop-blur-sm" style="display: none;">
        <div class="relative w-full max-w-md p-4 sm:p-6 bg-slate-900 border border-slate-700 rounded-2xl shadow-xl" @click.away="closeEditModal()">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-lg font-bold text-white">Edit Block Reason</h3>
                <button type="button" @click="closeEditModal()" class="text-slate-400 hover:bg-slate-800 hover:text-white rounded-lg text-sm p-1.5 ml-auto inline-flex items-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form :action="editActionUrl" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-4">
                    <label class="block mb-2 text-xs font-bold text-slate-300 uppercase tracking-wide">Status</label>
                    <select name="status" x-model="editData.status" class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg text-sm px-4 py-2 focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="Pending">Pending</option>
                        <option value="Blocked">Blocked</option>
                        <option value="Ignored">Ignored</option>
                    </select>
                </div>

                <div class="mb-5">
                    <label class="block mb-2 text-xs font-bold text-slate-300 uppercase tracking-wide">Reason / Details</label>
                    <textarea name="reason" x-model="editData.reason" rows="4" class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg text-sm px-4 py-3 focus:ring-2 focus:ring-indigo-500 outline-none placeholder-slate-500" placeholder="e.g. Sent to infrastructure firewall on MM-DD-YYYY"></textarea>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" @click="closeEditModal()" class="px-4 py-2 text-sm font-medium text-slate-300 bg-slate-800 border border-slate-700 rounded-lg hover:bg-slate-700 transition-colors">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-500 focus:ring-4 focus:ring-indigo-500/50 transition-colors">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('ipBlockLists', () => ({
            showEditModal: false,
            editActionUrl: '',
            editData: {
                status: 'Pending',
                reason: ''
            },
            
            openEditModal(id, status, reason) {
                this.editActionUrl = `/ip-block-lists/${id}`;
                this.editData.status = status;
                this.editData.reason = reason;
                this.showEditModal = true;
            },
            
            closeEditModal() {
                this.showEditModal = false;
            }
        }));
    });
</script>
@endsection

@php
function current_week() { return now()->weekOfYear; }
function current_year() { return now()->year; }
@endphp
