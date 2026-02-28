<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'SecOps SIEM') }} - Mega Insurance</title>
    
    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        slate: {
                            850: '#1b2333',
                            900: '#0f172a',
                            950: '#020617',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .glass-panel {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 2px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
    </style>
</head>
<body class="bg-slate-950 text-slate-200 antialiased font-sans" x-data="secopsAppInit()">

    <!-- Mobile Header -->
    <header class="md:hidden flex items-center justify-between p-4 glass-panel sticky top-0 z-50 print:hidden">
        <div class="flex items-center gap-3">
            <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            <span class="font-bold text-lg tracking-wider">SECOPS</span>
        </div>
        <button @click="sidebarOpen = !sidebarOpen" class="text-slate-300 hover:text-white">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
    </header>

    <div class="flex h-screen overflow-hidden">
        
        <!-- Sidebar Navigation -->
        <aside class="fixed md:static inset-y-0 left-0 z-40 w-64 transform transition-transform duration-300 ease-in-out md:transform-none glass-panel border-r border-slate-800 flex flex-col -translate-x-full md:translate-x-0 print:hidden"
               :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }">
            
            <div class="p-6 flex items-center gap-3 border-b border-white/5 shrink-0">
                <div class="w-10 h-10 flex items-center justify-center">
                    <img src="https://www.megainsurance.co.id/includes/images/logo.png" alt="Mega Insurance Logo" class="w-full h-full object-contain">
                </div>
                <div>
                    <h1 class="font-black text-base tracking-tight text-white leading-tight">Security Operations</h1>
                    <p class="text-[9px] text-slate-400 font-mono tracking-widest uppercase mt-0.5">Mega Insurance</p>
                </div>
            </div>

            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                
                <!-- MAIN -->
                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-3 mb-2 mt-2">Main</div>

                <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all group {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <svg class="mr-3 h-5 w-5 transition-colors {{ request()->routeIs('dashboard') ? 'text-white' : 'text-slate-500 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Dashboard
                </a>







                <!-- DEFENSE -->
                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-3 mb-2 mt-4">Defense</div>

                <!-- INCIDENT RESPONSE -->
                <div x-data="{ open: {{ request()->routeIs('reports.*') ? 'true' : 'false' }} }" class="mb-1">
                    <button @click="open = !open" 
                            class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg transition-all group {{ request()->routeIs('reports.*') ? 'text-white bg-white/5' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                        <div class="flex items-center">
                            <svg class="mr-3 h-5 w-5 transition-colors {{ request()->routeIs('reports.*') ? 'text-blue-500' : 'text-slate-500 group-hover:text-blue-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Incident Response
                        </div>
                        <svg class="w-4 h-4 transition-transform duration-200 text-slate-500" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    
                    <div x-show="open" x-collapse class="pl-4 space-y-1 mt-1">
                        <a href="{{ route('reports.create') }}" 
                           class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all group {{ request()->routeIs('reports.create') ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                            <span class="w-1.5 h-1.5 rounded-full mr-3 {{ request()->routeIs('reports.create') ? 'bg-white' : 'bg-slate-600 group-hover:bg-blue-500' }}"></span>
                            Create Report
                        </a>
                        <a href="{{ route('reports.index') }}" 
                           class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all group {{ request()->routeIs('reports.index') || request()->routeIs('reports.show') ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                            <span class="w-1.5 h-1.5 rounded-full mr-3 {{ request()->routeIs('reports.index') || request()->routeIs('reports.show') ? 'bg-white' : 'bg-slate-600 group-hover:bg-blue-500' }}"></span>
                            History Reports
                        </a>
                    </div>
                </div>





                <a href="{{ route('blocked-ips.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all group {{ request()->routeIs('blocked-ips.*') ? 'bg-slate-700 text-white shadow-lg' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <svg class="mr-3 h-5 w-5 transition-colors {{ request()->routeIs('blocked-ips.*') ? 'text-white' : 'text-slate-500 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                    Blocked IPs
                </a>

                <a href="{{ route('mitigation-logs.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all group {{ request()->routeIs('mitigation-logs.*') ? 'bg-slate-700 text-white shadow-lg' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <svg class="mr-3 h-5 w-5 transition-colors {{ request()->routeIs('mitigation-logs.*') ? 'text-white' : 'text-slate-500 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    Investigation Logs
                </a>

                <!-- INTEL -->
                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-3 mb-2 mt-4">Intelligence</div>

                <a href="{{ route('cti.domain.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all group {{ request()->routeIs('cti.domain.*') ? 'bg-purple-600 text-white shadow-lg shadow-purple-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <svg class="mr-3 h-5 w-5 transition-colors {{ request()->routeIs('cti.domain.*') ? 'text-white' : 'text-purple-500 group-hover:text-purple-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                    Domains
                </a>

                <a href="{{ route('cti.external.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all group {{ request()->routeIs('cti.external.*') ? 'bg-purple-600 text-white shadow-lg shadow-purple-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <svg class="mr-3 h-5 w-5 transition-colors {{ request()->routeIs('cti.external.*') ? 'text-white' : 'text-purple-500 group-hover:text-purple-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Dark Web
                </a>
                
                <a href="{{ route('cti.mitre.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all group {{ request()->routeIs('cti.mitre.*') ? 'bg-purple-600 text-white shadow-lg shadow-purple-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <svg class="mr-3 h-5 w-5 transition-colors {{ request()->routeIs('cti.mitre.*') ? 'text-white' : 'text-purple-500 group-hover:text-purple-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    MITRE Matrix
                </a>



                <!-- INVESTIGATION -->
                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-3 mb-2 mt-4">Investigation</div>

                <a href="{{ route('investigation.ip-analyzer.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all group {{ request()->routeIs('investigation.ip-analyzer.*') ? 'bg-amber-600 text-white shadow-lg shadow-amber-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <svg class="mr-3 h-5 w-5 transition-colors {{ request()->routeIs('investigation.ip-analyzer.*') ? 'text-white' : 'text-amber-500 group-hover:text-amber-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    IP Analyzer
                </a>

                <a href="{{ route('investigation.email-analyzer.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all group {{ request()->routeIs('investigation.email-analyzer.*') ? 'bg-amber-600 text-white shadow-lg shadow-amber-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <svg class="mr-3 h-5 w-5 transition-colors {{ request()->routeIs('investigation.email-analyzer.*') ? 'text-white' : 'text-amber-500 group-hover:text-amber-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    Email Analyzer
                </a>

                <a href="{{ route('url-scanner.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all group {{ request()->routeIs('url-scanner.*') ? 'bg-amber-600 text-white shadow-lg shadow-amber-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <svg class="mr-3 h-5 w-5 transition-colors {{ request()->routeIs('url-scanner.*') ? 'text-white' : 'text-amber-500 group-hover:text-amber-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                    URL Scanner
                </a>

                <a href="{{ route('file-analyst.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all group {{ request()->routeIs('file-analyst.*') ? 'bg-amber-600 text-white shadow-lg shadow-amber-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <svg class="mr-3 h-5 w-5 transition-colors {{ request()->routeIs('file-analyst.*') ? 'text-white' : 'text-amber-500 group-hover:text-amber-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                    Files
                </a>

                <a href="{{ route('investigation.yargen.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all group {{ request()->routeIs('investigation.yargen.*') ? 'bg-amber-600 text-white shadow-lg shadow-amber-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <svg class="mr-3 h-5 w-5 transition-colors {{ request()->routeIs('investigation.yargen.*') ? 'text-white' : 'text-amber-500 group-hover:text-amber-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                    YARA Generator
                </a>

                <!-- WEBHOOK -->
                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-3 mb-2 mt-4">Webhook</div>

                <a href="{{ route('webhook-scans.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all group {{ request()->routeIs('webhook-scans.*') ? 'bg-teal-600 text-white shadow-lg shadow-teal-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <svg class="mr-3 h-5 w-5 transition-colors {{ request()->routeIs('webhook-scans.*') ? 'text-white' : 'text-teal-500 group-hover:text-teal-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    File Upload Monitoring
                </a>

                <a href="{{ route('webhook-alerts.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all group {{ request()->routeIs('webhook-alerts.*') ? 'bg-teal-600 text-white shadow-lg shadow-teal-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <svg class="mr-3 h-5 w-5 transition-colors {{ request()->routeIs('webhook-alerts.*') ? 'text-white' : 'text-teal-500 group-hover:text-teal-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    <span class="flex-1">FUM Alerts</span>
                    <span x-cloak x-show="notifications.fum_alerts > 0" x-transition 
                          class="ml-auto inline-flex items-center justify-center px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-500 text-white shadow-lg shadow-amber-500/20"
                          x-text="notifications.fum_alerts"></span>
                </a>

                <a href="{{ route('wazuh-alerts.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all group {{ request()->routeIs('wazuh-alerts.*') ? 'bg-teal-600 text-white shadow-lg shadow-teal-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <svg class="mr-3 h-5 w-5 transition-colors {{ request()->routeIs('wazuh-alerts.*') ? 'text-white' : 'text-teal-500 group-hover:text-teal-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    <span class="flex-1">Wazuh Alerts</span>
                    <span x-cloak x-show="notifications.wazuh_alerts > 0" x-transition 
                          class="ml-auto inline-flex items-center justify-center px-2 py-0.5 text-[10px] font-bold rounded-full bg-rose-500 text-white shadow-lg shadow-rose-500/20"
                          x-text="notifications.wazuh_alerts"></span>
                </a>

                <!-- ASSETS -->
                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-3 mb-2 mt-4">Assets</div>

                <a href="{{ route('assets.server') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all group {{ request()->routeIs('assets.server*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <svg class="mr-3 h-5 w-5 transition-colors {{ request()->routeIs('assets.server*') ? 'text-white' : 'text-emerald-500 group-hover:text-emerald-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 01-2 2v4a2 2 0 012 2h14a2 2 0 012-2v-4a2 2 0 01-2-2m-2-4h.01M17 16h.01"></path></svg>
                    Servers
                </a>

                <a href="{{ route('assets.application.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all group {{ request()->routeIs('assets.application.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <svg class="mr-3 h-5 w-5 transition-colors {{ request()->routeIs('assets.application.*') ? 'text-white' : 'text-emerald-500 group-hover:text-emerald-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Applications
                </a>

                <a href="{{ route('assets.email.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all group {{ request()->routeIs('assets.email.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <svg class="mr-3 h-5 w-5 transition-colors {{ request()->routeIs('assets.email.*') ? 'text-white' : 'text-emerald-500 group-hover:text-emerald-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    Email Users
                </a>

                <!-- SETTINGS -->
                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-3 mb-2 mt-4">System</div>

                <a href="{{ route('settings.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all group {{ request()->routeIs('settings.*') ? 'bg-slate-700 text-white shadow-lg' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <svg class="mr-3 h-5 w-5 transition-colors {{ request()->routeIs('settings.*') ? 'text-white' : 'text-slate-500 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Settings
                </a>

            </nav>
            
            <!-- User Footer -->
            <div class="p-4 border-t border-white/5 bg-slate-900/50 backdrop-blur shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-xs font-bold text-white">Adm</div>
                    <div>
                        <div class="text-xs font-bold text-white">Administrator</div>
                        <div class="text-[10px] text-emerald-500 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Online
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-slate-950 to-slate-900 relative">
            
            <!-- Top Bar (Desktop) -->
            <div class="hidden md:flex items-center justify-between p-6 sticky top-0 z-30 bg-slate-950/80 backdrop-blur-sm border-b border-white/5">
                <div class="flex items-center gap-4">
                    <h2 class="text-lg font-bold text-white tracking-wide">{{ $header ?? 'Dashboard' }}</h2>
                </div>
                <!-- Right Side Actions -->
                <div class="flex items-center gap-4">
                     <!-- Time -->
                    <div class="text-xs font-mono text-slate-400" x-data x-text="new Date().toLocaleTimeString()"></div>
                    
                    <!-- Notifications -->
                    <button class="relative p-2 text-slate-400 hover:text-white transition-colors">
                        <span x-cloak x-show="notifications.total > 0" class="absolute top-2 right-2 w-2 h-2 rounded-full bg-red-500 animate-ping"></span>
                        <span x-cloak x-show="notifications.total > 0" class="absolute top-2 right-2 w-2 h-2 rounded-full bg-red-500"></span>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    </button>
                    
                    <!-- Logout -->
                     <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-slate-400 hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Content -->
            <div class="p-6">
                @if(session('success'))
                    <div class="mb-6 p-4 rounded-lg bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        {{ session('success') }}
                    </div>
                @endif

                @yield('content')
            </div>

        </main>
        
        <!-- Background Overlay -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity 
             class="fixed inset-0 bg-black/50 backdrop-blur-sm z-30 md:hidden"></div>
    </div>

    @stack('scripts')
    
    <script>
        function secopsAppInit() {
            return {
                sidebarOpen: false,
                notifications: {
                    fum_alerts: 0,
                    wazuh_alerts: 0,
                    total: 0
                },
                init() {
                    this.fetchNotifications();
                    // Poll exactly every 10 seconds
                    setInterval(() => this.fetchNotifications(), 10000);
                },
                fetchNotifications() {
                    fetch('/notifications/pending')
                        .then(res => res.json())
                        .then(data => {
                            this.notifications = data;
                        })
                        .catch(err => console.error('Error fetching notifications:', err));
                }
            }
        }
    </script>
</body>
</html>
