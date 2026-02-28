@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('cti.domain.index') }}" class="text-slate-500 hover:text-indigo-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white">{{ $domain->name }}</h1>
            </div>
            <p class="text-slate-500 text-sm mt-1 ml-8">Monitoring Details & CTI Intelligence</p>
        </div>
        <div class="flex gap-2 items-center">
            <form action="{{ route('cti.domain.scan', $domain->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Scan Now
                </button>
            </form>
             <span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                Auto: Daily 00:00
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column: Overview & SSL -->
        <div class="space-y-6 lg:col-span-2">
            
            <!-- VT Stats -->
            <div class="bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm">
                <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
                    <span class="text-indigo-500">🛡️</span> VirusTotal Analysis
                </h3>
                @if($domain->latestScan)
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div class="p-4 bg-red-50 dark:bg-red-900/10 rounded-lg text-center border border-red-100 dark:border-red-900/30">
                            <div class="text-2xl font-bold text-red-600">{{ $domain->latestScan->vt_stats['malicious'] ?? 0 }}</div>
                            <div class="text-xs text-red-500 font-bold uppercase">Malicious</div>
                        </div>
                        <div class="p-4 bg-green-50 dark:bg-green-900/10 rounded-lg text-center border border-green-100 dark:border-green-900/30">
                            <div class="text-2xl font-bold text-green-600">{{ $domain->latestScan->vt_stats['harmless'] ?? 0 }}</div>
                            <div class="text-xs text-green-500 font-bold uppercase">Harmless</div>
                        </div>
                         <div class="p-4 bg-orange-50 dark:bg-orange-900/10 rounded-lg text-center border border-orange-100 dark:border-orange-900/30">
                            <div class="text-2xl font-bold text-orange-600">{{ $domain->latestScan->vt_stats['suspicious'] ?? 0 }}</div>
                            <div class="text-xs text-orange-500 font-bold uppercase">Suspicious</div>
                        </div>
                        <div class="p-4 bg-slate-50 dark:bg-slate-800 rounded-lg text-center border border-slate-200 dark:border-slate-700">
                            <div class="text-2xl font-bold text-slate-600 dark:text-slate-300">{{ $domain->latestScan->vt_stats['undetected'] ?? 0 }}</div>
                            <div class="text-xs text-slate-500 font-bold uppercase">Undetected</div>
                        </div>
                    </div>
                    <p class="text-sm text-slate-500">Last Scanned: {{ $domain->latestScan->scan_date->diffForHumans() }}</p>
                @else
                    <div class="p-8 text-center text-slate-500 bg-slate-50 dark:bg-slate-800/50 rounded-lg">
                        No scan data available yet.
                    </div>
                @endif
            </div>

            <!-- SSL Status -->
            <div class="bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm">
                 <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
                    <span class="text-green-500">🔒</span> SSL Certificate Status
                </h3>
                @if($domain->sslStatus)
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-16 h-16 rounded-full flex items-center justify-center bg-{{ $domain->sslStatus->is_valid ? 'green' : 'red' }}-100 text-{{ $domain->sslStatus->is_valid ? 'green' : 'red' }}-600">
                             <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $domain->sslStatus->is_valid ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' : 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z' }}"></path></svg>
                        </div>
                        <div>
                            <p class="text-xl font-bold {{ $domain->sslStatus->is_valid ? 'text-green-600' : 'text-red-600' }}">
                                {{ $domain->sslStatus->is_valid ? 'Certificate Valid' : 'Certificate Invalid/Expired' }}
                            </p>
                            <p class="text-slate-500">Expires in <span class="font-bold text-slate-800 dark:text-white">{{ $domain->sslStatus->days_remaining }} days</span></p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div class="p-3 bg-slate-50 dark:bg-slate-800 rounded-lg">
                            <span class="block text-slate-500 text-xs uppercase mb-1">Issuer</span>
                            <span class="font-mono text-slate-700 dark:text-slate-300 break-words">{{ $domain->sslStatus->issuer }}</span>
                        </div>
                         <div class="p-3 bg-slate-50 dark:bg-slate-800 rounded-lg">
                            <span class="block text-slate-500 text-xs uppercase mb-1">Subject</span>
                            <span class="font-mono text-slate-700 dark:text-slate-300 break-words">{{ $domain->sslStatus->subject }}</span>
                        </div>
                        <div class="p-3 bg-slate-50 dark:bg-slate-800 rounded-lg">
                            <span class="block text-slate-500 text-xs uppercase mb-1">Valid From</span>
                            <span class="font-mono text-slate-700 dark:text-slate-300">{{ $domain->sslStatus->valid_from->format('d M Y H:i') }}</span>
                        </div>
                        <div class="p-3 bg-slate-50 dark:bg-slate-800 rounded-lg">
                            <span class="block text-slate-500 text-xs uppercase mb-1">Valid Until</span>
                            <span class="font-mono text-slate-700 dark:text-slate-300">{{ $domain->sslStatus->valid_until->format('d M Y H:i') }}</span>
                        </div>
                    </div>
                @else
                    <p class="text-slate-500 italic">No SSL data found. Run scan to fetch.</p>
                @endif
            </div>

        </div>

        <!-- Right Column: DNS -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm h-full">
                 <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
                    <span class="text-blue-500">📡</span> DNS Records
                </h3>
                
                @if($domain->dnsRecords->count() > 0)
                    <div class="space-y-3">
                        @foreach($domain->dnsRecords as $record)
                        <div class="p-3 rounded-lg border border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <div class="flex items-center justify-between mb-1">
                                <span class="px-2 py-0.5 rounded text-xs font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 w-10 text-center">
                                    {{ $record->record_type }}
                                </span>
                                <span class="text-xs text-slate-400">{{ $record->created_at->format('d M') }}</span>
                            </div>
                            <code class="text-xs font-mono text-slate-600 dark:text-slate-300 break-all block">
                                {{ $record->value }}
                            </code>
                        </div>
                        @endforeach
                    </div>
                @else
                     <p class="text-slate-500 italic">No DNS records found.</p>
                @endif
            </div>
        </div>

    </div>

    <!-- Typosquatting Section -->
    <div class="bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm mt-6">
        <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
            <span class="text-purple-500">👯‍♂️</span> Typosquatting & Phishing Detection
        </h3>
        <p class="text-sm text-slate-500 mb-4">Domains similar to yours that are currently active (Registered). Could be used for phishing.</p>

        @if(isset($typosquats) && $typosquats->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 dark:text-slate-400">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-800 dark:text-slate-200 uppercase font-bold text-xs">
                        <tr>
                            <th class="px-4 py-3">Suspicious Domain</th>
                            <th class="px-4 py-3">IP Address</th>
                            <th class="px-4 py-3">MX Record (Mail)</th>
                            <th class="px-4 py-3">Detected At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($typosquats as $sq)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-4 py-3 font-medium text-slate-800 dark:text-white">
                                {{ $sq->permuted_domain }}
                                <a href="http://{{ $sq->permuted_domain }}" target="_blank" class="text-xs text-indigo-500 hover:underline ml-1">↗</a>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $sq->ip_address ?? 'N/A' }}</td>
                            <td class="px-4 py-3 font-mono text-xs">
                                @if($sq->mx_record)
                                    <span class="text-red-500 font-bold">YES</span> ({{ Str::limit($sq->mx_record, 20) }})
                                @else
                                    <span class="text-slate-400">No</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ $sq->scan_date->format('d M Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-6 bg-green-50 dark:bg-green-900/10 rounded-lg border border-green-100 dark:border-green-900/30 text-center">
                <p class="text-green-700 dark:text-green-400 font-medium">Clean! No similar domains detected (yet).</p>
                <p class="text-xs text-green-600 dark:text-green-500 mt-1">Run a scan to verify.</p>
            </div>
        @endif
    </div>
</div>
@endsection
