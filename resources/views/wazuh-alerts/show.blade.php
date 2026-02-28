@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('wazuh-alerts.index') }}" class="text-slate-400 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-sm font-black
                        bg-{{ $wazuhAlert->severity_color }}-500/20 text-{{ $wazuhAlert->severity_color }}-400 border border-{{ $wazuhAlert->severity_color }}-500/30">
                        {{ $wazuhAlert->rule_level }}
                    </span>
                    <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-{{ $wazuhAlert->severity_color }}-500/20 text-{{ $wazuhAlert->severity_color }}-400 border border-{{ $wazuhAlert->severity_color }}-500/30">
                        {{ $wazuhAlert->severity }}
                    </span>
                    <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider
                        {{ $wazuhAlert->status === 'New' ? 'bg-blue-500/20 text-blue-400 border border-blue-500/30' : '' }}
                        {{ $wazuhAlert->status === 'Acknowledged' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : '' }}
                        {{ $wazuhAlert->status === 'Resolved' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : '' }}">
                        {{ $wazuhAlert->status }}
                    </span>
                </div>
                <h1 class="text-lg font-bold text-white mt-2">{{ $wazuhAlert->rule_description ?? 'Wazuh Alert' }}</h1>
                <p class="text-xs text-slate-400 mt-1">
                    Rule {{ $wazuhAlert->rule_id }} &bull;
                    Alert ID: <span class="font-mono">{{ $wazuhAlert->alert_id ?? 'N/A' }}</span> &bull;
                    {{ $wazuhAlert->created_at->format('M d, Y H:i:s') }}
                </p>
            </div>
        </div>

        <!-- Status Update -->
        <form method="POST" action="{{ route('wazuh-alerts.update-status', $wazuhAlert->id) }}" class="flex items-center gap-2">
            @csrf
            @method('PATCH')
            <select name="status" class="bg-slate-800 border border-slate-700 text-slate-300 rounded-lg text-xs px-3 py-2 focus:ring-2 focus:ring-blue-500">
                <option value="New" {{ $wazuhAlert->status === 'New' ? 'selected' : '' }}>New</option>
                <option value="Acknowledged" {{ $wazuhAlert->status === 'Acknowledged' ? 'selected' : '' }}>Acknowledged</option>
                <option value="Resolved" {{ $wazuhAlert->status === 'Resolved' ? 'selected' : '' }}>Resolved</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-xs font-bold transition-colors">
                Update
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Rule Info -->
            <div class="glass-panel p-5 rounded-2xl border border-white/5">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Rule Information
                </h3>
                <div class="space-y-3">
                    <div>
                        <div class="text-[10px] text-slate-500 uppercase tracking-wider mb-1">Rule ID</div>
                        <div class="text-sm text-white font-mono bg-slate-900/50 p-2 rounded">{{ $wazuhAlert->rule_id ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] text-slate-500 uppercase tracking-wider mb-1">Level</div>
                        <div class="text-sm text-white bg-slate-900/50 p-2 rounded">{{ $wazuhAlert->rule_level }} — {{ $wazuhAlert->severity }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] text-slate-500 uppercase tracking-wider mb-1">Groups</div>
                        <div class="flex flex-wrap gap-1 mt-1">
                            @if($wazuhAlert->rule_groups)
                                @foreach($wazuhAlert->rule_groups as $group)
                                    <span class="px-2 py-0.5 bg-slate-800 text-slate-300 rounded text-[10px] font-mono border border-slate-700">{{ $group }}</span>
                                @endforeach
                            @else
                                <span class="text-slate-500 text-xs">N/A</span>
                            @endif
                        </div>
                    </div>
                    @if($wazuhAlert->rule_mitre)
                    <div>
                        <div class="text-[10px] text-slate-500 uppercase tracking-wider mb-1">MITRE ATT&CK</div>
                        <div class="space-y-1">
                            @if(isset($wazuhAlert->rule_mitre['technique']))
                                @foreach((array)$wazuhAlert->rule_mitre['technique'] as $tech)
                                    <span class="inline-block px-2 py-0.5 bg-purple-500/20 text-purple-400 border border-purple-500/30 rounded text-[10px] font-mono mr-1">{{ $tech }}</span>
                                @endforeach
                            @endif
                            @if(isset($wazuhAlert->rule_mitre['tactic']))
                                @foreach((array)$wazuhAlert->rule_mitre['tactic'] as $tactic)
                                    <span class="inline-block px-2 py-0.5 bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 rounded text-[10px] font-mono mr-1">{{ $tactic }}</span>
                                @endforeach
                            @endif
                            @if(isset($wazuhAlert->rule_mitre['id']))
                                @foreach((array)$wazuhAlert->rule_mitre['id'] as $mitreId)
                                    <span class="inline-block px-2 py-0.5 bg-rose-500/20 text-rose-400 border border-rose-500/30 rounded text-[10px] font-bold mr-1">{{ $mitreId }}</span>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Agent Info -->
            <div class="glass-panel p-5 rounded-2xl border border-white/5">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"></path></svg>
                    Agent Details
                </h3>
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <div class="text-[10px] text-slate-500 uppercase tracking-wider mb-1">Agent Name</div>
                            <div class="text-sm text-cyan-400 font-bold">{{ $wazuhAlert->agent_name ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <div class="text-[10px] text-slate-500 uppercase tracking-wider mb-1">Agent ID</div>
                            <div class="text-sm text-slate-300 font-mono">{{ $wazuhAlert->agent_id ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div>
                        <div class="text-[10px] text-slate-500 uppercase tracking-wider mb-1">Agent IP</div>
                        <div class="text-sm text-slate-300 font-mono bg-slate-900/50 p-2 rounded">{{ $wazuhAlert->agent_ip ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] text-slate-500 uppercase tracking-wider mb-1">Manager</div>
                        <div class="text-sm text-slate-300 font-mono bg-slate-900/50 p-2 rounded">{{ $wazuhAlert->manager_name ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>

            <!-- Network Info -->
            @if($wazuhAlert->src_ip || $wazuhAlert->dst_ip)
            <div class="glass-panel p-5 rounded-2xl border border-white/5">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                    Network Details
                </h3>
                <div class="space-y-3">
                    @if($wazuhAlert->src_ip)
                    <div>
                        <div class="text-[10px] text-slate-500 uppercase tracking-wider mb-1">Source</div>
                        <div class="text-sm text-slate-300 font-mono bg-slate-900/50 p-2 rounded">
                            {{ $wazuhAlert->src_ip }}{{ $wazuhAlert->src_port ? ':' . $wazuhAlert->src_port : '' }}
                            @if($wazuhAlert->src_user) <span class="text-amber-400">({{ $wazuhAlert->src_user }})</span> @endif
                        </div>
                    </div>
                    @endif
                    @if($wazuhAlert->dst_ip)
                    <div>
                        <div class="text-[10px] text-slate-500 uppercase tracking-wider mb-1">Destination</div>
                        <div class="text-sm text-slate-300 font-mono bg-slate-900/50 p-2 rounded">
                            {{ $wazuhAlert->dst_ip }}{{ $wazuhAlert->dst_port ? ':' . $wazuhAlert->dst_port : '' }}
                            @if($wazuhAlert->dst_user) <span class="text-amber-400">({{ $wazuhAlert->dst_user }})</span> @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Event Data (Suricata / Wazuh Data) -->
            @if(isset($wazuhAlert->raw_json['data']) && is_array($wazuhAlert->raw_json['data']))
            <div class="glass-panel p-5 rounded-2xl border border-white/5">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                    Event Data
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @if(isset($wazuhAlert->raw_json['data']['alert']))
                    <div class="space-y-3">
                        <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider border-b border-white/5 pb-1 mb-2">Alert Details</h4>
                        <div>
                            <div class="text-[10px] text-slate-500 uppercase tracking-wider mb-1">Signature</div>
                            <div class="text-sm text-white font-medium">{{ $wazuhAlert->raw_json['data']['alert']['signature'] ?? 'N/A' }}</div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <div class="text-[10px] text-slate-500 uppercase tracking-wider mb-1">Category</div>
                                <div class="text-sm text-slate-300">{{ $wazuhAlert->raw_json['data']['alert']['category'] ?? 'N/A' }}</div>
                            </div>
                            <div>
                                <div class="text-[10px] text-slate-500 uppercase tracking-wider mb-1">Action</div>
                                <div class="text-sm text-slate-300">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ ($wazuhAlert->raw_json['data']['alert']['action'] ?? '') == 'allowed' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400' }}">
                                        {{ $wazuhAlert->raw_json['data']['alert']['action'] ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <div class="space-y-3">
                        <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider border-b border-white/5 pb-1 mb-2">Network Context</h4>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <div class="text-[10px] text-slate-500 uppercase tracking-wider mb-1">Protocol</div>
                                <div class="text-sm text-slate-300 font-mono">{{ $wazuhAlert->raw_json['data']['proto'] ?? 'N/A' }}</div>
                            </div>
                            <div>
                                <div class="text-[10px] text-slate-500 uppercase tracking-wider mb-1">Interface</div>
                                <div class="text-sm text-slate-300 font-mono">{{ $wazuhAlert->raw_json['data']['in_iface'] ?? 'N/A' }}</div>
                            </div>
                        </div>
                        @if(isset($wazuhAlert->raw_json['data']['metadata']['flowbits']))
                        <div>
                            <div class="text-[10px] text-slate-500 uppercase tracking-wider mb-1">Flowbits</div>
                            <div class="flex flex-wrap gap-1 mt-1">
                                @foreach((array)$wazuhAlert->raw_json['data']['metadata']['flowbits'] as $flowbit)
                                    <span class="px-2 py-0.5 bg-slate-800 text-slate-300 rounded text-[10px] font-mono border border-slate-700">{{ $flowbit }}</span>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Full Log -->
            <div class="glass-panel p-5 rounded-2xl border border-white/5">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Full Log
                </h3>
                <div class="bg-slate-900/80 rounded-lg p-4 overflow-x-auto">
                    <pre class="text-xs text-slate-300 font-mono whitespace-pre-wrap leading-relaxed">{{ $wazuhAlert->full_log ?? 'No log data available.' }}</pre>
                </div>
                @if($wazuhAlert->location)
                <div class="mt-3 text-[10px] text-slate-500">
                    <span class="font-bold uppercase tracking-wider">Location:</span> {{ $wazuhAlert->location }}
                    @if($wazuhAlert->decoder_name) &bull; <span class="font-bold uppercase tracking-wider">Decoder:</span> {{ $wazuhAlert->decoder_name }} @endif
                </div>
                @endif
            </div>

            <!-- Syscheck (FIM) -->
            @if($wazuhAlert->syscheck)
            <div class="glass-panel p-5 rounded-2xl border border-white/5">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    File Integrity Monitoring (Syscheck)
                </h3>
                <div class="bg-slate-900/80 rounded-lg p-4 overflow-x-auto">
                    <pre class="text-xs text-slate-300 font-mono whitespace-pre-wrap">{{ json_encode($wazuhAlert->syscheck, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
            @endif

            <!-- Raw JSON -->
            <div class="glass-panel p-5 rounded-2xl border border-white/5">
                <div x-data="{ expanded: false }">
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                            Raw Wazuh Alert JSON
                        </span>
                        <button @click="expanded = !expanded" class="text-xs text-blue-400 hover:text-blue-300 flex items-center gap-1 font-medium">
                            <svg class="w-3 h-3 transition-transform" :class="expanded ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            <span x-text="expanded ? 'Collapse' : 'Expand'"></span>
                        </button>
                    </h3>
                    <div x-show="expanded" x-collapse>
                        <div class="bg-slate-900/80 rounded-lg p-4 overflow-x-auto max-h-[500px] overflow-y-auto">
                            <pre class="text-[10px] text-slate-300 font-mono">{{ json_encode($wazuhAlert->raw_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
