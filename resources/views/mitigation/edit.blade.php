@extends('layouts.dashboard')

@section('content')
<div class="max-w-5xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <a href="{{ route('mitigation-logs.index') }}" class="inline-flex items-center text-slate-400 hover:text-white mb-4 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7"></path></svg>
            Back to Investigations
        </a>
        <h1 class="text-3xl font-bold text-white tracking-tight">Edit Investigation</h1>
        <p class="text-slate-400 mt-2">Update status, details, and analysis for {{ $mitigationLog->title }}</p>
    </div>

    <script>
        window.__investigationData = {
            fileAnalysisData: @json($fileAnalysisData),
            urlAnalysisData: @json($urlAnalysisData),
            ipAnalyses: @json($ipAnalyses),
            searchEmailUrl: '{{ route('mitigation-logs.search-emails') }}',
            existing: {
                type: @json(old('type', $mitigationLog->type ?? 'General')),
                status: @json(old('status', $mitigationLog->status ?? 'Pending')),
                reporterDepartment: @json(old('reporter_department', $mitigationLog->reporter_department ?? '')),
                analysisSummary: @json(old('analysis_summary', $mitigationLog->analysis_summary ?? '')),
                selectedFileId: @json(old('file_analysis_log_id', $mitigationLog->file_analysis_log_id ?? '')),
                selectedUrlId: @json(old('url_analysis_log_id', $mitigationLog->url_analysis_log_id ?? '')),
                selectedIpId: @json(old('ip_analysis_id', $mitigationLog->ip_analysis_id ?? '')),
                includeIpAnalyzer: @json(old('ip_analysis_id', $mitigationLog->ip_analysis_id) ? true : false)
            }
        };
    </script>

    <form action="{{ route('mitigation-logs.update', $mitigationLog) }}" method="POST" enctype="multipart/form-data" class="space-y-6" x-data="investigationForm()">
        @csrf
        @method('PUT')


        <!-- 1. Investigation Type & Core Info -->
        <div class="glass-panel p-6 rounded-2xl border border-slate-800 relative z-20">
            <h2 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-500 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
                Incident Overview
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Type Selection (4 Cards) -->
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-400 mb-3">Investigation Type</label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @if($mitigationLog->type === 'General')
                        <label class="cursor-pointer relative">
                            <input type="radio" name="type" value="General" x-model="type" class="peer sr-only">
                            <div class="p-4 rounded-xl border border-slate-700 bg-slate-900/50 hover:bg-slate-800 transition-all peer-checked:border-blue-500 peer-checked:bg-blue-500/10 peer-checked:shadow-lg peer-checked:shadow-blue-500/10">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                    </div>
                                    <div>
                                        <div class="font-bold text-white text-sm">General Incident</div>
                                        <div class="text-[10px] text-slate-400">System failures, malware</div>
                                    </div>
                                </div>
                            </div>
                        </label>
                        @endif

                        @if($mitigationLog->type === 'Email Phishing')
                        <label class="cursor-pointer relative">
                            <input type="radio" name="type" value="Email Phishing" x-model="type" class="peer sr-only">
                            <div class="p-4 rounded-xl border border-slate-700 bg-slate-900/50 hover:bg-slate-800 transition-all peer-checked:border-amber-500 peer-checked:bg-amber-500/10 peer-checked:shadow-lg peer-checked:shadow-amber-500/10">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <div>
                                        <div class="font-bold text-white text-sm">Email Phishing</div>
                                        <div class="text-[10px] text-slate-400">Suspicious emails, spam</div>
                                    </div>
                                </div>
                            </div>
                        </label>
                        @endif

                        @if($mitigationLog->type === 'File Check')
                        <label class="cursor-pointer relative">
                            <input type="radio" name="type" value="File Check" x-model="type" class="peer sr-only">
                            <div class="p-4 rounded-xl border border-slate-700 bg-slate-900/50 hover:bg-slate-800 transition-all peer-checked:border-teal-500 peer-checked:bg-teal-500/10 peer-checked:shadow-lg peer-checked:shadow-teal-500/10">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </div>
                                    <div>
                                        <div class="font-bold text-white text-sm">File Check</div>
                                        <div class="text-[10px] text-slate-400">File analysis verdict</div>
                                    </div>
                                </div>
                            </div>
                        </label>
                        @endif

                        @if($mitigationLog->type === 'Domain Check')
                        <label class="cursor-pointer relative">
                            <input type="radio" name="type" value="Domain Check" x-model="type" class="peer sr-only">
                            <div class="p-4 rounded-xl border border-slate-700 bg-slate-900/50 hover:bg-slate-800 transition-all peer-checked:border-purple-500 peer-checked:bg-purple-500/10 peer-checked:shadow-lg peer-checked:shadow-purple-500/10">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                                    </div>
                                    <div>
                                        <div class="font-bold text-white text-sm">Domain Check</div>
                                        <div class="text-[10px] text-slate-400">URL/domain verdict</div>
                                    </div>
                                </div>
                            </div>
                        </label>
                        @endif
                    </div>
                </div>

                <!-- Title -->
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-medium text-slate-400 mb-2">Title / Subject</label>
                    <input type="text" name="title" required value="{{ old('title', $mitigationLog->title) }}" class="w-full bg-slate-950/50 border border-slate-700 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm">
                </div>

                <!-- Attack Classification -->
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Attack Classification</label>
                    <div class="relative">
                        <select name="attack_classification" 
                                class="w-full bg-slate-950/50 border border-slate-700 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm appearance-none disabled:opacity-50 disabled:cursor-not-allowed"
                                {{ $mitigationLog->status == 'Pending' ? 'disabled' : '' }}
                                title="{{ $mitigationLog->status == 'Pending' ? 'Save as In Progress first to enable classification' : '' }}">
                            <option value="">-- Select --</option>
                            <option value="True Attack" {{ old('attack_classification', $mitigationLog->attack_classification) === 'True Attack' ? 'selected' : '' }}>🔴 True Attack</option>
                            <option value="False Attack" {{ old('attack_classification', $mitigationLog->attack_classification) === 'False Attack' ? 'selected' : '' }}>🟢 False Attack</option>
                        </select>
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                    @if($mitigationLog->status == 'Pending')
                    <p class="text-xs text-amber-500 mt-1 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Save as "In Progress" first to classify.
                    </p>
                    @endif
                </div>

                <!-- Incident Time -->
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Incident Time</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </span>
                         <input type="datetime-local" name="incident_time" value="{{ old('incident_time', $mitigationLog->incident_time ? \Carbon\Carbon::parse($mitigationLog->incident_time)->format('Y-m-d\TH:i') : '') }}" class="w-full bg-slate-950/50 border border-slate-700 rounded-lg pl-10 pr-4 py-3 text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm [color-scheme:dark]">
                    </div>
                </div>

                <!-- Priority & Severity (Grid) -->
                <div class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Priority -->
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-2">Priority</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                               <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </span>
                            <select name="priority" class="w-full bg-slate-950/50 border border-slate-700 rounded-lg pl-10 pr-4 py-3 text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm appearance-none cursor-pointer">
                                <option value="">Select Priority</option>
                                <option value="Low" {{ old('priority', $mitigationLog->priority) == 'Low' ? 'selected' : '' }}>Low</option>
                                <option value="Medium" {{ old('priority', $mitigationLog->priority) == 'Medium' ? 'selected' : '' }}>Medium</option>
                                <option value="High" {{ old('priority', $mitigationLog->priority) == 'High' ? 'selected' : '' }}>High</option>
                                <option value="Critical" {{ old('priority', $mitigationLog->priority) == 'Critical' ? 'selected' : '' }}>Critical</option>
                            </select>
                             <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Severity -->
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-2">Severity</label>
                         <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                               <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            </span>
                            <select name="severity" class="w-full bg-slate-950/50 border border-slate-700 rounded-lg pl-10 pr-4 py-3 text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm appearance-none cursor-pointer">
                                <option value="">Select Severity</option>
                                <option value="Low" {{ old('severity', $mitigationLog->severity) == 'Low' ? 'selected' : '' }}>Low</option>
                                <option value="Medium" {{ old('severity', $mitigationLog->severity) == 'Medium' ? 'selected' : '' }}>Medium</option>
                                <option value="High" {{ old('severity', $mitigationLog->severity) == 'High' ? 'selected' : '' }}>High</option>
                                <option value="Critical" {{ old('severity', $mitigationLog->severity) == 'Critical' ? 'selected' : '' }}>Critical</option>
                            </select>
                             <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Affected Assets (General only) -->
                @if($mitigationLog->type === 'General')
                <div class="col-span-1 md:col-span-2 bg-slate-900/40 p-5 rounded-lg border border-slate-800/50">
                     <div class="flex items-center gap-2 mb-4 border-b border-slate-800 pb-2">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        <label class="block text-sm font-bold text-slate-300">Affected Assets</label>
                     </div>
                     <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Hostname -->
                        <div>
                             <label class="block text-xs text-slate-500 mb-1">Hostname</label>
                             <input type="text" name="hostname" value="{{ old('hostname', $mitigationLog->hostname) }}" class="w-full bg-slate-950/50 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm text-sm" placeholder="e.g. SRV-DB-01">
                        </div>
                         <!-- Internal IP -->
                        <div>
                             <label class="block text-xs text-slate-500 mb-1">Internal IP</label>
                             <input type="text" name="internal_ip" value="{{ old('internal_ip', $mitigationLog->internal_ip) }}" class="w-full bg-slate-950/50 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm text-sm font-mono" placeholder="e.g. 192.168.1.50">
                        </div>
                         <!-- OS -->
                        <div>
                             <label class="block text-xs text-slate-500 mb-1">OS / Platform</label>
                             <input type="text" name="os" value="{{ old('os', $mitigationLog->os) }}" class="w-full bg-slate-950/50 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm text-sm" placeholder="e.g. Ubuntu 22.04 LTS">
                        </div>
                         <!-- Network Zone -->
                        <div>
                             <label class="block text-xs text-slate-500 mb-1">Network Zone</label>
                             <input type="text" name="network_zone" value="{{ old('network_zone', $mitigationLog->network_zone) }}" class="w-full bg-slate-950/50 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm text-sm" placeholder="e.g. DMZ / Production">
                        </div>
                     </div>
                </div>
                @endif


                <!-- System Affected (Legacy / Additional) (General only) -->
                <div x-show="type === 'General'" class="col-span-1 md:col-span-2">
                     <label class="block text-sm font-medium text-slate-400 mb-2">Affected System / Asset (Legacy / Description)</label>
                     <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 01-2 2v4a2 2 0 012 2h14a2 2 0 012-2v-4a2 2 0 01-2-2m-2-4h.01M17 16h.01"></path></svg>
                        </span>
                        <input type="text" name="system_affected" value="{{ old('system_affected', $mitigationLog->system_affected) }}" class="w-full bg-slate-950/50 border border-slate-700 rounded-lg pl-10 pr-4 py-3 text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm" placeholder="Additional system details if needed...">
                     </div>
                     <p class="text-xs text-slate-500 mt-1">For new incidents, please prefer using the detailed Asset fields above.</p>
                </div>

                <!-- Reported By & Department (Hidden for General) -->
                <div x-show="type !== 'General'">
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-2">Reported By</label>
                        <div class="relative">
                            <input type="text" id="reporter_email" name="reporter_email" value="{{ old('reporter_email', $mitigationLog->reporter_email) }}" 
                                @input.debounce.300ms="search($el.value, 'reporter')"
                                autocomplete="off"
                                class="w-full bg-slate-950/50 border border-slate-700 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm"
                                placeholder="Start typing name or email...">
                            
                            <div x-show="reporterSuggestions.length > 0" @click.away="reporterSuggestions = []" class="absolute z-50 w-full bg-slate-900 border border-slate-700 rounded-lg shadow-xl mt-1 max-h-48 overflow-y-auto">
                                <template x-for="suggestion in reporterSuggestions" :key="suggestion.email_address">
                                    <div @click="selectEmail(suggestion.email_address, 'reporter', suggestion.department)" class="px-4 py-2 hover:bg-slate-800 cursor-pointer text-sm text-slate-200 border-b border-slate-800 last:border-0">
                                        <span x-text="suggestion.display_name" class="font-bold block text-blue-500"></span>
                                        <div class="flex items-center gap-2">
                                            <span x-text="suggestion.email_address" class="text-xs text-slate-400"></span>
                                            <span x-show="suggestion.department" x-text="suggestion.department" class="text-[10px] bg-emerald-500/10 text-emerald-400 px-1.5 py-0.5 rounded"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-slate-400 mb-2">Department</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            </span>
                            <input type="text" name="reporter_department" x-model="reporterDepartment" 
                                class="w-full bg-slate-950/50 border border-slate-700 rounded-lg pl-10 pr-4 py-3 text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm"
                                placeholder="Auto-filled or type manually...">
                        </div>
                        <p class="text-xs text-slate-500 mt-1">Auto-filled when selecting a known email. Edit if needed.</p>
                    </div>
                </div>

                <!-- Status -->
                <div>
                     <label class="block text-sm font-medium text-slate-400 mb-2">Current Status</label>
                     <div class="relative">
                        <select name="status" x-model="status" class="w-full bg-slate-950/50 border border-slate-700 rounded-lg px-4 py-3 text-white appearance-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm">
                            <option value="Pending">Pending & Review</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Resolved" :disabled="{{ $mitigationLog->status == 'Pending' ? 'true' : 'false' }}">Resolved & Verified</option>
                        </select>
                         <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-400">
                             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                         </div>
                     </div>
                </div>

                <!-- Analyst Decision (Visible only if Status is In Progress or Resolved) -->
                <div x-show="status === 'In Progress' || status === 'Resolved'" x-transition>
                    <label class="block text-sm font-medium text-blue-400 mb-2">Decision / Analysis Result</label>
                    <div class="relative">
                        <textarea name="analyst_decision" rows="3" 
                            class="w-full bg-slate-950/50 border border-blue-500/30 rounded-lg px-4 py-3 text-white placeholder-slate-600 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm resize-none"
                            placeholder="Enter your analysis decision or conclusion here...">{{ old('analyst_decision', $mitigationLog->analyst_decision) }}</textarea>
                        <div class="absolute top-3 right-3">
                            <span class="flex h-2 w-2 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                            </span>
                        </div>
                    </div>
                    <p class="text-xs text-blue-400/70 mt-1">Required for ongoing investigations. Please summarize your findings.</p>
                </div>

                <!-- IP Analyzer Integration -->
                <div x-show="status === 'In Progress' || status === 'Resolved'" x-transition class="mt-4">
                    <div class="bg-slate-900/50 border border-slate-700 rounded-xl p-4 space-y-4">
                        <!-- Checkbox -->
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" x-model="includeIpAnalyzer" class="w-5 h-5 rounded border-slate-600 bg-slate-800 text-purple-500 focus:ring-purple-500 focus:ring-offset-0 cursor-pointer">
                            <span class="text-sm font-medium text-purple-400 group-hover:text-purple-300 transition-colors flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                                Ikut Sertakan IP Analyzer
                            </span>
                        </label>

                        <!-- IP Selection -->
                        <div x-show="includeIpAnalyzer" x-transition>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Pilih IP Address</label>
                            <div class="relative">
                                <select name="ip_analysis_id" x-model="selectedIpId" @change="onIpSelect($event.target.value)"
                                    class="w-full bg-slate-950/50 border border-purple-500/30 rounded-lg px-4 py-3 text-white appearance-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all shadow-sm">
                                    <option value="">-- Pilih IP yang telah dianalisis --</option>
                                    <template x-for="ip in ipAnalyses" :key="ip.id">
                                        <option :value="ip.id" :selected="ip.id == selectedIpId" x-text="ip.ip_address + ' — Risk Score: ' + ip.risk_score + '/100'"></option>
                                    </template>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-purple-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>

                            <!-- IP Analysis Result Preview -->
                            <div x-show="selectedIpData" x-transition class="mt-4 bg-slate-950 border border-purple-500/20 rounded-lg p-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-bold text-purple-400 uppercase tracking-wider">IP Analysis Result</h4>
                                    <span class="text-xs font-mono px-2 py-1 rounded-full"
                                        :class="selectedIpData && selectedIpData.risk_score >= 70 ? 'bg-red-500/10 text-red-400 border border-red-500/20' : (selectedIpData && selectedIpData.risk_score >= 40 ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20')"
                                        x-text="selectedIpData ? 'Risk: ' + selectedIpData.risk_score + '/100' : ''">
                                    </span>
                                </div>

                                <div class="grid grid-cols-2 gap-3 text-sm">
                                    <div>
                                        <span class="text-xs text-slate-500 uppercase">IP</span>
                                        <p class="text-white font-mono" x-text="selectedIpData?.ip_address"></p>
                                    </div>
                                    <div>
                                        <span class="text-xs text-slate-500 uppercase">Country</span>
                                        <p class="text-white" x-text="selectedIpData?.country || '-'"></p>
                                    </div>
                                    <div>
                                        <span class="text-xs text-slate-500 uppercase">ISP / Org</span>
                                        <p class="text-white" x-text="selectedIpData?.isp || '-'"></p>
                                    </div>
                                    <div>
                                        <span class="text-xs text-slate-500 uppercase">AbuseIPDB Confidence</span>
                                        <p class="text-white" x-text="selectedIpData?.abuseConfidence ? selectedIpData.abuseConfidence + '%' : '-'"></p>
                                    </div>
                                </div>

                                <!-- VT Stats -->
                                <template x-if="selectedIpData?.vtMalicious !== undefined">
                                    <div class="bg-slate-900 rounded-lg p-3 border border-slate-800">
                                        <span class="text-xs text-slate-500 uppercase block mb-2">VirusTotal Detection</span>
                                        <div class="flex items-center gap-4 text-sm">
                                            <span class="text-red-400" x-text="'Malicious: ' + (selectedIpData?.vtMalicious || 0)"></span>
                                            <span class="text-amber-400" x-text="'Suspicious: ' + (selectedIpData?.vtSuspicious || 0)"></span>
                                            <span class="text-emerald-400" x-text="'Clean: ' + (selectedIpData?.vtHarmless || 0)"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Email Specific Details (Conditional) -->
        <div x-show="type === 'Email Phishing'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" class="glass-panel p-6 rounded-2xl border border-amber-500/30 bg-amber-500/5 relative z-10">
            <h2 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-amber-500/20 text-amber-500 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                </span>
                Phishing Analysis Details
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-medium text-amber-400 mb-2">Email Subject Line</label>
                    <input type="text" name="email_subject" value="{{ old('email_subject', $mitigationLog->email_subject) }}" class="w-full bg-slate-950/50 border border-slate-700 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all shadow-sm">
                </div>
                
                <div>
                     <label class="block text-sm font-medium text-amber-400 mb-2">Sender Address (From)</label>
                     <div class="relative">
                        <input type="text" id="sender_email" name="email_sender" value="{{ old('email_sender', $mitigationLog->email_sender) }}" 
                            @input.debounce.300ms="search($el.value, 'sender')"
                            autocomplete="off"
                            class="w-full bg-slate-950/50 border border-slate-700 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all shadow-sm">
                        
                        <div x-show="senderSuggestions.length > 0" @click.away="senderSuggestions = []" class="absolute z-50 w-full bg-slate-900 border border-slate-700 rounded-lg shadow-xl mt-1 max-h-48 overflow-y-auto">
                            <template x-for="suggestion in senderSuggestions" :key="suggestion.email_address">
                                <div @click="selectEmail(suggestion.email_address, 'sender')" class="px-4 py-2 hover:bg-slate-800 cursor-pointer text-sm text-slate-200 border-b border-slate-800 last:border-0">
                                    <span x-text="suggestion.display_name" class="font-bold block text-amber-500"></span>
                                    <span x-text="suggestion.email_address" class="text-xs text-slate-400"></span>
                                </div>
                            </template>
                        </div>
                     </div>
                </div>

                <div>
                     <label class="block text-sm font-medium text-amber-400 mb-2">Recipient Address (To)</label>
                     <div class="relative">
                        <input type="text" id="recipient_email" name="email_recipient" value="{{ old('email_recipient', $mitigationLog->email_recipient) }}" 
                            @input.debounce.300ms="search($el.value, 'recipient')"
                            autocomplete="off"
                            class="w-full bg-slate-950/50 border border-slate-700 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all shadow-sm">
                        
                        <div x-show="recipientSuggestions.length > 0" @click.away="recipientSuggestions = []" class="absolute z-50 w-full bg-slate-900 border border-slate-700 rounded-lg shadow-xl mt-1 max-h-48 overflow-y-auto">
                            <template x-for="suggestion in recipientSuggestions" :key="suggestion.email_address">
                                <div @click="selectEmail(suggestion.email_address, 'recipient')" class="px-4 py-2 hover:bg-slate-800 cursor-pointer text-sm text-slate-200 border-b border-slate-800 last:border-0">
                                    <span x-text="suggestion.display_name" class="font-bold block text-amber-500"></span>
                                    <span x-text="suggestion.email_address" class="text-xs text-slate-400"></span>
                                </div>
                            </template>
                        </div>
                     </div>
                </div>

                <div class="col-span-1 md:col-span-2">
                     <label class="block text-sm font-medium text-amber-400 mb-2">General Email Headers / body mail</label>
                     <textarea name="email_headers" rows="4" class="w-full bg-slate-950/50 border border-slate-700 rounded-lg px-4 py-3 text-xs font-mono text-slate-300 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all shadow-sm">{{ old('email_headers', $mitigationLog->email_headers) }}</textarea>
                </div>
            </div>
        </div>

        <!-- 2b. File Check Details (Conditional) -->
        <div x-show="type === 'File Check'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" class="glass-panel p-6 rounded-2xl border border-teal-500/30 bg-teal-500/5 relative z-10">
            <h2 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-teal-500/20 text-teal-500 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </span>
                File Analysis Reference
            </h2>

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-teal-400 mb-2">Select Analyzed File</label>
                    <div class="relative">
                        <select name="file_analysis_log_id" x-model="selectedFileId" @change="onFileSelect($event.target.value)"
                            class="w-full bg-slate-950/50 border border-slate-700 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all shadow-sm appearance-none cursor-pointer">
                            <option value="">-- Select a previously analyzed file --</option>
                            <template x-for="item in fileAnalysisData" :key="item.id">
                                <option :value="item.id" x-text="item.file_name + ' — ' + item.verdict + ' (' + item.date + ')'" :selected="item.id == selectedFileId"></option>
                            </template>
                        </select>
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Choose from completed file analyses in the File Analyst module.</p>
                </div>

                <div x-show="selectedFileData" x-transition>
                    <label class="block text-sm font-medium text-teal-400 mb-2">Analysis Result Summary</label>
                    <div class="bg-slate-950 rounded-xl border border-slate-700 overflow-hidden">
                        <div class="px-5 py-3 flex items-center justify-between" 
                            :class="selectedFileData?.malicious > 0 ? 'bg-red-500/10 border-b border-red-500/20' : 'bg-emerald-500/10 border-b border-emerald-500/20'">
                            <div class="flex items-center gap-2">
                                <template x-if="selectedFileData?.malicious > 0">
                                    <span class="flex items-center gap-2 text-red-400 font-bold text-sm">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        MALICIOUS
                                    </span>
                                </template>
                                <template x-if="!selectedFileData?.malicious || selectedFileData?.malicious === 0">
                                    <span class="flex items-center gap-2 text-emerald-400 font-bold text-sm">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        CLEAN
                                    </span>
                                </template>
                            </div>
                            <span class="text-xs font-mono px-2 py-0.5 rounded" 
                                :class="selectedFileData?.malicious > 0 ? 'bg-red-500/20 text-red-300' : 'bg-emerald-500/20 text-emerald-300'"
                                x-text="(selectedFileData?.malicious || 0) + '/' + (selectedFileData?.total || 0) + ' detections'"></span>
                        </div>
                        <div class="p-5 space-y-3">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <span class="text-[10px] uppercase tracking-wider text-slate-500 font-bold">File Name</span>
                                    <p class="text-sm text-white font-medium mt-0.5" x-text="selectedFileData?.file_name"></p>
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase tracking-wider text-slate-500 font-bold">Scan Date</span>
                                    <p class="text-sm text-slate-300 mt-0.5" x-text="selectedFileData?.date"></p>
                                </div>
                            </div>
                            <div>
                                <span class="text-[10px] uppercase tracking-wider text-slate-500 font-bold">SHA-256 Hash</span>
                                <p class="text-xs text-slate-400 font-mono mt-0.5 break-all" x-text="selectedFileData?.hash"></p>
                            </div>
                            <div class="pt-2 border-t border-slate-800">
                                <span class="text-[10px] uppercase tracking-wider text-slate-500 font-bold">Detection Breakdown</span>
                                <div class="grid grid-cols-4 gap-2 mt-2">
                                    <div class="bg-red-500/10 border border-red-500/20 rounded-lg p-2 text-center">
                                        <div class="text-lg font-bold text-red-400" x-text="selectedFileData?.malicious || 0"></div>
                                        <div class="text-[10px] text-red-400/70">Malicious</div>
                                    </div>
                                    <div class="bg-orange-500/10 border border-orange-500/20 rounded-lg p-2 text-center">
                                        <div class="text-lg font-bold text-orange-400" x-text="selectedFileData?.suspicious || 0"></div>
                                        <div class="text-[10px] text-orange-400/70">Suspicious</div>
                                    </div>
                                    <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-lg p-2 text-center">
                                        <div class="text-lg font-bold text-emerald-400" x-text="selectedFileData?.harmless || 0"></div>
                                        <div class="text-[10px] text-emerald-400/70">Clean</div>
                                    </div>
                                    <div class="bg-slate-500/10 border border-slate-600 rounded-lg p-2 text-center">
                                        <div class="text-lg font-bold text-slate-400" x-text="selectedFileData?.undetected || 0"></div>
                                        <div class="text-[10px] text-slate-500">Undetected</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <textarea name="analysis_summary" x-model="analysisSummary" class="hidden"></textarea>
                </div>
            </div>
        </div>

        <!-- 2c. Domain Check Details (Conditional) -->
        <div x-show="type === 'Domain Check'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" class="glass-panel p-6 rounded-2xl border border-purple-500/30 bg-purple-500/5 relative z-10">
            <h2 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-purple-500/20 text-purple-500 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                </span>
                Domain / URL Analysis Reference
            </h2>

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-purple-400 mb-2">Select Analyzed URL / Domain</label>
                    <div class="relative">
                        <select name="url_analysis_log_id" x-model="selectedUrlId" @change="onUrlSelect($event.target.value)"
                            class="w-full bg-slate-950/50 border border-slate-700 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all shadow-sm appearance-none cursor-pointer">
                            <option value="">-- Select a previously scanned URL --</option>
                            <template x-for="item in urlAnalysisData" :key="item.id">
                                <option :value="item.id" x-text="item.url + ' — ' + item.verdict + ' (' + item.date + ')'" :selected="item.id == selectedUrlId"></option>
                            </template>
                        </select>
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Choose from completed URL scans in the URL Scanner module.</p>
                </div>

                <div x-show="selectedUrlData" x-transition>
                    <label class="block text-sm font-medium text-purple-400 mb-2">Analysis Result Summary</label>
                    <div class="bg-slate-950 rounded-xl border border-slate-700 overflow-hidden">
                        <div class="px-5 py-3 flex items-center justify-between" 
                            :class="selectedUrlData?.malicious > 0 ? 'bg-red-500/10 border-b border-red-500/20' : 'bg-emerald-500/10 border-b border-emerald-500/20'">
                            <div class="flex items-center gap-2">
                                <template x-if="selectedUrlData?.malicious > 0">
                                    <span class="flex items-center gap-2 text-red-400 font-bold text-sm">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        MALICIOUS
                                    </span>
                                </template>
                                <template x-if="!selectedUrlData?.malicious || selectedUrlData?.malicious === 0">
                                    <span class="flex items-center gap-2 text-emerald-400 font-bold text-sm">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        CLEAN
                                    </span>
                                </template>
                            </div>
                            <span class="text-xs font-mono px-2 py-0.5 rounded" 
                                :class="selectedUrlData?.malicious > 0 ? 'bg-red-500/20 text-red-300' : 'bg-emerald-500/20 text-emerald-300'"
                                x-text="(selectedUrlData?.malicious || 0) + '/' + (selectedUrlData?.total || 0) + ' detections'"></span>
                        </div>
                        <div class="p-5 space-y-3">
                            <div>
                                <span class="text-[10px] uppercase tracking-wider text-slate-500 font-bold">URL</span>
                                <p class="text-sm text-white font-mono mt-0.5 break-all" x-text="selectedUrlData?.url"></p>
                            </div>
                            <div>
                                <span class="text-[10px] uppercase tracking-wider text-slate-500 font-bold">Scan Date</span>
                                <p class="text-sm text-slate-300 mt-0.5" x-text="selectedUrlData?.date"></p>
                            </div>
                            <div class="pt-2 border-t border-slate-800">
                                <span class="text-[10px] uppercase tracking-wider text-slate-500 font-bold">Detection Breakdown</span>
                                <div class="grid grid-cols-4 gap-2 mt-2">
                                    <div class="bg-red-500/10 border border-red-500/20 rounded-lg p-2 text-center">
                                        <div class="text-lg font-bold text-red-400" x-text="selectedUrlData?.malicious || 0"></div>
                                        <div class="text-[10px] text-red-400/70">Malicious</div>
                                    </div>
                                    <div class="bg-orange-500/10 border border-orange-500/20 rounded-lg p-2 text-center">
                                        <div class="text-lg font-bold text-orange-400" x-text="selectedUrlData?.suspicious || 0"></div>
                                        <div class="text-[10px] text-orange-400/70">Suspicious</div>
                                    </div>
                                    <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-lg p-2 text-center">
                                        <div class="text-lg font-bold text-emerald-400" x-text="selectedUrlData?.harmless || 0"></div>
                                        <div class="text-[10px] text-emerald-400/70">Clean</div>
                                    </div>
                                    <div class="bg-slate-500/10 border border-slate-600 rounded-lg p-2 text-center">
                                        <div class="text-lg font-bold text-slate-400" x-text="selectedUrlData?.undetected || 0"></div>
                                        <div class="text-[10px] text-slate-500">Undetected</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <textarea name="analysis_summary" x-model="analysisSummary" class="hidden"></textarea>
                </div>
            </div>
        </div>

        <!-- 3. Description & Narrative -->
        <div class="glass-panel p-6 rounded-2xl border border-slate-800">
             <h2 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </span>
                Investigation Narrative
            </h2>

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Detailed Description</label>
                    <textarea name="description" rows="8" class="w-full bg-slate-950/50 border border-slate-700 rounded-lg px-4 py-3 text-white placeholder-slate-600 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm resize-none leading-relaxed">{{ old('description', $mitigationLog->description) }}</textarea>
                </div>

                <div x-show="type === 'General'">
                    <div class="flex items-center justify-between mb-2">
                         <label class="text-sm font-medium text-slate-400">Raw Technical Data / Logs</label>
                         <span class="text-[10px] uppercase bg-slate-800 text-slate-400 px-2 py-0.5 rounded border border-slate-700">JSON / Log Format</span>
                    </div>
                    <textarea name="event_log" rows="4" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-3 text-xs font-mono text-emerald-400 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500/30 transition-all shadow-inner">{{ old('event_log', $mitigationLog->event_log) }}</textarea>
                </div>
            </div>
        </div>

        <!-- 4. Evidence Upload -->
        <div class="glass-panel p-6 rounded-2xl border border-slate-800">
             <h2 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-purple-500/10 text-purple-500 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </span>
                Evidence & Attachments
            </h2>

            <!-- General Type: Before / After Uploads -->


            <!-- Multi-file Upload -->
            <div class="space-y-4">
                 <label class="block text-sm font-medium text-slate-400">Attachments (Multiple Files Allowed)</label>
                 
                 @if($mitigationLog->files && $mitigationLog->files->count() > 0)
                 <div class="mb-4">
                    <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Existing Files:</h4>
                    <ul class="text-xs text-slate-300 space-y-1">
                        @foreach($mitigationLog->files as $file)
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <span>{{ $file->original_name }}</span>
                            </li>
                        @endforeach
                    </ul>
                 </div>
                 @endif

                 <div class="relative w-full h-32 rounded-xl border-2 border-dashed border-slate-700 bg-slate-900/30 hover:bg-slate-900/50 hover:border-purple-500/50 transition-all flex flex-col items-center justify-center cursor-pointer">
                    <input type="file" name="evidence_files[]" multiple class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" @change="handleMultiFileSelect">
                    <div class="text-center p-4">
                         <svg class="w-8 h-8 text-slate-500 mb-2 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                         <p class="text-xs text-slate-400" id="multi-file-count">Upload more files...</p>
                    </div>
                </div>
                <ul id="file-list" class="text-xs text-slate-400 list-disc list-inside"></ul>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-end gap-4 pt-4">
            <a href="{{ route('mitigation-logs.index') }}" class="px-6 py-3 rounded-xl text-slate-400 hover:text-white hover:bg-white/5 transition-all font-medium">Cancel</a>
            <button type="submit" class="px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold rounded-xl shadow-lg shadow-blue-500/20 transform hover:-translate-y-0.5 transition-all duration-200">
                Update Investigation
            </button>
        </div>
    </form>
