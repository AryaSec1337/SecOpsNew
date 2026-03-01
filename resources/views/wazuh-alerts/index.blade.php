@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Wazuh Alerts</h1>
            <p class="text-sm text-slate-400 mt-1">Real-time security alerts received from Wazuh SIEM agents.</p>
        </div>
        <div class="flex items-center gap-3">
            @if($stats['critical'] > 0)
            <div class="flex items-center gap-2 px-3 py-1.5 bg-rose-500/10 border border-rose-500/30 rounded-lg animate-pulse">
                <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span class="text-xs font-bold text-rose-400">{{ $stats['critical'] }} Critical</span>
            </div>
            @endif
            <div class="flex items-center gap-2 px-3 py-1.5 bg-emerald-500/10 border border-emerald-500/30 rounded-lg">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                </span>
                <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider">Live</span>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-2 md:grid-cols-6 gap-3">
        <div class="glass-panel p-4 rounded-2xl">
            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total</div>
            <div class="text-xl font-black text-white mt-1">{{ $stats['total'] }}</div>
        </div>
        <div class="glass-panel p-4 rounded-2xl border-l-2 border-blue-500">
            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">New</div>
            <div class="text-xl font-black text-blue-400 mt-1">{{ $stats['new'] }}</div>
        </div>
        <div class="glass-panel p-4 rounded-2xl border-l-2 border-amber-500">
            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Acknowledged</div>
            <div class="text-xl font-black text-amber-400 mt-1">{{ $stats['acknowledged'] }}</div>
        </div>
        <div class="glass-panel p-4 rounded-2xl border-l-2 border-emerald-500">
            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Resolved</div>
            <div class="text-xl font-black text-emerald-400 mt-1">{{ $stats['resolved'] }}</div>
        </div>
        <div class="glass-panel p-4 rounded-2xl border-l-2 border-rose-500">
            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Critical</div>
            <div class="text-xl font-black text-rose-400 mt-1">{{ $stats['critical'] }}</div>
        </div>
        <div class="glass-panel p-4 rounded-2xl border-l-2 border-orange-500">
            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">High</div>
            <div class="text-xl font-black text-orange-400 mt-1">{{ $stats['high'] }}</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('wazuh-alerts.index') }}" 
           class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ !request('status') && !request('level') ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/20' : 'bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700' }}">
            All
        </a>
        <a href="{{ route('wazuh-alerts.index', ['status' => 'New']) }}" 
           class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ request('status') === 'New' ? 'bg-blue-600 text-white shadow-lg' : 'bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700' }}">
            New
        </a>
        <a href="{{ route('wazuh-alerts.index', ['status' => 'Acknowledged']) }}" 
           class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ request('status') === 'Acknowledged' ? 'bg-amber-600 text-white shadow-lg' : 'bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700' }}">
            Acknowledged
        </a>
        <a href="{{ route('wazuh-alerts.index', ['status' => 'Resolved']) }}" 
           class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ request('status') === 'Resolved' ? 'bg-emerald-600 text-white shadow-lg' : 'bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700' }}">
            Resolved
        </a>
        <span class="w-px h-6 bg-slate-700 mx-1"></span>
        <a href="{{ route('wazuh-alerts.index', ['level' => 12]) }}" 
           class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ request('level') == 12 ? 'bg-rose-600 text-white shadow-lg' : 'bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700' }}">
            🔴 Critical (≥12)
        </a>
        <a href="{{ route('wazuh-alerts.index', ['level' => 10]) }}" 
           class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ request('level') == 10 ? 'bg-orange-600 text-white shadow-lg' : 'bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700' }}">
            🟠 High (≥10)
        </a>
        <a href="{{ route('wazuh-alerts.index', ['level' => 7]) }}" 
           class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ request('level') == 7 ? 'bg-amber-600 text-white shadow-lg' : 'bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700' }}">
            🟡 Medium (≥7)
        </a>

        @if($agents->count() > 0)
        <span class="w-px h-6 bg-slate-700 mx-1"></span>
        <select onchange="if(this.value) window.location.href = '{{ route('wazuh-alerts.index') }}?agent='+this.value; else window.location.href='{{ route('wazuh-alerts.index') }}';" 
                class="bg-slate-800 border border-slate-700 text-slate-300 text-sm rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
            <option value="">All Agents</option>
            @foreach($agents as $agentName)
                <option value="{{ $agentName }}" {{ request('agent') === $agentName ? 'selected' : '' }}>{{ $agentName }}</option>
            @endforeach
        </select>
        @endif
    </div>

    <!-- Alerts Table -->
    <form action="{{ route('wazuh-alerts.bulk-resolve') }}" method="POST" id="bulk-resolve-form">
        @csrf
        <div class="mb-3 flex justify-end">
            <button type="submit" id="bulk-resolve-btn" class="hidden px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-sm font-bold shadow-lg shadow-emerald-600/20 transition-all items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Resolve Selected
            </button>
        </div>
        
    <div class="glass-panel rounded-2xl overflow-hidden border border-white/5 shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="text-xs uppercase bg-slate-900/80 text-slate-400 font-bold tracking-wider">
                    <tr>
                        <th class="px-5 py-4 w-10">
                            <input type="checkbox" id="select-all" class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-blue-500 focus:ring-blue-500/50 focus:ring-offset-slate-900 transition-all">
                        </th>
                        <th class="px-5 py-4">Severity/Level</th>
                        <th class="px-5 py-4">Rule</th>
                        <th class="px-5 py-4">Agent</th>
                        <th class="px-5 py-4">Source IP</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4">Time</th>
                        <th class="px-5 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($alerts as $alert)
                        <tr class="hover:bg-white/5 transition-colors group">
                            <!-- Checkbox -->
                            <td class="px-5 py-3">
                                <input type="checkbox" name="alert_ids[]" value="{{ $alert->id }}" class="alert-checkbox w-4 h-4 rounded border-slate-600 bg-slate-800 text-blue-500 focus:ring-blue-500/50 focus:ring-offset-slate-900 transition-all" {{ $alert->status === 'Resolved' ? 'disabled' : '' }}>
                            </td>
                            <!-- Level Badge -->
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-xs font-black
                                        {{ $alert->rule_level >= 12 ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : '' }}
                                        {{ $alert->rule_level >= 10 && $alert->rule_level < 12 ? 'bg-orange-500/20 text-orange-400 border border-orange-500/30' : '' }}
                                        {{ $alert->rule_level >= 7 && $alert->rule_level < 10 ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : '' }}
                                        {{ $alert->rule_level >= 4 && $alert->rule_level < 7 ? 'bg-blue-500/20 text-blue-400 border border-blue-500/30' : '' }}
                                        {{ $alert->rule_level < 4 ? 'bg-slate-500/20 text-slate-400 border border-slate-500/30' : '' }}">
                                        {{ $alert->rule_level }}
                                    </span>
                                    <span class="text-[10px] font-bold uppercase text-{{ $alert->severity_color }}-400">{{ $alert->severity }}</span>
                                </div>
                            </td>
                            <!-- Rule -->
                            <td class="px-5 py-3 whitespace-normal min-w-[250px] max-w-md">
                                <div>
                                    <div class="font-bold text-white group-hover:text-blue-400 transition-colors text-xs leading-relaxed">{{ $alert->rule_description }}</div>
                                    <div class="text-[10px] text-slate-500 font-mono mt-1">Rule {{ $alert->rule_id }}
                                        @if($alert->rule_groups)
                                            &bull; {{ implode(', ', array_slice($alert->rule_groups, 0, 3)) }}
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <!-- Agent -->
                            <td class="px-5 py-3">
                                @if($alert->agent_name)
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"></path></svg>
                                    <span class="text-slate-300 text-xs font-mono">{{ $alert->agent_name }}</span>
                                </div>
                                <div class="text-[10px] text-slate-500 mt-0.5">{{ $alert->agent_ip }}</div>
                                @else
                                    <span class="text-slate-600 text-xs">—</span>
                                @endif
                            </td>
                            <!-- Source IP -->
                            <td class="px-5 py-3">
                                @if($alert->src_ip)
                                    <span class="text-slate-300 text-xs font-mono">{{ $alert->src_ip }}</span>
                                @else
                                    <span class="text-slate-600 text-xs">—</span>
                                @endif
                            </td>
                            <!-- Status -->
                            <td class="px-5 py-3">
                                <span class="px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider
                                    {{ $alert->status === 'New' ? 'bg-blue-500/20 text-blue-400 border border-blue-500/30' : '' }}
                                    {{ $alert->status === 'Acknowledged' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : '' }}
                                    {{ $alert->status === 'Resolved' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : '' }}">
                                    {{ $alert->status }}
                                </span>
                            </td>
                            <!-- Time -->
                            <td class="px-5 py-3 text-slate-400">
                                <div class="text-white text-xs">{{ $alert->created_at->format('M d, Y') }}</div>
                                <div class="text-[10px] tracking-wider mt-0.5">{{ $alert->created_at->format('H:i:s') }}</div>
                            </td>
                            <!-- Action -->
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('wazuh-alerts.show', $alert->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-lg transition-colors border border-slate-700 text-xs font-medium">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        View
                                    </a>
                                    
                                    @if($alert->status !== 'Resolved')
                                        <form method="POST" action="{{ route('wazuh-alerts.incident', $alert->id) }}" class="escalate-incident-form">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 hover:text-rose-300 rounded-lg transition-colors border border-rose-500/20 hover:border-rose-500/30 text-xs font-medium focus:ring-2 focus:ring-rose-500/50">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                Create Incident
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-500">
                                <svg class="w-12 h-12 mx-auto text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                <div class="font-bold">No Wazuh alerts received yet.</div>
                                <div class="text-xs mt-1">Configure your Wazuh Manager integration to send alerts to this endpoint.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </form>

        
        @if($alerts->hasPages())
        <div class="px-6 py-4 border-t border-white/5 bg-slate-900/40">
            {{ $alerts->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.escalate-incident-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Create Incident Ticket?',
                    text: 'Are you sure you want to escalate this Wazuh Alert into an Incident Ticket? The alert will be marked as Resolved.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#f43f5e',
                    cancelButtonColor: '#334155',
                    confirmButtonText: 'Yes, escalate it!',
                    background: '#1e293b',
                    color: '#f8fafc',
                    customClass: {
                        popup: 'border border-slate-700 rounded-2xl',
                        confirmButton: 'rounded-lg px-4 py-2 font-bold',
                        cancelButton: 'rounded-lg px-4 py-2 font-bold border border-slate-600'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });
</script>
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('select-all');
        const alertCheckboxes = document.querySelectorAll('.alert-checkbox:not([disabled])');
        const bulkResolveBtn = document.getElementById('bulk-resolve-btn');

        // Toggle all checkboxes
        if(selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                alertCheckboxes.forEach(cb => {
                    cb.checked = selectAllCheckbox.checked;
                });
                toggleBulkResolveButton();
            });
        }

        // Toggle individual checkboxes
        alertCheckboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                // Update "select all" state
                const allChecked = Array.from(alertCheckboxes).every(c => c.checked);
                const someChecked = Array.from(alertCheckboxes).some(c => c.checked);
                
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = someChecked && !allChecked;
                
                toggleBulkResolveButton();
            });
        });

        function toggleBulkResolveButton() {
            const anyChecked = Array.from(alertCheckboxes).some(cb => cb.checked);
            if(anyChecked) {
                bulkResolveBtn.classList.remove('hidden');
                bulkResolveBtn.classList.add('inline-flex');
            } else {
                bulkResolveBtn.classList.add('hidden');
                bulkResolveBtn.classList.remove('inline-flex');
            }
        }
    });
</script>
@endpush
@endsection
