@extends('layouts.dashboard')

@section('content')
<div class="mb-8">
    <div class="flex items-center gap-3 mb-2">
        <div class="p-2 bg-rose-500/10 rounded-lg border border-rose-500/20">
            <svg class="w-6 h-6 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Alerts Bad IP</h1>
            <p class="text-sm text-slate-400 font-medium">Monitoring malicious IP traffic detected by Suricata IDS/IPS</p>
        </div>
    </div>
</div>

<!-- Stats Dashboard -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8" x-data="badIpDashboard()" x-init="startPolling">
    <div class="glass-panel p-5 rounded-2xl border border-white/5 flex items-center justify-between group hover:bg-slate-800/50 transition-colors">
        <div>
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Total Alerts</p>
            <p class="text-3xl font-black text-white leading-none" x-text="stats.total">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-slate-800 flex items-center justify-center text-slate-400 border border-slate-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
        </div>
    </div>

    <div class="glass-panel p-5 rounded-2xl border border-white/5 flex items-center justify-between group hover:bg-slate-800/50 transition-colors">
        <div>
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">New Alerts</p>
            <p class="text-3xl font-black text-blue-400 leading-none" x-text="stats.new">{{ number_format($stats['new']) }}</p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-400 border border-blue-500/20">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>
    </div>

    <div class="glass-panel p-5 rounded-2xl border border-white/5 flex items-center justify-between group hover:bg-slate-800/50 transition-colors">
        <div>
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Acknowledged</p>
            <p class="text-3xl font-black text-amber-400 leading-none" x-text="stats.acknowledged">{{ number_format($stats['acknowledged']) }}</p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-400 border border-amber-500/20">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
        </div>
    </div>

    <div class="glass-panel p-5 rounded-2xl border border-white/5 flex items-center justify-between group hover:bg-slate-800/50 transition-colors">
        <div>
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Resolved</p>
            <p class="text-3xl font-black text-emerald-400 leading-none" x-text="stats.resolved">{{ number_format($stats['resolved']) }}</p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-400 border border-emerald-500/20">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
    </div>
</div>

