@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen flex items-center justify-center p-6 text-slate-300 font-sans">
    
    <div class="max-w-5xl w-full grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- LEFT PANEL: CONFIGURE MISSION -->
        <div class="flex flex-col gap-6">
            <div class="mb-2">
                 <div class="flex items-center gap-3 mb-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse shadow-[0_0_10px_#6366f1]"></span>
                    <span class="text-xs font-mono text-indigo-400 tracking-widest uppercase">New Operation</span>
                </div>
                <h1 class="text-4xl font-black text-white tracking-tighter uppercase leading-none">
                    Deploy<br>Agent Uplink
                </h1>
                <p class="text-slate-500 text-sm mt-4 leading-relaxed font-mono">
                    > INITIATE_NEW_NODE_REGISTRATION<br>
                    > ESTABLISH_SECURE_C2_CHANNEL<br>
                    > AWAITING_OPERATOR_INPUT...
                </p>
            </div>

            @if(session('success_token'))
                <!-- SUCCESS STATE (Hidden on Left) -->
                <div class="bg-emerald-900/10 border border-emerald-500/30 p-6 rounded-sm relative overflow-hidden">
                    <div class="absolute inset-0 bg-emerald-500/5 animate-pulse"></div>
                    <div class="relative z-10 flex items-start gap-4">
                        <div class="p-3 bg-emerald-900/30 border border-emerald-500/50 rounded-full text-emerald-400">
                             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white uppercase tracking-wide">Uplink Established</h3>
                            <p class="text-xs text-sm text-emerald-400 mt-1">Node "{{ session('success_token')['hostname'] }}" registered. Proceed to agent installation on the target machine.</p>
                        </div>
                    </div>
                </div>
            @else
                <!-- CONFIGURATION FORM -->
                <div class="bg-[#0a0a0a] border border-slate-800 p-8 relative group">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-500/5 rounded-bl-full -mr-4 -mt-4 transition-all group-hover:bg-indigo-500/10"></div>
                    <form action="{{ route('assets.store') }}" method="POST" class="space-y-6 relative z-10">
                        @csrf
                        
                        <!-- Hostname -->
                        <div>
                            <label class="block text-[10px] font-mono uppercase tracking-widest text-slate-500 mb-2">Target Identity (Hostname)</label>
                            <div class="relative">
                                <span class="absolute left-4 top-3 text-slate-600 font-mono">></span>
                                <input type="text" name="hostname" required class="w-full pl-10 pr-4 py-3 bg-[#111] border border-slate-700 text-white font-mono text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-all placeholder-slate-600 uppercase" placeholder="SRV-OPERATIVE-01">
                            </div>
                        </div>

                        <!-- C2 URL -->
                        <div>
                            <label class="block text-[10px] font-mono uppercase tracking-widest text-slate-500 mb-2">Command & Control (C2) URL</label>
                            <div class="relative">
                                <span class="absolute left-4 top-3 text-slate-600 font-mono">@</span>
                                <input type="text" name="manager_url" required value="{{ url('/') }}" class="w-full pl-10 pr-4 py-3 bg-[#111] border border-slate-700 text-white font-mono text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-all placeholder-slate-600">
                            </div>
                            <p class="text-[9px] text-slate-600 mt-2 font-mono">* Must be reachable from the target node.</p>
                        </div>

                        <!-- Actions -->
                        <div class="pt-4 flex gap-4">
                            <a href="{{ route('assets.server') }}" class="px-6 py-3 border border-slate-700 text-slate-400 hover:text-white hover:border-slate-500 font-bold text-xs tracking-wider uppercase transition-all">
                                Abort
                            </a>
                            <button type="submit" class="flex-1 px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs tracking-wider uppercase shadow-[0_0_20px_rgba(79,70,229,0.4)] hover:shadow-[0_0_30px_rgba(79,70,229,0.6)] transition-all flex items-center justify-center gap-2 group/btn">
                                <span>Generate Secure Token</span>
                                <svg class="w-4 h-4 group-hover/btn:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>

        <!-- RIGHT PANEL: TERMINAL & PAYLOAD -->
        <div class="relative">
             @if(session('success_token'))
                <!-- PAYLOAD TERMINAL -->
                <div class="bg-[#050505] border border-slate-800 h-full flex flex-col relative overflow-hidden" x-data="{ platform: 'linux', copied: false }">
                    <!-- Decor -->
                     <div class="absolute top-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-indigo-500 to-transparent opacity-50"></div>
                     
                    <!-- Terminal Header -->
                    <div class="flex items-center justify-between px-4 py-3 bg-[#111] border-b border-slate-800">
                         <div class="flex gap-4 text-xs font-mono font-bold tracking-wider">
                             <button @click="platform = 'linux'" :class="platform === 'linux' ? 'text-white border-b-2 border-indigo-500' : 'text-slate-500 hover:text-slate-300'" class="pb-3 transition-colors px-1">
                                 LINUX_PAYLOAD
                             </button>
                             <button @click="platform = 'windows'" :class="platform === 'windows' ? 'text-white border-b-2 border-indigo-500' : 'text-slate-500 hover:text-slate-300'" class="pb-3 transition-colors px-1">
                                 WINDOWS_PAYLOAD
                             </button>
                         </div>
                    </div>

                    <!-- Token Display -->
                    <div class="p-6 border-b border-slate-800 bg-[#080808]">
                        <p class="text-[10px] text-indigo-400 font-mono uppercase mb-2">/// SECURE_ACCESS_TOKEN_GENERATED ///</p>
                        <div class="relative group cursor-pointer" @click="navigator.clipboard.writeText('{{ session('success_token')['token'] }}'); copied = true; setTimeout(() => copied = false, 2000)">
                             <code class="block w-full p-4 bg-[#0a0a0a] border border-indigo-900/30 text-indigo-100 font-mono text-xs break-all shadow-[inset_0_0_20px_rgba(79,70,229,0.1)] group-hover:border-indigo-500/50 transition-colors">
                                {{ session('success_token')['token'] }}
                            </code>
                            <div class="absolute right-2 top-2 text-[10px] text-indigo-500 opacity-0 group-hover:opacity-100 transition-opacity bg-black px-1">
                                <span x-show="!copied">CLICK_TO_COPY</span>
                                <span x-show="copied" class="text-emerald-500">COPIED!</span>
                            </div>
                        </div>
                    </div>

                    <!-- Command Area -->
                    <div class="flex-1 p-6 font-mono text-sm relative group">
                        <div class="absolute top-4 right-4 z-10">
                             <button 
                                @click="navigator.clipboard.writeText(platform === 'linux' ? `{{ 'curl -O ' . session('success_token')['url'] . '/agent.py && python3 agent.py ' . session('success_token')['install_args'] }}` : `{{ 'curl.exe -O ' . session('success_token')['url'] . '/agent.py; python agent.py ' . session('success_token')['install_args'] }}`); copied = true; setTimeout(() => copied = false, 2000)"
                                class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold uppercase tracking-wider shadow-lg transition-all flex items-center gap-2"
                            >
                                <svg x-show="!copied" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                                <svg x-show="copied" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span x-text="copied ? 'COPIED' : 'COPY_COMMAND'"></span>
                             </button>
                        </div>

                         <div x-show="platform === 'linux'" class="h-full">
                            <span class="text-emerald-500 select-none">root@node:~#</span>
                            <span class="text-slate-300 break-all select-all">curl -O {{ session('success_token')['url'] }}/agent.py && python3 agent.py {{ session('success_token')['install_args'] }}</span>
                            <span class="animate-pulse inline-block w-2 h-4 bg-emerald-500 align-middle ml-1"></span>
                        </div>

                         <div x-show="platform === 'windows'" class="h-full">
                            <span class="text-blue-400 select-none">PS C:\Users\Admin></span>
                            <span class="text-slate-300 break-all select-all">curl.exe -O {{ session('success_token')['url'] }}/agent.py; python agent.py {{ session('success_token')['install_args'] }}</span>
                            <span class="animate-pulse inline-block w-2 h-4 bg-blue-500 align-middle ml-1"></span>
                        </div>
                    </div>
                    
                    <!-- Footer Info -->
                    <div class="p-3 bg-[#080808] border-t border-slate-800 text-[10px] text-slate-500 font-mono text-center">
                        // EXECUTE ON TARGET MACHINE WITH ELEVATED PRIVILEGES //
                    </div>
                </div>
             @else
                <!-- IDLE STATE GRAPHIC -->
                <div class="h-full border border-slate-800 border-dashed rounded-sm flex flex-col items-center justify-center text-center p-8 opacity-50 bg-[#050505]">
                    <div class="w-24 h-24 mb-6 relative">
                        <div class="absolute inset-0 border-4 border-slate-800 rounded-full animate-[spin_10s_linear_infinite]"></div>
                        <div class="absolute inset-0 border-4 border-t-indigo-500 rounded-full animate-[spin_3s_linear_infinite]"></div>
                        <div class="absolute inset-4 border-4 border-slate-800 rounded-full animate-[spin_10s_linear_infinite_reverse]"></div>
                    </div>
                    <h3 class="text-slate-400 font-bold uppercase tracking-widest mb-2">Awaiting Configuration</h3>
                    <p class="text-xs text-slate-600 font-mono max-w-xs">
                        Configure target identity to generate secure installation payload.
                    </p>
                </div>
             @endif
        </div>

    </div>
</div>
@endsection
