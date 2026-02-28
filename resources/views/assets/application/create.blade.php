@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen flex items-center justify-center p-6 text-slate-300 font-sans">
    <div class="max-w-5xl w-full grid grid-cols-1 lg:grid-cols-2 gap-8" x-data="{ 
        name: '',
        vendor: '',
        version: '',
        type: 'Web App',
        initAnim: true,
        init() { setTimeout(() => this.initAnim = false, 1000); }
    }">
        
        <!-- LEFT PANEL: CONFIGURATION FORM -->
        <div class="flex flex-col gap-6">
             <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="w-2 h-2 rounded-full bg-cyan-500 animate-pulse shadow-[0_0_10px_#06b6d4]"></span>
                    <span class="text-xs font-mono text-cyan-500 tracking-widest uppercase">New Deployment</span>
                </div>
                <h1 class="text-3xl font-black text-white tracking-tighter uppercase leading-none">
                    Module Registration
                </h1>
                <p class="text-slate-500 text-xs mt-2 font-mono">Register new software asset into the centralized command registry.</p>
            </div>

            <form action="{{ route('assets.application.store') }}" method="POST" class="space-y-6 relative z-10 bg-[#0a0a0a] border border-slate-800 p-8">
                @csrf
                
                <!-- Identity Inputs -->
                <div class="space-y-4">
                     <p class="text-[10px] text-slate-600 uppercase tracking-widest border-b border-slate-800 pb-2"># IDENTITY_PARAMS</p>
                    
                    <div>
                        <label class="block text-[10px] font-mono uppercase tracking-widest text-slate-500 mb-2">Module Name</label>
                        <input type="text" name="name" x-model="name" required class="w-full px-4 py-3 bg-[#111] border border-slate-700 text-white font-mono text-sm focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 outline-none transition-all placeholder-slate-600 uppercase" placeholder="ENTER MODULE ID">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-mono uppercase tracking-widest text-slate-500 mb-2">Vendor</label>
                            <input type="text" name="vendor" x-model="vendor" class="w-full px-4 py-3 bg-[#111] border border-slate-700 text-white font-mono text-sm focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 outline-none transition-all placeholder-slate-600 uppercase" placeholder="VENDOR">
                        </div>
                         <div>
                            <label class="block text-[10px] font-mono uppercase tracking-widest text-slate-500 mb-2">Version</label>
                            <input type="text" name="version" x-model="version" class="w-full px-4 py-3 bg-[#111] border border-slate-700 text-white font-mono text-sm focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 outline-none transition-all placeholder-slate-600 uppercase" placeholder="v1.0.0">
                        </div>
                    </div>
                </div>

                <!-- Classification Inputs -->
                <div class="space-y-4 mt-6">
                    <p class="text-[10px] text-slate-600 uppercase tracking-widest border-b border-slate-800 pb-2"># CLASSIFICATION</p>
                    
                    <div>
                        <label class="block text-[10px] font-mono uppercase tracking-widest text-slate-500 mb-2">Module Type</label>
                        <select name="type" x-model="type" class="w-full px-4 py-3 bg-[#111] border border-slate-700 text-white font-mono text-sm focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 outline-none transition-all uppercase appearance-none">
                            <option value="Web App">Web App</option>
                            <option value="Mobile App">Mobile App</option>
                            <option value="Desktop App">Desktop App</option>
                            <option value="SaaS">SaaS Platform</option>
                            <option value="API">API Service</option>
                            <option value="Database">Database System</option>
                        </select>
                    </div>

                     <div class="grid grid-cols-2 gap-4">
                         <div>
                            <label class="block text-[10px] font-mono uppercase tracking-widest text-slate-500 mb-2">Criticality</label>
                            <select name="criticality" class="w-full px-4 py-3 bg-[#111] border border-slate-700 text-white font-mono text-sm focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 outline-none transition-all uppercase appearance-none">
                                <option value="Low">Low</option>
                                <option value="Medium">Medium</option>
                                <option value="High">High</option>
                                <option value="Critical">Critical</option>
                            </select>
                        </div>
                        <div>
                             <label class="block text-[10px] font-mono uppercase tracking-widest text-slate-500 mb-2">Initial Status</label>
                            <select name="status" class="w-full px-4 py-3 bg-[#111] border border-slate-700 text-white font-mono text-sm focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 outline-none transition-all uppercase appearance-none">
                                <option value="Active">Active</option>
                                <option value="Development">Development</option>
                                <option value="Warning">Warning</option>
                                <option value="Offline">Offline</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="pt-4 flex gap-4">
                    <a href="{{ route('assets.application.index') }}" class="px-6 py-3 border border-slate-700 text-slate-400 hover:text-white hover:border-slate-500 font-bold text-xs tracking-wider uppercase transition-all"> Abort </a>
                    <button type="submit" class="flex-1 px-6 py-3 bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs tracking-wider uppercase shadow-[0_0_20px_rgba(8,145,178,0.4)] hover:shadow-[0_0_30px_rgba(8,145,178,0.6)] transition-all flex items-center justify-center gap-2 group/btn">
                        <span>Initialize Module</span>
                        <svg class="w-4 h-4 group-hover/btn:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                    </button>
                </div>
            </form>
        </div>

        <!-- RIGHT PANEL: PREVIEW & TERMINAL -->
        <div class="relative">
             <!-- Init Animation Overlay -->
            <div x-show="initAnim" 
                 x-transition:leave="transition ease-in duration-500"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 z-20 bg-black flex flex-col items-center justify-center border border-slate-800 rounded-sm">
                <div class="w-16 h-16 border-4 border-cyan-500/20 border-t-cyan-500 rounded-full animate-spin mb-4"></div>
                <p class="text-xs font-mono text-cyan-500 tracking-widest animate-pulse">ESTABLISHING UPLINK...</p>
            </div>

            <div class="bg-[#050505] border border-slate-800 h-full flex flex-col relative overflow-hidden">
                <!-- Decorative Grid -->
                <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.02)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.02)_1px,transparent_1px)] bg-[size:20px_20px] opacity-20 pointer-events-none"></div>
                
                <div class="p-4 border-b border-slate-800 flex justify-between items-center bg-[#0a0a0a] relative z-10">
                     <div class="flex items-center gap-2">
                         <span class="w-2 h-2 rounded-full bg-slate-600"></span>
                         <span class="text-xs font-mono text-slate-400">PREVIEW_OUTPUT</span>
                     </div>
                </div>

                <div class="p-8 flex items-center justify-center flex-1 relative z-10">
                    <!-- The Card Preview (Same as Index) -->
                    <div class="w-full max-w-sm bg-[#0f0f0f] border border-cyan-500 shadow-[0_0_20px_rgba(8,145,178,0.2)] rounded-sm p-5 relative overflow-hidden transition-all duration-300 transform hover:scale-105">
                        
                        <!-- Status Indicator Line -->
                        <div class="absolute top-0 left-0 w-full h-1 bg-cyan-500"></div>
                        
                        <div class="flex justify-between items-start mb-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-slate-900 border border-slate-700 flex items-center justify-center text-xl rounded-sm">
                                    <span x-show="type.toLowerCase().includes('web')">🌐</span>
                                    <span x-show="!type.toLowerCase().match(/web/)">📦</span>
                                </div>
                                <div>
                                    <h3 class="font-bold text-white text-sm tracking-wide uppercase" x-text="name || 'MODULE NAME'"></h3>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        <span class="text-[10px] font-mono text-emerald-500">ACTIVE</span>
                                    </div>
                                </div>
                            </div>
                            <span class="text-[9px] font-mono text-slate-600 border border-slate-800 px-1.5 py-0.5 rounded" x-text="version || 'v1.0.0'"></span>
                        </div>

                        <div class="space-y-3 mb-4">
                            <div>
                                <div class="flex justify-between text-[9px] font-mono text-slate-500 mb-1">
                                    <span>SEC_COMPLIANCE</span>
                                    <span class="text-emerald-400">PENDING</span>
                                </div>
                                <div class="h-1 w-full bg-slate-900 rounded-full overflow-hidden">
                                     <div class="h-full bg-emerald-500 w-[50%] animate-pulse"></div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between items-center text-[10px] text-slate-600 font-mono border-t border-slate-800 pt-3 mt-auto">
                            <span>VENDOR: <span class="text-slate-400" x-text="vendor || 'UNKNOWN'"></span></span>
                        </div>

                        <!-- Scan Line Overlay -->
                        <div class="absolute inset-0 pointer-events-none bg-gradient-to-b from-transparent via-cyan-500/10 to-transparent h-[10px] w-full animate-scan"></div>
                    </div>
                </div>

                <div class="p-4 bg-[#0a0a0a] border-t border-slate-800 text-[10px] font-mono text-slate-500 text-center">
                    // AWAITING FINAL COMMIT //
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes scan {
        0% { transform: translateY(-100%); }
        100% { transform: translateY(500%); }
    }
    .animate-scan {
        animation: scan 4s linear infinite;
    }
</style>
@endsection
