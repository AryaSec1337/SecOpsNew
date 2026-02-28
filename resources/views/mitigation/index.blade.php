@extends('layouts.dashboard')

@section('content')
<div class="space-y-8">
    <!-- Header & Stats -->
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white tracking-tight">Investigation Logs</h1>
                <p class="text-slate-400 mt-1">Track and manage security incidents and investigations.</p>
            </div>
            <a href="{{ route('mitigation-logs.create') }}" class="group relative inline-flex items-center justify-center px-6 py-3 text-sm font-bold text-white transition-all duration-200 bg-blue-600 font-pj rounded-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-600 hover:bg-blue-500 shadow-lg shadow-blue-600/30">
                <span class="absolute inset-0 w-full h-full -mt-1 rounded-lg opacity-30 bg-gradient-to-b from-transparent via-transparent to-black"></span>
                <span class="relative flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    New Investigation
                </span>
            </a>
        </div>

        <!-- Status Stats Row -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Pending -->
            <div class="relative overflow-hidden group bg-slate-900/50 backdrop-blur-xl border border-white/5 rounded-2xl p-6 transition-all duration-300 hover:bg-slate-800/50 hover:border-amber-500/30 hover:shadow-lg hover:shadow-amber-500/10">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <svg class="w-24 h-24 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div class="relative">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="p-2 bg-amber-500/10 rounded-lg">
                            <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <h3 class="text-sm font-medium text-slate-400">Pending Review</h3>
                    </div>
                    <div class="text-4xl font-bold text-white tracking-tight">{{ $stats['pending'] }}</div>
                    <div class="mt-2 text-xs text-amber-500 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                        Requires attention
                    </div>
                </div>
            </div>

            <!-- In Progress -->
            <div class="relative overflow-hidden group bg-slate-900/50 backdrop-blur-xl border border-white/5 rounded-2xl p-6 transition-all duration-300 hover:bg-slate-800/50 hover:border-blue-500/30 hover:shadow-lg hover:shadow-blue-500/10">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <svg class="w-24 h-24 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                </div>
                <div class="relative">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="p-2 bg-blue-500/10 rounded-lg">
                            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                        </div>
                        <h3 class="text-sm font-medium text-slate-400">In Progress</h3>
                    </div>
                    <div class="text-4xl font-bold text-white tracking-tight">{{ $stats['in_progress'] }}</div>
                    <div class="mt-2 text-xs text-blue-400 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                        Active investigations
                    </div>
                </div>
            </div>

            <!-- Resolved -->
            <div class="relative overflow-hidden group bg-slate-900/50 backdrop-blur-xl border border-white/5 rounded-2xl p-6 transition-all duration-300 hover:bg-slate-800/50 hover:border-emerald-500/30 hover:shadow-lg hover:shadow-emerald-500/10">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <svg class="w-24 h-24 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div class="relative">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="p-2 bg-emerald-500/10 rounded-lg">
                            <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <h3 class="text-sm font-medium text-slate-400">Resolved</h3>
                    </div>
                    <div class="text-4xl font-bold text-white tracking-tight">{{ $stats['resolved'] }}</div>
                    <div class="mt-2 text-xs text-emerald-400 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        Completed cases
                    </div>
                </div>
            </div>
        </div>

        <!-- Type Stats Row -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <!-- Email Phishing -->
            <div class="relative overflow-hidden group bg-slate-900/50 backdrop-blur-xl border border-white/5 rounded-2xl p-5 transition-all duration-300 hover:bg-slate-800/50 hover:border-orange-500/30">
                <div class="flex flex-col gap-3">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-orange-500/10 rounded-xl">
                            <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-slate-400">Email Phishing</h3>
                            <p class="text-[10px] text-slate-600">Phishing incidents</p>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-orange-400">{{ $stats['email_phishing'] }}</div>
                </div>
            </div>

            <!-- SIEM / General Incident -->
            <div class="relative overflow-hidden group bg-slate-900/50 backdrop-blur-xl border border-white/5 rounded-2xl p-5 transition-all duration-300 hover:bg-slate-800/50 hover:border-cyan-500/30">
                <div class="flex flex-col gap-3">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-cyan-500/10 rounded-xl">
                            <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-slate-400">General</h3>
                            <p class="text-[10px] text-slate-600">Security events</p>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-cyan-400">{{ $stats['siem_incident'] }}</div>
                </div>
            </div>

            <!-- File Check -->
            <div class="relative overflow-hidden group bg-slate-900/50 backdrop-blur-xl border border-white/5 rounded-2xl p-5 transition-all duration-300 hover:bg-slate-800/50 hover:border-teal-500/30">
                <div class="flex flex-col gap-3">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-teal-500/10 rounded-xl">
                            <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-slate-400">File Check</h3>
                            <p class="text-[10px] text-slate-600">File analysis</p>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-teal-400">{{ $stats['file_check'] }}</div>
                </div>
            </div>

            <!-- Domain Check -->
            <div class="relative overflow-hidden group bg-slate-900/50 backdrop-blur-xl border border-white/5 rounded-2xl p-5 transition-all duration-300 hover:bg-slate-800/50 hover:border-purple-500/30">
                <div class="flex flex-col gap-3">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-purple-500/10 rounded-xl">
                            <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-slate-400">Domain Check</h3>
                            <p class="text-[10px] text-slate-600">URL/domain analysis</p>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-purple-400">{{ $stats['domain_check'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Buttons -->
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('mitigation-logs.index') }}" 
           class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ !request('type') ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/20' : 'bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700' }}">
            All Logs
        </a>
        <a href="{{ route('mitigation-logs.index', ['type' => 'Email Phishing']) }}" 
           class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ request('type') === 'Email Phishing' ? 'bg-orange-600 text-white shadow-lg shadow-orange-900/20' : 'bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700' }}">
            Email Phishing
        </a>
        <a href="{{ route('mitigation-logs.index', ['type' => 'File Check']) }}" 
           class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ request('type') === 'File Check' ? 'bg-teal-600 text-white shadow-lg shadow-teal-900/20' : 'bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700' }}">
            File Check
        </a>
        <a href="{{ route('mitigation-logs.index', ['type' => 'Domain Check']) }}" 
           class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ request('type') === 'Domain Check' ? 'bg-purple-600 text-white shadow-lg shadow-purple-900/20' : 'bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700' }}">
            Domain Check
        </a>
        <a href="{{ route('mitigation-logs.index', ['type' => 'General']) }}" 
           class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ request('type') === 'General' ? 'bg-cyan-600 text-white shadow-lg shadow-cyan-900/20' : 'bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700' }}">
            General
        </a>
    </div>

    <!-- Investigation List -->
    <div id="logs-wrapper">
        @include('mitigation.partials.logs-table')
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterLinks = document.querySelectorAll('.flex.flex-wrap.items-center.gap-2 a');
        const logsWrapper = document.getElementById('logs-wrapper');

        filterLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.getAttribute('href');

                // Update active state visually
                filterLinks.forEach(l => {
                    l.className = 'px-4 py-2 rounded-lg text-sm font-medium transition-all bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700';
                });
                
                // Set active style based on type
                const urlParams = new URL(url).searchParams;
                const type = urlParams.get('type');
                
                if (!type) {
                    this.className = 'px-4 py-2 rounded-lg text-sm font-medium transition-all bg-blue-600 text-white shadow-lg shadow-blue-900/20';
                } else if (type === 'Email Phishing') {
                    this.className = 'px-4 py-2 rounded-lg text-sm font-medium transition-all bg-orange-600 text-white shadow-lg shadow-orange-900/20';
                } else if (type === 'File Check') {
                    this.className = 'px-4 py-2 rounded-lg text-sm font-medium transition-all bg-teal-600 text-white shadow-lg shadow-teal-900/20';
                } else if (type === 'Domain Check') {
                    this.className = 'px-4 py-2 rounded-lg text-sm font-medium transition-all bg-purple-600 text-white shadow-lg shadow-purple-900/20';
                } else if (type === 'General') {
                    this.className = 'px-4 py-2 rounded-lg text-sm font-medium transition-all bg-cyan-600 text-white shadow-lg shadow-cyan-900/20';
                }

                // Push state to URL
                window.history.pushState({}, '', url);

                // Fetch data
                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    logsWrapper.innerHTML = html;
                })
                .catch(error => console.error('Error:', error));
            });
        });

        // Handle pagination clicks similarly
        document.addEventListener('click', function(e) {
            const paginationLink = e.target.closest('.pagination a');
            if (paginationLink && logsWrapper.contains(paginationLink)) {
                e.preventDefault();
                const url = paginationLink.getAttribute('href');
                
                window.history.pushState({}, '', url);

                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    logsWrapper.innerHTML = html;
                    // Scroll to top of table
                    document.getElementById('logs-table-container').scrollIntoView({ behavior: 'smooth' });
                })
                .catch(error => console.error('Error:', error));
            }
        });

        // Handle browser back/forward buttons
        window.addEventListener('popstate', function() {
            fetch(window.location.href, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                logsWrapper.innerHTML = html;
                // Update active filter button based on current URL
                const currentType = new URLSearchParams(window.location.search).get('type');
                // (Optional: Logic to re-highlight correct button on back/forward)
            });
        });
    });
</script>
</div>
@endsection