</div>

<script>
    function investigationForm() {
        const data = window.__investigationData || {};
        const existing = data.existing || {};
        return {
            type: existing.type || 'General',
            status: existing.status || 'Pending',
            reporterDepartment: existing.reporterDepartment || '',
            analysisSummary: existing.analysisSummary || '',
            selectedFileId: existing.selectedFileId || '',
            selectedUrlId: existing.selectedUrlId || '',
            selectedIpId: existing.selectedIpId || '',
            includeIpAnalyzer: existing.includeIpAnalyzer || false,
            selectedFileData: null,
            selectedUrlData: null,
            selectedIpData: null,
            fileAnalysisData: data.fileAnalysisData || [],
            urlAnalysisData: data.urlAnalysisData || [],
            ipAnalyses: (data.ipAnalyses || []).map(ip => {
                const geo = ip.geo_data || {};
                const vt = ip.virustotal_data || {};
                const abuse = ip.abuseipdb_data || {};
                const vtStats = vt.last_analysis_stats || vt.stats || {};
                return {
                    id: ip.id,
                    ip_address: ip.ip_address,
                    risk_score: ip.risk_score,
                    country: geo.country || geo.country_name || '-',
                    isp: geo.isp || geo.org || geo.as || '-',
                    abuseConfidence: abuse.abuseConfidenceScore ?? abuse.confidence_score ?? null,
                    vtMalicious: vtStats.malicious ?? 0,
                    vtSuspicious: vtStats.suspicious ?? 0,
                    vtHarmless: vtStats.harmless ?? 0,
                    vtUndetected: vtStats.undetected ?? 0,
                    created_at: ip.created_at,
                };
            }),
            reporterSuggestions: [],
            senderSuggestions: [],
            recipientSuggestions: [],
            init() {
                if (this.selectedFileId) { this.onFileSelect(this.selectedFileId); }
                if (this.selectedUrlId) { this.onUrlSelect(this.selectedUrlId); }
                if (this.selectedIpId) { this.onIpSelect(this.selectedIpId); }
            },
            search(query, field) {
                if (query.length < 2) {
                    this[field + 'Suggestions'] = [];
                    return;
                }
                fetch(data.searchEmailUrl + '?query=' + encodeURIComponent(query))
                    .then(res => res.json())
                    .then(results => {
                        this[field + 'Suggestions'] = results;
                    });
            },
            onFileSelect(id) {
                this.selectedFileId = id;
                if (!id) { this.selectedFileData = null; this.analysisSummary = ''; return; }
                const item = this.fileAnalysisData.find(f => f.id == id);
                if (!item) return;
                this.selectedFileData = item;
                let verdict = item.malicious > 0
                    ? '\u26a0\ufe0f MALICIOUS \u2014 ' + item.malicious + ' of ' + item.total + ' security vendors flagged this file as malicious.'
                    : '\u2705 CLEAN \u2014 No threats detected by any of the ' + item.total + ' security vendors.';
                this.analysisSummary = '[File Analysis Report]\n\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501'
                    + '\nFile Name  : ' + item.file_name
                    + '\nSHA-256    : ' + item.hash
                    + '\nScan Date  : ' + item.date
                    + '\n\n[Verdict]\n' + verdict
                    + '\n\n[Detection Statistics]'
                    + '\n\u2022 Malicious  : ' + item.malicious + '/' + item.total + ' vendors'
                    + '\n\u2022 Suspicious : ' + item.suspicious + '/' + item.total + ' vendors'
                    + '\n\u2022 Clean      : ' + item.harmless + '/' + item.total + ' vendors'
                    + '\n\u2022 Undetected : ' + item.undetected + '/' + item.total + ' vendors';
            },
            onUrlSelect(id) {
                this.selectedUrlId = id;
                if (!id) { this.selectedUrlData = null; this.analysisSummary = ''; return; }
                const item = this.urlAnalysisData.find(u => u.id == id);
                if (!item) return;
                this.selectedUrlData = item;
                let verdict = item.malicious > 0
                    ? '\u26a0\ufe0f MALICIOUS \u2014 ' + item.malicious + ' of ' + item.total + ' security vendors flagged this URL as malicious.'
                    : '\u2705 CLEAN \u2014 No threats detected by any of the ' + item.total + ' security vendors.';
                this.analysisSummary = '[URL/Domain Analysis Report]\n\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501\u2501'
                    + '\nURL        : ' + item.url
                    + '\nScan Date  : ' + item.date
                    + '\n\n[Verdict]\n' + verdict
                    + '\n\n[Detection Statistics]'
                    + '\n\u2022 Malicious  : ' + item.malicious + '/' + item.total + ' vendors'
                    + '\n\u2022 Suspicious : ' + item.suspicious + '/' + item.total + ' vendors'
                    + '\n\u2022 Clean      : ' + item.harmless + '/' + item.total + ' vendors'
                    + '\n\u2022 Undetected : ' + item.undetected + '/' + item.total + ' vendors';
            },
            onIpSelect(id) {
                this.selectedIpId = id;
                if (!id) { this.selectedIpData = null; return; }
                const item = this.ipAnalyses.find(i => i.id == id);
                if (!item) return;
                this.selectedIpData = item;
            },
            selectEmail(email, field, department) {
                document.getElementById(field + '_email').value = email;
                if (field === 'reporter' && department) {
                    this.reporterDepartment = department;
                }
                this[field + 'Suggestions'] = [];
            }
        };
    }

    function handleFileSelect(e) {
        const file = e.target.files[0];
        const container = e.target.closest('.group');
        const textEl = container.querySelector('.file-name');
        if (file) {
            textEl.textContent = file.name;
            textEl.classList.add('text-blue-400', 'font-bold');
        }
    }

    function handleMultiFileSelect(e) {
        const files = e.target.files;
        const countEl = document.getElementById('multi-file-count');
        const listEl = document.getElementById('file-list');
        
        if (files.length > 0) {
            countEl.textContent = files.length + ' file(s) selected';
            countEl.classList.add('text-purple-400', 'font-bold');
            
            listEl.innerHTML = '';
            Array.from(files).forEach(file => {
                const li = document.createElement('li');
                li.textContent = file.name;
                listEl.appendChild(li);
            });
        }
    }
</script>
@endsection
