@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen flex items-center justify-center p-6 text-slate-300 font-sans">
    <div class="max-w-5xl w-full grid grid-cols-1 lg:grid-cols-2 gap-8" x-data="{ 
        name: '{{ addslashes($application->name) }}',
        vendor: '{{ addslashes($application->vendor) }}',
        version: '{{ addslashes($application->app_version) }}',
        type: '{{ $application->app_type }}',
        status: '{{ $application->status }}',
        criticality: '{{ $application->criticality }}',
        ctiEnabled: {{ $application->cti_monitoring_enabled ? 'true' : 'false' }}
    }">
        
        <!-- LEFT PANEL: CONFIGURATION FORM -->
        <div class="flex flex-col gap-6">
             <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="w-2 h-2 rounded-full bg-purple-500 animate-pulse shadow-[0_0_10px_#a855f7]"></span>
                    <span class="text-xs font-mono text-purple-500 tracking-widest uppercase">System Reconfiguration</span>
                </div>
                <h1 class="text-3xl font-black text-white tracking-tighter uppercase leading-none">
                    Module Config
                </h1>
                <p class="text-slate-500 text-xs mt-2 font-mono">Update parameters for Module ID: #{{ $application->id }}</p>
            </div>

            <form action="{{ route('assets.application.update', $application->id) }}" method="POST" class="space-y-6 relative z-10 bg-[#0a0a0a] border border-slate-800 p-8">
                @csrf
                @method('PUT')
                
                <!-- Identity Inputs -->
                <div class="space-y-4">
                     <p class="text-[10px] text-slate-600 uppercase tracking-widest border-b border-slate-800 pb-2"># IDENTITY_PARAMS</p>
                    
                    <div>
                        <label class="block text-[10px] font-mono uppercase tracking-widest text-slate-500 mb-2">Module Name</label>
                        <input type="text" name="name" x-model="name" required class="w-full px-4 py-3 bg-[#111] border border-slate-700 text-white font-mono text-sm focus:border-purple-500 focus:ring-1 focus:ring-purple-500 outline-none transition-all placeholder-slate-600 uppercase">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-mono uppercase tracking-widest text-slate-500 mb-2">Vendor</label>
                            <input type="text" name="vendor" x-model="vendor" class="w-full px-4 py-3 bg-[#111] border border-slate-700 text-white font-mono text-sm focus:border-purple-500 focus:ring-1 focus:ring-purple-500 outline-none transition-all placeholder-slate-600 uppercase">
                        </div>
                         <div>
                            <label class="block text-[10px] font-mono uppercase tracking-widest text-slate-500 mb-2">Version</label>
                            <input type="text" name="version" x-model="version" class="w-full px-4 py-3 bg-[#111] border border-slate-700 text-white font-mono text-sm focus:border-purple-500 focus:ring-1 focus:ring-purple-500 outline-none transition-all placeholder-slate-600 uppercase">
                        </div>
                    </div>
                </div>

                <!-- Classification Inputs -->
                <div class="space-y-4 mt-6">
                    <p class="text-[10px] text-slate-600 uppercase tracking-widest border-b border-slate-800 pb-2"># CLASSIFICATION</p>
                    
                    <div>
                        <label class="block text-[10px] font-mono uppercase tracking-widest text-slate-500 mb-2">Module Type</label>
                         <select name="type" x-model="type" class="w-full px-4 py-3 bg-[#111] border border-slate-700 text-white font-mono text-sm focus:border-purple-500 focus:ring-1 focus:ring-purple-500 outline-none transition-all uppercase appearance-none">
                            <option value="Web App">Web App</option>
                            <option value="Mobile App">Mobile App</option>
                            <option value="Desktop App">Desktop App</option>
                            <option value="SaaS">SaaS</option>
                            <option value="API">API Service</option>
                            <option value="Database">Database</option>
                        </select>
                    </div>

                     <div class="grid grid-cols-2 gap-4">
                         <div>
                            <label class="block text-[10px] font-mono uppercase tracking-widest text-slate-500 mb-2">Criticality</label>
                            <select name="criticality" x-model="criticality" class="w-full px-4 py-3 bg-[#111] border border-slate-700 text-white font-mono text-sm focus:border-purple-500 focus:ring-1 focus:ring-purple-500 outline-none transition-all uppercase appearance-none">
                                <option value="Low">Low</option>
                                <option value="Medium">Medium</option>
                                <option value="High">High</option>
                                <option value="Critical">Critical</option>
                            </select>
                        </div>
                        <div>
                             <label class="block text-[10px] font-mono uppercase tracking-widest text-slate-500 mb-2">Current Status</label>
                             <select name="status" x-model="status" class="w-full px-4 py-3 bg-[#111] border border-slate-700 text-white font-mono text-sm focus:border-purple-500 focus:ring-1 focus:ring-purple-500 outline-none transition-all uppercase appearance-none">
                                <option value="Active">Active</option>
                                <option value="Development">Development</option>
                                <option value="Warning">Warning</option>
                                <option value="Offline">Offline</option>
                            </select>
                        </div>
                    </div>
                </div>

                 <!-- CTI Toggle -->
                 <div class="mt-4 bg-[#080808] p-3 border border-slate-800 flex items-center justify-between cursor-pointer" @click="ctiEnabled = !ctiEnabled">
                    <div>
                        <p class="text-[10px] font-bold text-white uppercase tracking-wider">THREAT SENSOR / CTI</p>
                        <p class="text-[9px] text-slate-500 font-mono">Real-time threat intelligence scraping</p>
                    </div>
                     <div class="w-12 h-6 rounded-full border border-slate-600 relative transition-colors"
                             :class="ctiEnabled ? 'bg-purple-900/50 border-purple-500' : 'bg-slate-800'">
                        <div class="absolute top-0.5 left-0.5 w-4.5 h-4.5 rounded-full bg-slate-400 transition-transform shadow-md"
                                 :class="ctiEnabled ? 'translate-x-6 bg-purple-400 shadow-[0_0_10px_#a855f7]' : 'translate-x-0'"></div>
                    </div>
                    <input type="hidden" name="cti_monitoring_enabled" :value="ctiEnabled ? '1' : '0'">
                </div>

                <div class="pt-4 flex gap-4">
                    <a href="{{ route('assets.application.index') }}" class="px-6 py-3 border border-slate-700 text-slate-400 hover:text-white hover:border-slate-500 font-bold text-xs tracking-wider uppercase transition-all"> Cancel </a>
                    <button type="submit" class="flex-1 px-6 py-3 bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs tracking-wider uppercase shadow-[0_0_20px_rgba(147,51,234,0.4)] hover:shadow-[0_0_30px_rgba(147,51,234,0.6)] transition-all flex items-center justify-center gap-2 group/btn">
                        <span>Commit Updates</span>
                        <svg class="w-4 h-4 group-hover/btn:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </button>
                </div>
            </form>
        </div>

        <!-- RIGHT PANEL: PREVIEW & TERMINAL -->
        <div class="relative">
            <div class="bg-[#050505] border border-slate-800 h-full flex flex-col relative overflow-hidden">
                <div class="p-4 border-b border-slate-800 flex justify-between items-center bg-[#0a0a0a] relative z-10">
                     <div class="flex items-center gap-2">
                         <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                         <span class="text-xs font-mono text-slate-400">STATUS_MONITOR</span>
                     </div>
                </div>

                <div class="p-8 flex items-center justify-center flex-1 relative z-10">
                    <!-- The Card Preview -->
                    <div class="w-full max-w-sm bg-[#0f0f0f] border border-cyan-500 shadow-[0_0_20px_rgba(8,145,178,0.2)] rounded-sm p-5 relative overflow-hidden transition-all duration-300 transform"
                         :class="{
                            'border-red-500 shadow-[0_0_20px_rgba(239,68,68,0.2)]': criticality === 'Critical',
                            'border-orange-500 shadow-[0_0_20px_rgba(249,115,22,0.2)]': criticality === 'High',
                            'border-blue-500 shadow-[0_0_20px_rgba(59,130,246,0.2)]': criticality === 'Medium',
                            'border-slate-600': criticality === 'Low'
                         }">
                        
                        <!-- Status Indicator Line -->
                        <div class="absolute top-0 left-0 w-full h-1 transition-colors"
                             :class="{
                                'bg-red-500': criticality === 'Critical',
                                'bg-orange-500': criticality === 'High',
                                'bg-blue-500': criticality === 'Medium',
                                'bg-slate-700': criticality === 'Low'
                             }"></div>
                        
                        <div class="flex justify-between items-start mb-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-slate-900 border border-slate-700 flex items-center justify-center text-xl rounded-sm">
                                    <span x-show="type.toLowerCase().includes('web')">🌐</span>
                                    <span x-show="!type.toLowerCase().match(/web/)">📦</span>
                                </div>
                                <div>
                                    <h3 class="font-bold text-white text-sm tracking-wide uppercase transition-all" x-text="name || 'MODULE NAME'"></h3>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="w-1.5 h-1.5 rounded-full transition-colors"
                                              :class="{
                                                'bg-emerald-500 animate-pulse': status === 'Active',
                                                'bg-red-500': status === 'Offline',
                                                'bg-yellow-500': status === 'Warning',
                                                'bg-blue-500': status === 'Development'
                                              }"></span>
                                        <span class="text-[10px] font-mono transition-colors"
                                               :class="{
                                                'text-emerald-500': status === 'Active',
                                                'text-red-500': status === 'Offline',
                                                'text-yellow-500': status === 'Warning',
                                                'text-blue-500': status === 'Development'
                                              }" x-text="status.toUpperCase()"></span>
                                    </div>
                                </div>
                            </div>
                            <span class="text-[9px] font-mono text-slate-600 border border-slate-800 px-1.5 py-0.5 rounded" x-text="version || 'v1.0.0'"></span>
                        </div>

                        <!-- CTI Visual -->
                         <div class="mb-4 p-3 rounded bg-slate-900/50 border border-slate-800 flex items-center gap-3" x-show="ctiEnabled" x-transition>
                            <div class="relative w-2 h-2">
                                <span class="absolute inline-flex h-full w-full rounded-full bg-purple-400 opacity-75 animate-ping"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-purple-500"></span>
                            </div>
                            <span class="text-[10px] font-mono text-purple-400 uppercase tracking-wider animate-pulse">Threat Sensor Active</span>
                        </div>

                        <div class="flex justify-between items-center text-[10px] text-slate-600 font-mono border-t border-slate-800 pt-3 mt-auto">
                            <span>VENDOR: <span class="text-slate-400" x-text="vendor || 'UNKNOWN'"></span></span>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