<div class="glass-panel rounded-2xl border border-white/5 overflow-hidden" x-data="badIpTable()" x-init="startTablePolling">
    <!-- Navbar Filters -->
    <div class="p-4 border-b border-white/5 bg-slate-900/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form method="GET" action="{{ route('bad-ip-alerts.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="relative">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search IP or Rule..." 
                    class="pl-9 pr-4 py-2 bg-slate-800 border border-slate-700 text-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none w-64 placeholder-slate-500 transition-all">
            </div>

            <select name="status" class="bg-slate-800 border border-slate-700 text-slate-200 rounded-lg text-sm px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none appearance-none pr-8">
                <option value="">All Statuses</option>
                <option value="New" {{ request('status') == 'New' ? 'selected' : '' }}>New</option>
                <option value="Acknowledged" {{ request('status') == 'Acknowledged' ? 'selected' : '' }}>Acknowledged</option>
                <option value="Resolved" {{ request('status') == 'Resolved' ? 'selected' : '' }}>Resolved</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 text-sm font-medium rounded-lg transition-colors border border-slate-600">
                Filter
            </button>
            <a href="{{ route('bad-ip-alerts.index') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-slate-300 text-sm font-medium rounded-lg transition-colors border border-slate-700">
                Clear
            </a>
        </form>
    </div>

    <form method="POST" action="{{ route('bad-ip-alerts.bulk-resolve') }}" id="bulk-resolve-form">
        @csrf
        
        <!-- Bulk Actions Bar -->
        <div id="bulk-actions-bar" class="hidden px-4 py-3 bg-indigo-900/40 border-b border-indigo-500/20 flex items-center justify-between">
            <div class="flex items-center gap-3 text-indigo-200 text-sm font-medium">
                <span id="selected-count" class="bg-indigo-500/30 text-indigo-300 px-2.5 py-0.5 rounded-full text-xs border border-indigo-500/30">0</span>
                <span>alerts selected</span>
            </div>
            
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-1.5 bg-emerald-500 hover:bg-emerald-400 text-emerald-950 rounded-lg text-xs font-bold transition-all shadow-lg shadow-emerald-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Mark as Resolved
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-900/80 border-b border-white/5 text-[10px] uppercase tracking-widest text-slate-500">
                        <th class="px-4 py-4 w-12 text-center">
                            <input type="checkbox" id="select-all" class="w-4 h-4 rounded border-slate-700 bg-slate-800 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-slate-900">
                        </th>
                        <th class="px-5 py-4 font-bold">Severity</th>
                        <th class="px-5 py-4 font-bold">Rule Description</th>
                        <th class="px-5 py-4 font-bold">Source IP</th>
                        <th class="px-5 py-4 font-bold">Destination</th>
                        <th class="px-5 py-4 font-bold">Status</th>
                        <th class="px-5 py-4 font-bold">Time</th>
                        <th class="px-5 py-4 font-bold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    
                    <template x-for="alert in alerts" :key="alert.id">
                        <tr class="hover:bg-white/[0.02] transition-colors group">
                            <td class="px-4 py-3 text-center">
                                <input type="checkbox" name="alert_ids[]" :value="alert.id" class="alert-checkbox w-4 h-4 rounded border-slate-700 bg-slate-800 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-slate-900" :disabled="alert.status === 'Resolved'">
                            </td>
                            <!-- Severity -->
                            <td class="px-5 py-3">
                                <span class="px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider border"
                                    :class="{
                                        'bg-rose-500/20 text-rose-400 border-rose-500/30': alert.severity_color === 'rose',
                                        'bg-orange-500/20 text-orange-400 border-orange-500/30': alert.severity_color === 'orange',
                                        'bg-amber-500/20 text-amber-400 border-amber-500/30': alert.severity_color === 'amber',
                                        'bg-blue-500/20 text-blue-400 border-blue-500/30': alert.severity_color === 'blue',
                                        'bg-slate-500/20 text-slate-400 border-slate-500/30': alert.severity_color === 'slate'
                                    }" x-text="alert.signature_severity || 'N/A'">
                                </span>
                            </td>
                            <!-- Rule -->
                            <td class="px-5 py-3 whitespace-normal min-w-[250px]">
                                <div class="text-sm font-medium text-slate-200" x-text="alert.rule_description"></div>
                            </td>
                            <!-- Source IP -->
                            <td class="px-5 py-3">
                                <span class="px-2 py-1 rounded bg-rose-500/10 text-rose-400 border border-rose-500/20 text-xs font-mono" x-text="alert.src_ip"></span>
                            </td>
                            <!-- Destination -->
                            <td class="px-5 py-3">
                                <template x-if="alert.dest_ip">
                                    <div>
                                        <div class="text-xs font-mono text-slate-300" x-text="alert.dest_ip + (alert.dest_port ? ':' + alert.dest_port : '')"></div>
                                        <div class="text-[10px] text-slate-500 uppercase mt-0.5" x-text="alert.proto"></div>
                                    </div>
                                </template>
                                <template x-if="!alert.dest_ip">
                                    <span class="text-slate-600 text-xs">—</span>
                                </template>
                            </td>
                            <!-- Status -->
                            <td class="px-5 py-3">
                                <span class="px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider border"
                                    :class="{
                                        'bg-blue-500/20 text-blue-400 border-blue-500/30': alert.status === 'New',
                                        'bg-amber-500/20 text-amber-400 border-amber-500/30': alert.status === 'Acknowledged',
                                        'bg-emerald-500/20 text-emerald-400 border-emerald-500/30': alert.status === 'Resolved'
                                    }" x-text="alert.status">
                                </span>
                            </td>
                            <!-- Time -->
                            <td class="px-5 py-3 text-slate-400">
                                <div class="text-white text-xs" x-text="formatDate(alert.created_at)"></div>
                                <div class="text-[10px] tracking-wider mt-0.5" x-text="formatTime(alert.created_at)"></div>
                            </td>
                            <!-- Action -->
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <template x-if="alert.status !== 'Resolved'">
                                        <form method="POST" :action="`/bad-ip-alerts/${alert.id}/status`" class="inline-flex">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="Resolved">
                                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 hover:text-emerald-300 rounded-lg transition-colors border border-emerald-500/20 hover:border-emerald-500/30 text-xs font-medium">
                                                Resolve
                                            </button>
                                        </form>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <tr x-show="alerts.length === 0" x-cloak>
                        <td colspan="8" class="px-5 py-12 text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-800 border border-slate-700 mb-4">
                                <svg class="w-8 h-8 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <h3 class="text-sm font-bold text-slate-300 mb-1">No Bad IP Alerts found</h3>
                            <p class="text-xs text-slate-500">Wait for Suricata to detect bad IP traffic or adjust your filters.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </form>
    
    <!-- Pagination -->
    @if($alerts->hasPages())
        <div class="p-4 border-t border-white/5 bg-slate-900/50">
            {{ $alerts->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        window.badIpDashboard = function() {
            return {
                stats: @json($stats),
                startPolling() {
                    setInterval(() => {
                        fetch(window.location.href, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if(data.stats) {
                                this.stats = data.stats;
                            }
                        })
                        .catch(err => console.error('Polling error:', err));
                    }, 5000); // Poll every 5 seconds
                }
            }
        };

        window.badIpTable = function() {
            return {
                alerts: @json($alerts->items()),
                formatDate(dateString) {
                    const d = new Date(dateString);
                    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                },
                formatTime(dateString) {
                    const d = new Date(dateString);
                    return d.toLocaleTimeString('en-US', { hour12: false });
                },
                startTablePolling() {
                    setInterval(() => {
                        fetch(window.location.href, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if(data.alerts && data.alerts.data) {
                                // Simple replacement. For complex state retention (like checked boxes), 
                                // a more robust diffing mechanism is ideal, but full replacement is fine for a realtime log view.
                                this.alerts = data.alerts.data;
                            }
                        })
                        .catch(err => console.error('Table Polling error:', err));
                    }, 5000);
                }
            }
        };
    });

    document.addEventListener('DOMContentLoaded', function() {
        // We use event delegation for checkboxes since rows are re-rendered by Alpine
        const tableContainer = document.querySelector('.overflow-x-auto');
        const selectAllCheckbox = document.getElementById('select-all');
        const bulkActionsBar = document.getElementById('bulk-actions-bar');
        const selectedCountSpan = document.getElementById('selected-count');
        const bulkResolveForm = document.getElementById('bulk-resolve-form');

        function updateBulkActions() {
            const alertCheckboxes = document.querySelectorAll('.alert-checkbox:not(:disabled)');
            const checkedCount = document.querySelectorAll('.alert-checkbox:checked').length;
            
            if (checkedCount > 0) {
                bulkActionsBar.classList.remove('hidden');
                selectedCountSpan.textContent = checkedCount;
            } else {
                bulkActionsBar.classList.add('hidden');
            }
            
            selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < alertCheckboxes.length;
            if (checkedCount === alertCheckboxes.length && alertCheckboxes.length > 0) {
                selectAllCheckbox.checked = true;
                selectAllCheckbox.indeterminate = false;
            } else {
                selectAllCheckbox.checked = false;
            }
        }

        // Event delegation for dynamically rendered Alpine rows
        if(tableContainer) {
            tableContainer.addEventListener('change', function(e) {
                if(e.target && e.target.classList.contains('alert-checkbox')) {
                    updateBulkActions();
                } else if(e.target && e.target.id === 'select-all') {
                    const alertCheckboxes = document.querySelectorAll('.alert-checkbox:not(:disabled)');
                    alertCheckboxes.forEach(cb => {
                        cb.checked = e.target.checked;
                    });
                    updateBulkActions();
                }
            });
        }
        
        if(bulkResolveForm) {
            bulkResolveForm.addEventListener('submit', function(e) {
                if(!confirm('Are you sure you want to mark the selected occurrences as Resolved?')) {
                    e.preventDefault();
                }
            });
        }
        
        updateBulkActions();
    });
</script>
@endpush
@endsection
