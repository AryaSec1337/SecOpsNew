@extends('layouts.dashboard')

@section('content')
<div class="max-w-7xl mx-auto min-h-screen pb-20" x-data="reportWizard()">
    
    <!-- Top Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-black text-white tracking-tight uppercase flex items-center gap-3">
                <span class="w-1.5 h-8 bg-blue-500 rounded-full shadow-[0_0_15px_rgba(59,130,246,0.5)]"></span>
                SOC L3 Investigation Report
            </h1>
            <p class="text-slate-400 ml-5 font-mono text-sm tracking-wide">Advanced Forensic Documentation Builder</p>
        </div>
        <div class="flex items-center gap-4">
             <button type="button" @click="analyzeModalOpen = true" class="group relative px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-bold rounded-lg shadow-lg shadow-indigo-500/30 overflow-hidden transition-all">
                <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/20 to-transparent translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-1000"></div>
                <span class="relative flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    AI Analyze
                </span>
            </button>
             <div class="text-right">
                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Draft Status</div>
                <div class="text-emerald-400 font-mono text-sm flex items-center justify-end gap-2">
                    <span class="relative flex h-2 w-2">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    Live Saving...
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('reports.store') }}" method="POST" id="reportForm">
        @csrf
        <input type="hidden" name="timeline_json" :value="JSON.stringify(timeline)">
        <input type="hidden" name="iocs_json" :value="JSON.stringify(iocs)">

        <div class="grid grid-cols-12 gap-8">
            
            <!-- LEFT: Navigation Steps -->
            <div class="col-span-12 md:col-span-3">
                <div class="sticky top-24 space-y-3">
                    <template x-for="(step, index) in steps" :key="index">
                        <button type="button" 
                                @click="currentStep = index"
                                class="w-full text-left px-5 py-5 rounded-xl border transition-all duration-300 group relative overflow-hidden"
                                :class="currentStep === index ? 'bg-blue-600/10 border-blue-500/50 text-white shadow-[0_0_20px_rgba(59,130,246,0.15)]' : 'bg-slate-900/40 border-slate-800 text-slate-500 hover:border-slate-700 hover:bg-slate-800/50 hover:text-slate-300'">
                            
                            <div class="absolute inset-0 bg-gradient-to-r from-blue-600/10 to-transparent translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-500 ease-out" x-show="currentStep !== index"></div>

                            <div class="relative z-10 flex items-center justify-between">
                                <div>
                                    <div class="text-[10px] font-bold uppercase tracking-widest mb-1.5" :class="currentStep === index ? 'text-blue-400' : 'text-slate-600'">STEP 0<span x-text="index + 1"></span></div>
                                    <div class="font-bold text-lg tracking-tight" x-text="step.title"></div>
                                </div>
                                <div x-show="currentStep === index" class="w-2 h-2 rounded-full bg-blue-500 shadow-[0_0_10px_rgba(59,130,246,0.8)] animate-pulse"></div>
                            </div>
                        </button>
                    </template>

                    <button type="submit" class="w-full mt-8 bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 text-white font-bold py-4 rounded-xl shadow-[0_0_20px_rgba(16,185,129,0.3)] border border-emerald-400/20 transition-all transform hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-3 group">
                        <svg class="w-5 h-5 text-emerald-100 group-hover:animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Finalize & Publish
                    </button>
                </div>
            </div>

            <!-- RIGHT: Form Content -->
            <div class="col-span-12 md:col-span-9">
                <div class="glass-panel p-8 rounded-2xl border border-slate-800 bg-slate-950/50 backdrop-blur-xl relative min-h-[700px]">
                    
                    <!-- STEP 1: OVERVIEW -->
                    <div x-show="currentStep === 0" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                        <h2 class="text-2xl font-black text-white mb-8 border-b border-white/5 pb-6 flex items-center gap-3">
                            <svg class="w-6 h-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Incident Overview
                        </h2>
                        
                        <div class="grid grid-cols-2 gap-8 mb-8">
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Report Period</label>
                                <input type="month" name="period" required value="{{ now()->format('Y-m') }}" 
                                       class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-4 py-3 text-slate-200 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all placeholder-slate-600 shadow-inner">
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Incident Title</label>
                                <input type="text" name="title" required placeholder="e.g. Advanced Persistent Threat - Finance Sector" 
                                       class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-4 py-3 text-slate-200 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all placeholder-slate-600 shadow-inner">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-8 mb-8">
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">TLP Classification</label>
                                <div class="grid grid-cols-4 gap-3">
                                    <label class="cursor-pointer group">
                                        <input type="radio" name="tlp" value="RED" class="peer sr-only">
                                        <div class="text-center py-3 rounded-lg border border-slate-700 bg-slate-900/50 text-slate-500 text-xs font-bold transition-all peer-checked:bg-red-500 peer-checked:text-white peer-checked:border-red-400 peer-checked:shadow-[0_0_15px_rgba(239,68,68,0.4)] group-hover:border-slate-500">RED</div>
                                    </label>
                                    <label class="cursor-pointer group">
                                        <input type="radio" name="tlp" value="AMBER" class="peer sr-only">
                                        <div class="text-center py-3 rounded-lg border border-slate-700 bg-slate-900/50 text-slate-500 text-xs font-bold transition-all peer-checked:bg-orange-500 peer-checked:text-white peer-checked:border-orange-400 peer-checked:shadow-[0_0_15px_rgba(249,115,22,0.4)] group-hover:border-slate-500">AMBER</div>
                                    </label>
                                    <label class="cursor-pointer group">
                                        <input type="radio" name="tlp" value="GREEN" class="peer sr-only">
                                        <div class="text-center py-3 rounded-lg border border-slate-700 bg-slate-900/50 text-slate-500 text-xs font-bold transition-all peer-checked:bg-emerald-500 peer-checked:text-white peer-checked:border-emerald-400 peer-checked:shadow-[0_0_15px_rgba(16,185,129,0.4)] group-hover:border-slate-500">GREEN</div>
                                    </label>
                                    <label class="cursor-pointer group">
                                        <input type="radio" name="tlp" value="CLEAR" class="peer sr-only" checked>
                                        <div class="text-center py-3 rounded-lg border border-slate-700 bg-slate-900/50 text-slate-500 text-xs font-bold transition-all peer-checked:bg-slate-600 peer-checked:text-white peer-checked:border-slate-500 peer-checked:shadow-[0_0_15px_rgba(71,85,105,0.4)] group-hover:border-slate-500">CLEAR</div>
                                    </label>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Risk Score</label>
                                <div class="relative">
                                    <select name="risk_score" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-4 py-3 text-slate-200 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all shadow-inner appearance-none cursor-pointer">
                                        <option value="Low">Low (Routine)</option>
                                        <option value="Medium">Medium (Concerning)</option>
                                        <option value="High">High (Urgent Action)</option>
                                        <option value="Critical">Critical (Immediate Crisis)</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-8">
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Executive Summary</label>
                                <textarea name="executive_summary" rows="5" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-4 py-3 text-slate-200 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all placeholder-slate-600 shadow-inner font-sans leading-relaxed" placeholder="High-level business focused summary..."></textarea>
                            </div>
                             <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Impact Analysis</label>
                                <textarea name="impact_analysis" rows="3" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-4 py-3 text-slate-200 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all placeholder-slate-600 shadow-inner font-sans leading-relaxed" placeholder="Financial, Reputational, or Operational impact..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 2: TECHNICAL & KILL CHAIN -->
                    <div x-show="currentStep === 1" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                        <h2 class="text-2xl font-black text-white mb-8 border-b border-white/5 pb-6 flex items-center gap-3">
                            <svg class="w-6 h-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                            Technical Analysis
                        </h2>

                        <div class="mb-10 bg-black/20 p-6 rounded-xl border border-white/5">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-6 block">Cyber Kill Chain Phase (Furthest Reached)</label>
                            <div class="flex items-center justify-between relative px-2">
                                <div class="absolute top-[14px] left-0 right-0 h-1 bg-slate-800 -z-0 rounded-full"></div>
                                
                                <template x-for="(phase, index) in killChainPhases" :key="phase">
                                    <label class="cursor-pointer group relative flex flex-col items-center z-10 w-16">
                                        <input type="radio" name="kill_chain_phase" :value="phase" class="peer sr-only" x-model="selectedKillChain">
                                        
                                        <!-- Node -->
                                        <div class="w-8 h-8 rounded-full border-2 border-slate-700 bg-slate-900 group-hover:border-blue-500 group-hover:scale-110 peer-checked:bg-blue-600 peer-checked:border-blue-400 peer-checked:shadow-[0_0_15px_rgba(59,130,246,0.5)] transition-all duration-300 flex items-center justify-center">
                                            <span class="text-[10px] font-bold text-slate-500 peer-checked:text-white" x-text="index + 1"></span>
                                        </div>

                                        <!-- Label -->
                                        <span class="absolute top-10 text-[9px] font-bold uppercase tracking-wider text-center transition-colors duration-300 w-24" 
                                              :class="selectedKillChain === phase ? 'text-blue-400 opacity-100' : 'text-slate-600 opacity-60 group-hover:opacity-100 group-hover:text-slate-400'" 
                                              x-text="phase"></span>
                                    </label>
                                </template>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-8 mt-12 mb-8">
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Root Cause</label>
                                <div class="relative">
                                     <select name="root_cause" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-4 py-3 text-slate-200 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all shadow-inner appearance-none cursor-pointer">
                                        <option value="Phishing">Phishing / Social Engineering</option>
                                        <option value="Vulnerability">Software Vulnerability (CVE)</option>
                                        <option value="Misconfiguration">System Misconfiguration</option>
                                        <option value="Insider">Insider Threat</option>
                                        <option value="ZeroDay">Zero-Day Exploit</option>
                                        <option value="Unknown">Unknown / Investigation Ongoing</option>
                                    </select>

                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </div>
                                </div>
                            </div>
                             <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">MITRE Tactics (Hold Ctrl to verify)</label>
                                <select name="mitre_tactics[]" multiple class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-4 py-3 text-slate-200 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all shadow-inner h-32 scrollbar-thin scrollbar-thumb-slate-700 scrollbar-track-transparent">
                                    <option value="Initial Access">Initial Access</option>
                                    <option value="Execution">Execution</option>
                                    <option value="Persistence">Persistence</option>
                                    <option value="Privilege Escalation">Privilege Escalation</option>
                                    <option value="Defense Evasion">Defense Evasion</option>
                                    <option value="Credential Access">Credential Access</option>
                                    <option value="Discovery">Discovery</option>
                                    <option value="Lateral Movement">Lateral Movement</option>
                                    <option value="Collection">Collection</option>
                                    <option value="Exfiltration">Exfiltration</option>
                                    <option value="Impact">Impact</option>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Technical Deep Dive</label>
                            <textarea name="technical_analysis" rows="8" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-4 py-3 text-slate-200 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all placeholder-slate-600 shadow-inner font-mono text-sm leading-relaxed" placeholder="Detailed technical findings, logs analysis, payload decoding..."></textarea>
                        </div>
                    </div>

                    <!-- STEP 3: FORENSICS (TIMELINE & IOCs) -->
                    <div x-show="currentStep === 2" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                         <h2 class="text-2xl font-black text-white mb-8 border-b border-white/5 pb-6 flex items-center gap-3">
                             <svg class="w-6 h-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                             Forensic Artifacts
                         </h2>

                         <!-- TIMELINE BUILDER -->
                         <div class="mb-8 p-6 bg-slate-900/30 rounded-xl border border-slate-800">
                             <div class="flex justify-between items-center mb-6">
                                 <h3 class="font-bold text-blue-400 uppercase tracking-widest text-xs flex items-center gap-2">
                                     <span class="w-2 h-2 rounded-full bg-blue-500"></span> Attack Timeline
                                 </h3>
                                 <button type="button" @click="addTimelineEvent()" class="text-xs bg-blue-600 hover:bg-blue-500 text-white font-bold px-3 py-1.5 rounded transition-all shadow-[0_0_10px_rgba(59,130,246,0.3)]">+ Add Event</button>
                             </div>
                             
                             <div class="space-y-3">
                                 <template x-for="(event, index) in timeline" :key="index">
                                     <div class="flex gap-2 group">
                                         <input type="datetime-local" x-model="event.time" class="w-1/4 bg-slate-950 border border-slate-700 rounded px-3 py-2 text-xs text-slate-300 focus:border-blue-500 focus:outline-none">
                                         <input type="text" x-model="event.desc" placeholder="Event description..." class="w-1/2 bg-slate-950 border border-slate-700 rounded px-3 py-2 text-xs text-slate-300 focus:border-blue-500 focus:outline-none">
                                         <select x-model="event.type" class="w-1/4 bg-slate-950 border border-slate-700 rounded px-3 py-2 text-xs text-slate-300 focus:border-blue-500 focus:outline-none">
                                             <option value="Recon">Recon</option>
                                             <option value="Exploit">Exploit</option>
                                             <option value="Action">Action</option>
                                             <option value="Defense">Defense</option>
                                         </select>
                                         <button type="button" @click="timeline.splice(index, 1)" class="w-8 flex items-center justify-center text-slate-600 hover:text-red-500 transition-colors bg-slate-900 border border-slate-700 rounded hover:border-red-500/50">
                                             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                         </button>
                                     </div>
                                 </template>
                                 <div x-show="timeline.length === 0" class="text-center text-slate-600 text-xs italic py-8 border-2 border-dashed border-slate-800 rounded-lg">No timeline events added.<br>Click "+ Add Event" to begin forensic reconstruction.</div>
                             </div>
                         </div>

                         <!-- IOC BUILDER -->
                         <div class="p-6 bg-slate-900/30 rounded-xl border border-slate-800">
                             <div class="flex justify-between items-center mb-6">
                                 <h3 class="font-bold text-red-400 uppercase tracking-widest text-xs flex items-center gap-2">
                                     <span class="w-2 h-2 rounded-full bg-red-500"></span> Indicators of Compromise
                                 </h3>
                                 <button type="button" @click="addIoc()" class="text-xs bg-red-600 hover:bg-red-500 text-white font-bold px-3 py-1.5 rounded transition-all shadow-[0_0_10px_rgba(239,68,68,0.3)]">+ Add IoC</button>
                             </div>
                             
                             <div class="space-y-3">
                                 <template x-for="(ioc, index) in iocs" :key="index">
                                     <div class="flex gap-2">
                                         <select x-model="ioc.type" class="w-1/4 bg-slate-950 border border-slate-700 rounded px-3 py-2 text-xs text-slate-300 focus:border-red-500 focus:outline-none">
                                             <option value="IPv4">IPv4</option>
                                             <option value="Domain">Domain</option>
                                             <option value="Hash-MD5">Hash (MD5)</option>
                                             <option value="Hash-SHA256">Hash (SHA256)</option>
                                             <option value="URL">URL</option>
                                         </select>
                                         <input type="text" x-model="ioc.value" placeholder="Value (e.g. 192.168.1.1)" class="w-1/2 bg-slate-950 border border-slate-700 rounded px-3 py-2 text-xs text-slate-300 font-mono focus:border-red-500 focus:outline-none">
                                         <input type="text" x-model="ioc.desc" placeholder="Context..." class="w-1/4 bg-slate-950 border border-slate-700 rounded px-3 py-2 text-xs text-slate-300 focus:border-red-500 focus:outline-none">
                                         <button type="button" @click="iocs.splice(index, 1)" class="w-8 flex items-center justify-center text-slate-600 hover:text-red-500 transition-colors bg-slate-900 border border-slate-700 rounded hover:border-red-500/50">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                         </button>
                                     </div>
                                 </template>
                                  <div x-show="iocs.length === 0" class="text-center text-slate-600 text-xs italic py-8 border-2 border-dashed border-slate-800 rounded-lg">No IoCs recorded.</div>
                             </div>
                         </div>
                    </div>

                    <!-- STEP 4: CLOSING (RECOMMENDATIONS) -->
                    <div x-show="currentStep === 3" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                        <h2 class="text-2xl font-black text-white mb-8 border-b border-white/5 pb-6 flex items-center gap-3">
                             <svg class="w-6 h-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Strategic Recommendations
                        </h2>

                        <div class="mb-8 space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Remediation Steps</label>
                            <textarea name="recommendations" rows="6" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-4 py-3 text-slate-200 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all placeholder-slate-600 shadow-inner leading-relaxed" placeholder="Step-by-step containment and eradication strategy..."></textarea>
                        </div>

                         <div class="grid grid-cols-3 gap-8">
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">MTTD (Detection)</label>
                                <input type="text" name="metrics[mttd]" placeholder="e.g. 2 Hours" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-4 py-3 text-slate-200 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all placeholder-slate-600 shadow-inner">
                            </div>
                             <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">MTTR (Response)</label>
                                <input type="text" name="metrics[mttr]" placeholder="e.g. 45 Minutes" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-4 py-3 text-slate-200 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all placeholder-slate-600 shadow-inner">
                            </div>
                             <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Artifacts Collected</label>
                                <input type="number" name="metrics[artifacts]" placeholder="0" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-4 py-3 text-slate-200 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all placeholder-slate-600 shadow-inner">
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
        <!-- AI Analysis Modal -->
        <div x-show="analyzeModalOpen" class="fixed inset-0 z-50 flex items-center justify-center px-4" x-cloak>
            <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="analyzeModalOpen = false"></div>
            <div class="relative bg-slate-900 border border-indigo-500/30 rounded-2xl p-6 w-full max-w-2xl shadow-2xl shadow-indigo-500/20">
                <h3 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                    <span class="w-2 h-8 bg-indigo-500 rounded-full"></span>
                    DeepSeek AI Analysis
                </h3>
                <p class="text-slate-400 text-sm mb-4">Paste your raw logs (Wazuh, Syslog, Apache) below. DeepSeek-V3 will extract IPs, correlate with Threat Intelligence feeds, and generate a forensic report.</p>
                
                <textarea x-show="!isAnalyzing && analysisSteps.length === 0 && !ipSelectionMode" x-model="logContent" rows="8" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-3 text-slate-300 font-mono text-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 outline-none mb-6 placeholder-slate-600" placeholder="Paste logs here..."></textarea>
                
                <!-- IP Selection Panel -->
                <div x-show="ipSelectionMode" class="mb-6 bg-slate-950 rounded-lg border border-indigo-500/30 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h4 class="text-sm font-bold text-indigo-400 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Pilih IP untuk Dianalisis
                            </h4>
                            <p class="text-xs text-slate-500 mt-1">Ditemukan <span class="text-indigo-300 font-bold" x-text="extractedIps.length"></span> External IP. Pilih maksimal 5 IP.</p>
                        </div>
                        <div class="text-xs text-slate-400">
                            <span x-text="selectedIps.length"></span> / 5 dipilih
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-slate-700">
                        <template x-for="ip in extractedIps" :key="ip">
                            <label class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-all"
                                   :class="selectedIps.includes(ip) ? 'bg-indigo-600/20 border-indigo-500/50 shadow-[0_0_10px_rgba(99,102,241,0.2)]' : 'bg-slate-900 border-slate-700 hover:border-slate-600'"
                                   @click="toggleIpSelection(ip)">
                                <div class="w-5 h-5 rounded border-2 flex items-center justify-center transition-all"
                                     :class="selectedIps.includes(ip) ? 'bg-indigo-600 border-indigo-500' : 'border-slate-600'">
                                    <svg x-show="selectedIps.includes(ip)" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <span class="font-mono text-sm" :class="selectedIps.includes(ip) ? 'text-indigo-300' : 'text-slate-300'" x-text="ip"></span>
                            </label>
                        </template>
                    </div>
                    
                    <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-slate-800">
                        <button type="button" @click="ipSelectionMode = false; analysisSteps = [];" class="px-4 py-2 text-slate-400 hover:text-white text-sm font-bold">Batal</button>
                        <button type="button" @click="analyzeSelectedIps()" 
                                :disabled="selectedIps.length === 0"
                                class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-lg shadow-lg shadow-indigo-500/20 flex items-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            Analisis <span x-text="selectedIps.length"></span> IP Terpilih
                        </button>
                    </div>
                </div>
                
                <!-- Live Progress Feed -->
                <div x-show="isAnalyzing || analysisSteps.length > 0" class="mb-6 bg-slate-950 rounded-lg border border-slate-800 p-4 h-64 overflow-y-auto font-mono text-xs space-y-2 scrollbar-thin scrollbar-thumb-slate-700">
                    <template x-for="step in analysisSteps" :key="step.name">
                        <div class="flex items-center gap-3 p-2 rounded border border-transparent" :class="step.status === 'error' ? 'bg-red-900/20 border-red-500/30' : (step.status === 'success' ? 'bg-emerald-900/10' : 'bg-slate-900')">
                            <!-- Status Icons -->
                            <div class="w-5 h-5 flex items-center justify-center shrink-0">
                                <svg x-show="step.status === 'running'" class="animate-spin w-4 h-4 text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <svg x-show="step.status === 'success'" class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <svg x-show="step.status === 'error'" class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                <div x-show="step.status === 'skipped'" class="w-2 h-2 rounded-full bg-slate-600"></div>
                            </div>
                            
                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-center mb-0.5">
                                    <span class="font-bold truncate" :class="step.status === 'error' ? 'text-red-400' : 'text-slate-300'" x-text="step.name"></span>
                                    <span class="text-[10px] uppercase tracking-wider" :class="{'text-indigo-400': step.status==='running', 'text-emerald-500': step.status==='success', 'text-red-500': step.status==='error', 'text-slate-600': step.status==='skipped'}" x-text="step.status"></span>
                                </div>
                                <div class="text-slate-500 truncate" x-text="step.msg"></div>
                            </div>
                        </div>
                    </template>
                </div>
                
                <!-- Live TI Data View -->
                <div x-show="collectedIntel.length > 0" class="mb-6 rounded-lg border border-indigo-500/20 bg-indigo-500/5 p-4">
                    <h4 class="text-xs font-bold text-indigo-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Live Intelligence Data
                    </h4>
                    <div class="space-y-3 overflow-y-auto max-h-64 scrollbar-thin scrollbar-thumb-indigo-900 pr-2">
                        <template x-for="intel in collectedIntel" :key="intel.ip">
                            <div class="bg-slate-900/80 rounded-lg p-3 text-[10px] font-mono border border-slate-700/50 shadow-sm relative overflow-hidden group hover:border-indigo-500/30 transition-colors">
                                
                                <!-- Header: IP & Risk -->
                                <div class="flex justify-between items-center mb-2 border-b border-slate-800/50 pb-2">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-white text-xs bg-slate-800 px-1.5 py-0.5 rounded border border-slate-700" x-text="intel.ip"></span>
                                        <template x-if="intel.details.geo">
                                            <span class="text-slate-400 flex items-center gap-1">
                                                <span x-text="intel.details.geo.country"></span>
                                                <span class="text-slate-600">|</span>
                                                <span x-text="intel.details.geo.org || 'Unknown ASN'" class="truncate max-w-[150px]"></span>
                                            </span>
                                        </template>
                                    </div>
                                    <span class="font-bold px-2 py-0.5 rounded" 
                                          :class="(intel.details.risk_score || 0) > 75 ? 'bg-red-500/20 text-red-400' : ((intel.details.risk_score || 0) > 40 ? 'bg-orange-500/20 text-orange-400' : 'bg-emerald-500/20 text-emerald-400')"
                                          x-text="'Risk Score: ' + (intel.details.risk_score || 0)"></span>
                                </div>

                                <!-- Grid Details -->
                                <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-slate-400">
                                    
                                    <!-- IPinfo -->
                                    <div class="flex flex-col border-l-2 border-slate-800 pl-2">
                                        <span class="text-[9px] uppercase tracking-wider text-slate-500 font-bold mb-0.5">IPinfo.io (Geo)</span>
                                        <span x-text="intel.details.geo ? `${intel.details.geo.city}, ${intel.details.geo.region}` : 'No Data'" class="text-slate-300"></span>
                                    </div>

                                    <!-- GreyNoise -->
                                    <div class="flex flex-col border-l-2 border-slate-800 pl-2">
                                        <span class="text-[9px] uppercase tracking-wider text-slate-500 font-bold mb-0.5">GreyNoise</span>
                                        <template x-if="intel.details.greynoise">
                                            <div class="flex flex-col gap-1">
                                                 <span :class="intel.details.greynoise.classification === 'malicious' ? 'text-red-400' : 'text-slate-300'" 
                                                       x-text="intel.details.greynoise.classification || 'Noise Not Detected'"></span>
                                                
                                                <!-- Actor display -->
                                                <div x-show="intel.details.greynoise.actor && intel.details.greynoise.actor !== 'unknown'" class="text-[10px] text-indigo-300">
                                                    Actor: <span x-text="intel.details.greynoise.actor"></span>
                                                </div>

                                                <!-- Tags display -->
                                                <div x-show="intel.details.greynoise.tags && intel.details.greynoise.tags.length > 0" class="flex flex-wrap gap-1 mt-1">
                                                     <template x-for="tag in intel.details.greynoise.tags">
                                                         <span class="px-1 py-0.5 bg-slate-800 border border-slate-700 rounded text-[9px] text-slate-400" x-text="tag"></span>
                                                     </template>
                                                </div>
                                            </div>
                                        </template>
                                        <span x-show="!intel.details.greynoise" class="text-slate-600 italic">No noise detected</span>
                                    </div>

                                    <!-- VirusTotal (Expanded) -->
                                    <div class="flex flex-col border-l-2 border-slate-800 pl-2 col-span-2">
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="text-[9px] uppercase tracking-wider text-slate-500 font-bold">VirusTotal Analysis</span>
                                            <template x-if="intel.details.virustotal && intel.details.virustotal.last_analysis_stats">
                                                 <div class="flex gap-2 text-[9px] items-center">
                                                     <span class="text-xs font-bold" 
                                                           :class="intel.details.virustotal.last_analysis_stats.malicious > 0 ? 'text-red-400' : 'text-emerald-400'"
                                                           x-text="intel.details.virustotal.last_analysis_stats.malicious > 0 ? 'MALICIOUS' : 'CLEAN'"></span>
                                                     <span class="text-slate-500">|</span>
                                                     <span class="text-red-400 font-bold" x-text="intel.details.virustotal.last_analysis_stats.malicious + ' Detections'"></span>
                                                     <span class="text-emerald-600" x-text="intel.details.virustotal.last_analysis_stats.harmless + ' Clean'"></span>
                                                 </div>
                                            </template>
                                        </div>

                                        <template x-if="intel.details.virustotal">
                                            <div class="space-y-3 mt-1">
                                                 <!-- Detections List -->
                                                 <div x-show="intel.details.virustotal.last_analysis_stats.malicious > 0" class="bg-red-500/5 p-2 rounded border border-red-500/20">
                                                     <div class="flex items-center gap-2 mb-2">
                                                         <span class="text-[8px] text-red-400 font-bold uppercase">Security Vendors Flagging this IP</span>
                                                         <div class="h-px bg-red-500/20 flex-1"></div>
                                                     </div>
                                                     <div class="flex flex-wrap gap-2 max-h-32 overflow-y-auto custom-scrollbar">
                                                         <template x-for="(result, vendor) in intel.details.virustotal.last_analysis_results">
                                                             <div x-show="result.category === 'malicious' || result.category === 'suspicious'" 
                                                                  class="flex items-center gap-1.5 bg-slate-900 border border-red-500/30 px-2 py-1 rounded shadow-sm">
                                                                 <span class="text-red-300 font-bold text-[9px]" x-text="vendor"></span>
                                                                 <span class="text-[8px] uppercase font-bold px-1 rounded bg-red-500 text-white" x-text="result.result || 'MALICIOUS'"></span>
                                                             </div>
                                                         </template>
                                                     </div>
                                                 </div>
                                                 
                                                 <!-- Clean List (Summary) -->
                                                 <div x-show="intel.details.virustotal.last_analysis_results">
                                                     <div class="flex items-center gap-2 mb-1 opacity-50">
                                                         <span class="text-[8px] text-emerald-500 font-bold uppercase">Clean Engines</span>
                                                         <div class="h-px bg-emerald-500/20 flex-1"></div>
                                                     </div>
                                                     <div class="flex flex-wrap gap-1 opacity-50 hover:opacity-100 transition-opacity max-h-16 overflow-hidden hover:max-h-64 overflow-y-auto">
                                                         <template x-for="(result, vendor) in intel.details.virustotal.last_analysis_results">
                                                             <span x-show="result.category === 'harmless' || result.category === 'undetected'" 
                                                                   class="text-[8px] text-emerald-600/70" x-text="vendor + ' • '"></span>
                                                         </template>
                                                     </div>
                                                 </div>
                                            </div>
                                        </template>
                                        <span x-show="!intel.details.virustotal" class="text-slate-600 italic">Scan unavailable</span>
                                    </div>

                                    <!-- AbuseIPDB (Detailed) -->
                                    <div class="flex flex-col border-l-2 border-slate-800 pl-2 col-span-2 mt-2 pt-2 border-t border-slate-800/50">
                                        <div class="flex justify-between items-start mb-2">
                                            <div>
                                                <span class="text-[9px] uppercase tracking-wider text-slate-500 font-bold block">AbuseIPDB Community Reports</span>
                                                <template x-if="intel.details.abuseipdb">
                                                    <div class="flex items-baseline gap-2 mt-0.5">
                                                        <span class="text-xs font-mono text-slate-300" x-text="intel.details.abuseipdb.isp || 'Unknown ISP'"></span>
                                                        <span class="text-[9px] text-slate-500" x-show="intel.details.abuseipdb.domain" x-text="'(' + intel.details.abuseipdb.domain + ')'"></span>
                                                        <span class="px-1.5 py-0.5 rounded text-[8px] font-bold uppercase bg-slate-800 text-slate-400 border border-slate-700" x-text="intel.details.abuseipdb.usageType || 'Generic'"></span>
                                                    </div>
                                                </template>
                                            </div>
                                            <template x-if="intel.details.abuseipdb">
                                                <div class="text-right">
                                                     <div class="text-xs font-bold" 
                                                          :class="intel.details.abuseipdb.abuseConfidenceScore > 50 ? 'text-red-400' : 'text-emerald-400'"
                                                          x-text="intel.details.abuseipdb.abuseConfidenceScore + '% Confidence'"></div>
                                                     <div class="text-[9px] text-slate-500" x-text="intel.details.abuseipdb.totalReports + ' reports from ' + intel.details.abuseipdb.numDistinctUsers + ' users'"></div>
                                                </div>
                                            </template>
                                        </div>

                                        <template x-if="intel.details.abuseipdb && intel.details.abuseipdb.reports">
                                            <div class="bg-black/20 rounded border border-white/5 overflow-hidden">
                                                <div class="px-2 py-1 bg-white/5 border-b border-white/5 flex justify-between items-center text-[8px] text-slate-500 uppercase font-bold">
                                                    <span>Recent Reports</span>
                                                    <span>Comment</span>
                                                </div>
                                                <div class="max-h-32 overflow-y-auto custom-scrollbar divide-y divide-white/5">
                                                    <template x-for="report in intel.details.abuseipdb.reports">
                                                        <div class="p-2 text-[9px] hover:bg-white/5 transition-colors">
                                                            <div class="flex justify-between items-start mb-1 h-full">
                                                                <div class="flex items-center gap-1.5 shrink-0">
                                                                    <span class="font-mono text-indigo-300" x-text="report.reportedAt ? report.reportedAt.split('T')[0] : ''"></span>
                                                                    <span class="text-slate-500" x-text="'•'"></span>
                                                                    <span class="text-slate-400" x-text="report.reporterCountryCode"></span>
                                                                </div>
                                                                <div class="text-slate-500 font-mono text-[8px] text-right shrink-0" x-text="'ID: ' + report.reporterId"></div>
                                                            </div>
                                                            <div class="text-slate-300 leading-relaxed font-mono pl-1 border-l-2 border-indigo-500/20" x-text="report.comment"></div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                        <span x-show="!intel.details.abuseipdb" class="text-slate-600 italic">No reports found</span>
                                    </div>

                                    <!-- AlienVault OTX -->
                                    <div class="flex flex-col border-l-2 border-slate-800 pl-2 col-span-2">
                                        <span class="text-[9px] uppercase tracking-wider text-slate-500 font-bold mb-0.5">AlienVault OTX</span>
                                        <template x-if="intel.details.alienvault">
                                            <div class="flex flex-col gap-1">
                                                <div class="flex gap-4">
                                                    <span x-text="`Pulses: ${intel.details.alienvault.pulse_count || 0}`" class="text-slate-300"></span>
                                                    <span x-text="`Reputation: ${intel.details.alienvault.reputation}`" class="text-slate-300"></span>
                                                </div>
                                                
                                                <!-- Validation / Whitelist Status -->
                                                <template x-if="intel.details.alienvault.validation && intel.details.alienvault.validation.length > 0">
                                                    <div class="flex flex-wrap gap-1">
                                                        <template x-for="val in intel.details.alienvault.validation">
                                                            <span class="px-1.5 py-0.5 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded text-[9px] font-bold" x-text="val"></span>
                                                        </template>
                                                    </div>
                                                </template>

                                                <!-- Pulse Details -->
                                                <div x-show="intel.details.alienvault.pulses && intel.details.alienvault.pulses.length > 0" class="mt-2 text-[9px]">
                                                    <span class="text-slate-500 font-bold uppercase">Related Pulses (Top 3):</span>
                                                    <div class="space-y-4 mt-1">
                                                        <template x-for="pulse in intel.details.alienvault.pulses.slice(0, 3)">
                                                            <div class="bg-slate-900/50 p-4 rounded-lg border border-indigo-500/20 text-[10px] relative overflow-hidden shadow-sm">
                                                                
                                                                <!-- Header -->
                                                                <div class="flex justify-between items-start mb-3 border-b border-white/5 pb-2">
                                                                    <div>
                                                                         <a :href="`https://otx.alienvault.com/pulse/${pulse.id}`" target="_blank" class="text-indigo-400 font-bold text-xs hover:underline flex items-center gap-2" >
                                                                            <span x-text="pulse.name"></span>
                                                                            <svg class="w-3 h-3 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                                                         </a>
                                                                         <div class="text-slate-500 font-mono text-[9px] mt-0.5" x-text="'ID: ' + pulse.id"></div>
                                                                    </div>
                                                                    <div class="flex flex-col items-end gap-1">
                                                                        <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider"
                                                                                  :class="{
                                                                                      'bg-slate-100 text-slate-800': (pulse.TLP || 'white') === 'white',
                                                                                      'bg-emerald-500/20 text-emerald-400': (pulse.TLP || 'white') === 'green',
                                                                                      'bg-amber-500/20 text-amber-400': (pulse.TLP || 'white') === 'amber',
                                                                                      'bg-red-500/20 text-red-500': (pulse.TLP || 'white') === 'red'
                                                                                  }"
                                                                                  x-text="'TLP: ' + (pulse.TLP || 'WHITE')"></span>
                                                                        <span class="text-slate-500 text-[9px]" x-text="(pulse.indicators_count || 0) + ' IOCs'"></span>
                                                                    </div>
                                                                </div>

                                                                <!-- Meta Grid -->
                                                                <div class="grid grid-cols-2 gap-4 mb-3">
                                                                    
                                                                    <!-- Col 1: Threats -->
                                                                    <div class="space-y-2">
                                                                        <!-- Adversary -->
                                                                        <div>
                                                                            <span class="text-[9px] uppercase font-bold text-slate-500 block mb-0.5">Adversary</span>
                                                                            <span x-show="pulse.adversary" class="text-rose-400 font-bold bg-rose-900/10 px-1.5 py-0.5 rounded border border-rose-500/20" x-text="pulse.adversary"></span>
                                                                            <span x-show="!pulse.adversary" class="text-slate-600 italic">None identified</span>
                                                                        </div>
                                                                        
                                                                        <!-- Malware -->
                                                                        <div>
                                                                            <span class="text-[9px] uppercase font-bold text-slate-500 block mb-0.5">Malware Families</span>
                                                                            <div x-show="pulse.malware_families && pulse.malware_families.length > 0" class="flex flex-wrap gap-1">
                                                                                 <template x-for="fam in pulse.malware_families">
                                                                                    <span class="text-red-400 bg-red-900/20 border border-red-500/20 px-1.5 py-0.5 rounded" x-text="fam"></span>
                                                                                </template>
                                                                            </div>
                                                                            <span x-show="!pulse.malware_families || pulse.malware_families.length === 0" class="text-slate-600 italic">None identified</span>
                                                                        </div>
                                                                    </div>

                                                                    <!-- Col 2: Context -->
                                                                    <div class="space-y-2">
                                                                        <!-- Industries -->
                                                                        <div>
                                                                            <span class="text-[9px] uppercase font-bold text-slate-500 block mb-0.5">Target Industries</span>
                                                                            <div x-show="pulse.industries && pulse.industries.length > 0" class="flex flex-wrap gap-1">
                                                                                 <template x-for="ind in pulse.industries">
                                                                                    <span class="text-amber-400 bg-amber-900/20 border border-amber-500/20 px-1.5 py-0.5 rounded" x-text="ind"></span>
                                                                                </template>
                                                                            </div>
                                                                            <span x-show="!pulse.industries || pulse.industries.length === 0" class="text-slate-600 italic">None specified</span>
                                                                        </div>

                                                                        <!-- Targeted Countries -->
                                                                        <div>
                                                                            <span class="text-[9px] uppercase font-bold text-slate-500 block mb-0.5">Target Countries</span>
                                                                            <div x-show="pulse.targeted_countries && pulse.targeted_countries.length > 0" class="flex flex-wrap gap-1">
                                                                                 <template x-for="country in pulse.targeted_countries">
                                                                                    <span class="text-sky-400 bg-sky-900/20 border border-sky-500/20 px-1.5 py-0.5 rounded" x-text="country"></span>
                                                                                </template>
                                                                            </div>
                                                                            <span x-show="!pulse.targeted_countries || pulse.targeted_countries.length === 0" class="text-slate-600 italic">Global / Unspecified</span>
                                                                        </div>

                                                                        <!-- Attack IDs -->
                                                                        <div>
                                                                            <span class="text-[9px] uppercase font-bold text-slate-500 block mb-0.5">MITRE ATT&CK</span>
                                                                            <div x-show="pulse.attack_ids && pulse.attack_ids.length > 0" class="flex flex-wrap gap-1">
                                                                                 <template x-for="att in pulse.attack_ids">
                                                                                    <span class="text-purple-400 bg-purple-900/20 border border-purple-500/20 px-1.5 py-0.5 rounded" x-text="att"></span>
                                                                                </template>
                                                                            </div>
                                                                            <span x-show="!pulse.attack_ids || pulse.attack_ids.length === 0" class="text-slate-600 italic">None mapped</span>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!-- Description -->
                                                                <div class="bg-black/20 p-2 rounded border border-white/5 mb-3">
                                                                    <p class="text-slate-400 leading-relaxed font-mono text-[9px]" x-text="pulse.description || 'No detailed description provided by author.'"></p>
                                                                </div>

                                                                <!-- Sample Indicators Table -->
                                                                <div x-show="pulse.indicators && pulse.indicators.length > 0" class="mb-3">
                                                                    <span class="text-[9px] uppercase font-bold text-slate-500 block mb-1">Sample Indicators (Top 10)</span>
                                                                    <div class="overflow-x-auto">
                                                                        <table class="w-full text-[8px] text-left border-collapse">
                                                                            <thead>
                                                                                <tr class="text-slate-500 border-b border-white/10">
                                                                                    <th class="py-1">Type</th>
                                                                                    <th class="py-1">Indicator</th>
                                                                                    <th class="py-1">Created</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody class="text-slate-400 divide-y divide-white/5">
                                                                                <template x-for="ioc in pulse.indicators">
                                                                                    <tr>
                                                                                        <td class="py-1 pr-2 text-indigo-300" x-text="ioc.type"></td>
                                                                                        <td class="py-1 pr-2 font-mono" x-text="ioc.indicator"></td>
                                                                                        <td class="py-1 text-slate-600" x-text="ioc.created.split('T')[0]"></td>
                                                                                    </tr>
                                                                                </template>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                                
                                                                <!-- Footer: Tags, References, Author -->
                                                                <div class="flex justify-between items-end border-t border-white/5 pt-2">
                                                                    <div class="flex flex-col gap-1 w-2/3">
                                                                        <div x-show="pulse.tags && pulse.tags.length > 0" class="flex flex-wrap gap-1 opacity-80">
                                                                            <template x-for="tag in pulse.tags.slice(0,5)">
                                                                                 <span class="text-slate-500 text-[8px] bg-slate-800 border border-slate-700 px-1.5 rounded" x-text="tag"></span>
                                                                            </template>
                                                                        </div>
                                                                        <div x-show="pulse.references && pulse.references.length > 0" class="truncate text-[8px] text-indigo-400/70">
                                                                            <span class="text-slate-600 uppercase font-bold mr-1">Ref:</span>
                                                                            <span x-text="pulse.references[0]"></span>
                                                                            <span x-show="pulse.references.length > 1" x-text="` (+${pulse.references.length - 1} more)`"></span>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="text-[8px] text-slate-600 text-right">
                                                                        <div x-text="pulse.created ? 'Created: ' + pulse.created.split('T')[0] : ''"></div>
                                                                        <div class="text-indigo-300/60" x-text="'By: ' + pulse.author_name"></div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                        <span x-show="!intel.details.alienvault" class="text-slate-600 italic">No OTX data</span>
                                    </div>

                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 mt-4 border-t border-slate-800 pt-4">
                    <button type="button" @click="analyzeModalOpen = false" class="px-4 py-2 text-slate-400 hover:text-white font-bold text-sm">Cancel</button>
                    
                    <!-- Phase 1 Button: Scan -->
                    <button type="button" 
                            x-show="!scanComplete"
                            @click="analyzeLog('scan')" 
                            :disabled="isAnalyzing" 
                            class="px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-lg shadow-lg shadow-indigo-500/20 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                        <svg x-show="isAnalyzing" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-text="isAnalyzing ? 'Scanning...' : 'Scan & Enrich Data'"></span>
                    </button>

                    <!-- Phase 2 Button: Synthesize -->
                    <button type="button" 
                            x-show="scanComplete"
                            @click="proceedToAi()" 
                            :disabled="isAnalyzing" 
                            class="px-6 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-lg shadow-lg shadow-emerald-500/20 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all animate-pulse">
                        <svg x-show="isAnalyzing" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-text="isAnalyzing ? 'Synthesizing Report...' : 'Verified - Proceed to AI'"></span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function reportWizard() {
        return {
            currentStep: 0,
            selectedKillChain: '',
            killChainPhases: ['Recon', 'Weaponization', 'Delivery', 'Exploitation', 'Installation', 'C2', 'Actions'],
            timeline: [],
            iocs: [],
            
            // AI Analysis State
            analyzeModalOpen: false,
            logContent: '',
            isAnalyzing: false,
            scanComplete: false, // Phase 1 State
            analysisSteps: [], 
            collectedIntel: [], 
            resultCache: null, // Temporary storage for Phase 1 result
            
            // IP Selection State
            ipSelectionMode: false, // Show IP selection UI
            extractedIps: [], // IPs from backend
            selectedIps: [], // User-selected IPs

            async analyzeLog(mode = 'scan') {
                if (!this.logContent) return;
                
                this.isAnalyzing = true;
                
                // If starting fresh scan
                if (mode === 'scan') {
                    this.scanComplete = false;
                    this.analysisSteps = []; 
                    this.collectedIntel = []; 
                    this.resultCache = null;
                }

                // Prepare Payload
                let payload = { 
                    log_content: this.logContent,
                    action: mode
                };

                // If synthesizing, pass back the accumulated intel 
                if (mode === 'synthesize' && this.collectedIntel.length > 0) {
                     let intelMap = {};
                    this.collectedIntel.forEach(item => {
                        intelMap[item.ip] = item.details;
                    });
                    payload.intel_data = intelMap;
                }

                try {
                    const response = await fetch('{{ route("reports.analyze") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(payload)
                    });

                    const reader = response.body.getReader();
                    const decoder = new TextDecoder();
                    
                    while (true) {
                        const { done, value } = await reader.read();
                        if (done) break;
                        
                        const chunk = decoder.decode(value);
                        const lines = chunk.split('\n\n');
                        
                        for (const line of lines) {
                            if (line.startsWith('data: ')) {
                                try {
                                    const data = JSON.parse(line.substring(6));
                                    
                                    // HANDLE FINAL COMPLETION
                                    if (data.status === 'done') {
                                        this.finalizeAnalysis(data);
                                        return; 
                                    }

                                    // HANDLE PHASE 1 COMPLETION (REVIEW)
                                    if (data.status === 'review_required') {
                                        this.scanComplete = true; // Unlock buttons
                                        this.resultCache = data.result;
                                        this.updateStep('Review', 'success', 'Enrichment complete. Verify data below before proceeding.');
                                        return;
                                    }
                                    
                                    // HANDLE IP SELECTION REQUIRED
                                    if (data.status === 'ip_selection_required') {
                                        this.ipSelectionMode = true;
                                        this.extractedIps = data.extracted_ips || [];
                                        this.selectedIps = []; // Reset selection
                                        this.updateStep('IP Selection', 'pending', data.message);
                                        return;
                                    }
                                    
                                    // HANDLE TI DATA STREAM
                                    if (data.step === 'TI_DATA_PAYLOAD') {
                                        const intel = JSON.parse(data.message);
                                        // Avoid duplicates in UI array
                                        if (!this.collectedIntel.some(i => i.ip === intel.ip)) {
                                            this.collectedIntel.unshift(intel); 
                                        }
                                    } else {
                                        this.updateStep(data.step, data.status, data.message);
                                    }
                                    
                                } catch (e) {
                                  console.error("Parse error", e);
                                }
                            }
                        }
                    }

                } catch (error) {
                    console.error(error);
                    alert("Analysis Stream Failed: " + error.message);
                } finally {
                    this.isAnalyzing = false;
                }
            },

            proceedToAi() {
                this.analyzeLog('synthesize');
            },
            
            // IP Selection Methods
            toggleIpSelection(ip) {
                const idx = this.selectedIps.indexOf(ip);
                if (idx > -1) {
                    this.selectedIps.splice(idx, 1);
                } else if (this.selectedIps.length < 5) {
                    this.selectedIps.push(ip);
                }
            },
            
            analyzeSelectedIps() {
                if (this.selectedIps.length === 0) {
                    alert('Silakan pilih minimal 1 IP untuk dianalisis.');
                    return;
                }
                // Set analyzing state FIRST to ensure progress panel shows immediately
                this.isAnalyzing = true;
                this.ipSelectionMode = false;
                this.analysisSteps = []; // Reset steps
                this.collectedIntel = []; // Reset intel
                this.scanComplete = false;
                this.analyzeLogWithSelectedIps();
            },
            
            async analyzeLogWithSelectedIps() {
                // isAnalyzing already set by caller
                
                const payload = { 
                    log_content: this.logContent,
                    action: 'scan',
                    selected_ips: this.selectedIps
                };

                try {
                    const response = await fetch('{{ route("reports.analyze") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(payload)
                    });

                    const reader = response.body.getReader();
                    const decoder = new TextDecoder();
                    
                    while (true) {
                        const { done, value } = await reader.read();
                        if (done) break;
                        
                        const chunk = decoder.decode(value);
                        const lines = chunk.split('\n\n');
                        
                        for (const line of lines) {
                            if (line.startsWith('data: ')) {
                                try {
                                    const data = JSON.parse(line.substring(6));
                                    
                                    if (data.status === 'done') {
                                        this.finalizeAnalysis(data);
                                        return;
                                    }
                                    
                                    if (data.status === 'review_required') {
                                        this.scanComplete = true;
                                        this.resultCache = data.result;
                                        this.updateStep('Review', 'success', 'Enrichment complete. Verify data below before proceeding.');
                                        return;
                                    }
                                    
                                    if (data.step === 'TI_DATA_PAYLOAD') {
                                        console.log('[DEBUG] TI_DATA_PAYLOAD received:', data.message);
                                        const intel = JSON.parse(data.message);
                                        console.log('[DEBUG] Parsed intel:', intel);
                                        if (!this.collectedIntel.some(i => i.ip === intel.ip)) {
                                            this.collectedIntel.unshift(intel);
                                            console.log('[DEBUG] Added to collectedIntel. Total:', this.collectedIntel.length);
                                        }
                                    } else {
                                        console.log('[DEBUG] Step received:', data.step, data.status, data.message);
                                        this.updateStep(data.step, data.status, data.message);
                                    }
                                } catch (e) {
                                    console.error("Parse error", e);
                                }
                            }
                        }
                    }
                } catch (error) {
                    console.error(error);
                    alert("Analysis Stream Failed: " + error.message);
                } finally {
                    this.isAnalyzing = false;
                }
            },

            updateStep(stepName, status, message) {
                // Find existing step or create new
                let step = this.analysisSteps.find(s => s.name === stepName);
                if (!step) {
                    step = { name: stepName, status: status, msg: message };
                    this.analysisSteps.push(step);
                } else {
                    step.status = status;
                    step.msg = message;
                }
                
                // Keep the steps array valid for Alpine
                 this.analysisSteps = [...this.analysisSteps];
            },

            finalizeAnalysis(data) {
                // Success notification
                this.analyzeModalOpen = false;
                
                // Clear steps after successful close
                setTimeout(() => { this.analysisSteps = []; }, 500);

                if (data.redirect_url) {
                    alert("✅ Analysis Complete!\n\nReport saved to database. Redirecting to report details...");
                    window.location.href = data.redirect_url;
                } else {
                    alert("Analysis finished but no redirect URL provided.");
                }
            },

            steps: [
                { title: 'Overview & Scope' },
                { title: 'Analysis & TTPs' },
                { title: 'Forensics & IoCs' },
                { title: 'Closing Strategy' }
            ],
            
            addTimelineEvent() {
                this.timeline.push({ time: new Date().toISOString().slice(0, 16), desc: '', type: 'Recon' });
            },
            addIoc() {
                this.iocs.push({ type: 'IPv4', value: '', desc: '' });
            }
        }
    }
</script>
@endsection
