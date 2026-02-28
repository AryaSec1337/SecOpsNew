@extends('layouts.dashboard')

@section('content')
<div class="h-[calc(100vh-100px)] flex flex-col font-mono text-slate-300 max-w-7xl mx-auto w-full gap-4">

    <!-- SIGNAL HEADER -->
    <div class="bg-black border border-slate-800 rounded-sm p-4 relative overflow-hidden shadow-[0_0_30px_rgba(0,0,0,0.5)]">
        <!-- Scanline -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(18,16,16,0)_50%,rgba(0,0,0,0.25)_50%),linear-gradient(90deg,rgba(255,0,0,0.06),rgba(0,255,0,0.02),rgba(0,0,255,0.06))] z-0 pointer-events-none bg-[length:100%_2px,3px_100%] opacity-20"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-6">
                <div class="relative w-16 h-16 flex-shrink-0">
                    <div class="absolute inset-0 rounded-full border-2 border-slate-800 bg-slate-900/50 animate-pulse"></div>
                    <div class="absolute inset-2 rounded-full border border-red-500/30 flex items-center justify-center">
                        <span class="text-2xl font-black text-red-600">{{ substr($chat['group'], 0, 1) }}</span>
                    </div>
                     <!-- Orbit Dot -->
                    <div class="absolute top-0 left-1/2 w-1 h-1 bg-red-500 shadow-[0_0_10px_red] rounded-full animate-spin origin-[0_32px]"></div>
                </div>
                
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <span class="text-xs bg-red-900/40 text-red-500 border border-red-900/50 px-2 py-0.5 animate-pulse">SIGNAL_ACQUIRED</span>
                        <span class="text-xs text-slate-500">FREQ: {{ rand(100, 999) }}.{{ rand(10, 99) }} MHz</span>
                    </div>
                    <h1 class="text-2xl font-black text-white tracking-widest uppercase flex items-center gap-2">
                        INTERCEPT: {{ $chat['group'] }}
                        @if($chat['paid'])
                            <span class="text-xs bg-red-600 text-black px-2 py-0.5 font-bold">RANSOM_PAID</span>
                        @else
                            <span class="text-xs bg-slate-700 text-slate-400 px-2 py-0.5 font-bold">UNRESOLVED</span>
                        @endif
                    </h1>
                     <p class="text-[10px] text-slate-500 mt-1">LOG ID: {{ $chat['chat_id'] }} // ENCRYPTION: PARTIALLY_BROKEN</p>
                </div>
            </div>

            <!-- Stats Module -->
            <div class="flex gap-6 border-l border-slate-800 pl-6">
                <div class="text-right">
                    <p class="text-[9px] text-slate-500 uppercase tracking-widest">Message Count</p>
                    <p class="text-xl font-bold text-white">{{ $chat['message_count'] ?? 0 }}</p>
                </div>
                <div class="text-right">
                    <p class="text-[9px] text-slate-500 uppercase tracking-widest">Final Demand</p>
                    <p class="text-xl font-bold text-emerald-400 font-mono">{{ $chat['negotiatedransom'] ?? 'UNKNOWN' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="flex-1 flex flex-col md:flex-row gap-4 overflow-hidden">
        
        <!-- SIDEBAR: DECRYPTION STATUS -->
        <div class="w-full md:w-64 bg-[#0a0a0a] border border-slate-800 p-4 flex flex-col gap-4 hidden md:flex shrink-0">
            <div>
                <h3 class="text-xs font-bold text-slate-500 uppercase border-b border-slate-800 pb-2 mb-3">Session Metadata</h3>
                <div class="space-y-3 font-mono text-xs">
                    <div>
                        <span class="block text-[9px] text-slate-600">TARGET ID</span>
                        <span class="text-emerald-500">{{ substr(md5($chat['group']), 0, 12) }}</span>
                    </div>
                    <div>
                        <span class="block text-[9px] text-slate-600">FIRST CONTACT</span>
                        <span class="text-slate-400">{{ $chat['messages'][0]['timestamp'] ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="block text-[9px] text-slate-600">LAST ACTIVITY</span>
                        <span class="text-slate-400">{{ end($chat['messages'])['timestamp'] ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <div class="flex-1 border border-slate-800 bg-black p-2 relative">
                <div class="absolute inset-0 opacity-10 bg-[url('https://grainy-gradients.vercel.app/noise.svg')]"></div>
                <!-- Fake Decryption Log -->
                 <div class="h-full overflow-hidden text-[9px] text-green-900 font-mono leading-tight space-y-0.5 opacity-50" id="decryption-log">
                    <!-- JS will populate -->
                </div>
            </div>
        </div>

        <!-- MAIN TERMINAL: CHAT FEED -->
        <div class="flex-1 bg-[#050505] border border-slate-800 relative flex flex-col">
            <!-- Terminal Top Bar -->
            <div class="bg-[#111] px-4 py-1 border-b border-slate-800 flex justify-between items-center text-[10px] text-slate-500">
                <span>COMMS_LOG_VIEWER v2.4</span>
                <div class="flex gap-2">
                    <span class="w-2 h-2 rounded-full bg-slate-700"></span>
                    <span class="w-2 h-2 rounded-full bg-slate-700"></span>
                </div>
            </div>

            <!-- Messages Area -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4 scrollbar-thin scrollbar-thumb-slate-700 relative">
                <div class="flex justify-center mb-6">
                    <div class="text-[10px] text-yellow-600 border border-yellow-900/30 bg-yellow-900/10 px-4 py-1 rounded">
                        ⚠ WARNING: UNAUTHORIZED INTERCEPTION DETECTED
                    </div>
                </div>

                @forelse($chat['messages'] as $index => $msg)
                    @php
                        $isAttacker = strtolower($msg['party']) !== 'victim';
                    @endphp

                    <div class="group flex flex-col {{ $isAttacker ? 'items-start' : 'items-end' }} mb-4 opacity-0 animate-fade-in" style="animation-delay: {{ $index * 0.05 }}s; animation-fill-mode: forwards;">
                        
                        <!-- Metadata Line -->
                        <div class="flex items-center gap-2 mb-1 text-[10px] font-mono opacity-60">
                            @if($isAttacker)
                                <span class="text-red-500 font-bold">[HOSTILE]</span>
                                <span class="text-red-400">{{ $msg['party'] }}</span>
                            @else
                                <span class="text-emerald-500 font-bold">[TARGET]</span>
                                <span class="text-emerald-400">{{ $msg['party'] }}</span>
                            @endif
                            <span class="text-slate-600">@ {{ $msg['timestamp'] ?? 'UNKNOWN' }}</span>
                        </div>

                        <!-- Message Block -->
                        <div class="max-w-[90%] md:max-w-[80%] relative">
                            <!-- Visual line connecting metadata -->
                            <div class="absolute top-0 {{ $isAttacker ? '-left-2 border-l-2 border-red-900/50' : '-right-2 border-r-2 border-emerald-900/50' }} h-full w-2"></div>
                            
                            <div class="px-4 py-3 text-sm font-mono shadow-lg border-t border-b 
                                {{ $isAttacker 
                                    ? 'bg-[#1a0505] text-red-100 border-red-900/30 shadow-red-900/5' 
                                    : 'bg-[#051a1a] text-emerald-100 border-emerald-900/30 shadow-emerald-900/5' }}">
                                {!! nl2br(e($msg['content'])) !!}
                            
                                @if(str_contains($msg['content'], 'http'))
                                    <div class="mt-2 pt-2 border-t {{ $isAttacker ? 'border-red-900/30' : 'border-emerald-900/30' }}">
                                        <div class="flex items-center gap-2 text-xs opacity-70">
                                            <svg class="w-3 h-3 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                                            <span class="tracking-wider text-[10px] uppercase">Suspicious Link Fragment</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center h-full text-slate-700 font-mono">
                        <div class="text-4xl">NO_SIGNAL</div>
                        <p class="text-xs mt-2">// FREQUENCY SCAN COMPLETE. NO DATA.</p>
                    </div>
                @endforelse

                <div class="py-8 flex flex-col items-center gap-1 opacity-50">
                    <p class="text-[10px] text-slate-600 uppercase tracking-[0.3em]animate-pulse">--- END OF TRANSMISSION ---</p>
                </div>
            </div>

            <!-- Terminal Cursor Input (Fake) -->
            <div class="bg-[#0a0a0a] p-3 border-t border-slate-800 flex items-center gap-2 text-sm font-mono text-slate-500">
                <span class="text-emerald-500 font-bold">sysadmin@watchtower:~$</span>
                <span class="w-2 h-4 bg-emerald-500 animate-pulse"></span>
            </div>
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Simple matrix-style rain or log scroller for the sidebar
        const logContainer = document.getElementById('decryption-log');
        const logs = [
            "Attempting handshake...",
            "Key exchange: RSA-4096",
            "Packet captured: 44kb",
            "Decrypting payload...",
            "Signature mismatch.",
            "Retrying with bruteforce dictionary...",
            "Segment 1 decoded.",
            "Segment 2 decoded.",
            "Analysis: High Confidence.",
            "Tracing origin IP...",
            "Proxy detected: TOR network.",
            "Signal lost.",
            "Reacquiring..."
        ];

        let i = 0;
        setInterval(() => {
            const el = document.createElement('div');
            el.innerText = `> ${logs[i % logs.length]} [${Math.random().toString(36).substring(7)}]`;
            logContainer.prepend(el);
            if(logContainer.children.length > 20) logContainer.lastChild.remove();
            i++;
        }, 800);
    });
</script>

<style>
    @keyframes fade-in {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation-name: fade-in;
        animation-duration: 0.5s;
    }
</style>
@endsection
