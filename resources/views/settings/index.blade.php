@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">System Settings</h1>
            <p class="text-slate-500 text-sm mt-1">Configure system behavior and data retention policies.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 p-4 rounded-lg flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-500/10 border border-red-500/20 text-red-500 p-4 rounded-lg flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Settings Form -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="font-bold text-lg text-slate-800 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Data Retention Policy
                    </h3>
                    <p class="text-sm text-slate-500 mt-1">Automatically delete old logs to save database space.</p>
                </div>
                
                <form action="{{ route('settings.update') }}" method="POST" class="p-6 space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Activity Logs -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Activity Logs Retention (Days)
                        </label>
                        <div class="flex items-center gap-3">
                            <input type="number" name="retention_activity_logs" value="{{ $settings['retention_activity_logs'] ?? 30 }}" class="w-24 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                            <span class="text-xs text-slate-500">Days</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">Logs in <code>activity_logs</code> older than this will be permanently deleted.</p>
                    </div>

                    <!-- FIM Logs -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            File Integrity Logs Retention (Days)
                        </label>
                        <div class="flex items-center gap-3">
                            <input type="number" name="retention_fim_logs" value="{{ $settings['retention_fim_logs'] ?? 30 }}" class="w-24 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                            <span class="text-xs text-slate-500">Days</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">Logs in <code>file_integrity_logs</code> older than this will be permanently deleted.</p>
                    </div>

                    <!-- Blocked IPs -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Blocked IP History Retention (Days)
                        </label>
                        <div class="flex items-center gap-3">
                            <input type="number" name="retention_blocked_ips" value="{{ $settings['retention_blocked_ips'] ?? 90 }}" class="w-24 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                            <span class="text-xs text-slate-500">Days</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">Records in <code>blocked_ips</code> older than this will be pruned (Active blocks are respected).</p>
                    </div>

                    <!-- Incidents -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Incident Reports Retention (Days)
                        </label>
                        <div class="flex items-center gap-3">
                            <input type="number" name="retention_incidents" value="{{ $settings['retention_incidents'] ?? 365 }}" class="w-24 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                            <span class="text-xs text-slate-500">Days</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">Closed incidents older than this will be archived/deleted.</p>
                    </div>

                    <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-medium transition-colors shadow-lg shadow-indigo-500/30">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar / Actions -->
        <div class="space-y-6">
            <!-- Manual Cleanup -->
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6">
                <h3 class="font-bold text-lg text-slate-800 dark:text-white mb-2">Manual Cleanup</h3>
                <p class="text-sm text-slate-500 mb-4">Run the cleanup job immediately based on current settings.</p>
                
                <form action="{{ route('settings.cleanup') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 transition-colors flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Run Cleanup Now
                    </button>
                </form>
            </div>

            <!-- Stats -->
            <div class="bg-indigo-900/20 border border-indigo-500/20 rounded-xl p-6">
                <!-- ... existing stats ... -->
                <h3 class="font-bold text-indigo-400 mb-2">Database Stats</h3>
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Activity Logs</span>
                        <span class="text-slate-300 font-mono">{{ DB::table('activity_logs')->count() }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">FIM Logs</span>
                        <span class="text-slate-300 font-mono">{{ DB::table('file_integrity_logs')->count() }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Blocked IPs</span>
                        <span class="text-slate-300 font-mono">{{ DB::table('blocked_ips')->count() }}</span>
                    </div>
                    <div class="pt-3 border-t border-indigo-500/20">
                         <div class="flex justify-between text-sm">
                            <span class="text-slate-500">Total Backups</span>
                            <span class="text-slate-300 font-mono">{{ \App\Models\Backup::count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Backup History (New Section) -->
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                    <h3 class="font-bold text-slate-800 dark:text-white text-sm">Backup History</h3>
                </div>
                <div class="max-h-64 overflow-y-auto">
                    @php $backups = \App\Models\Backup::latest()->take(10)->get(); @endphp
                    @if($backups->isEmpty())
                        <div class="p-4 text-center text-slate-500 text-xs text-italic">No backups found.</div>
                    @else
                        <ul class="divide-y divide-slate-200 dark:divide-slate-800">
                            @foreach($backups as $backup)
                            <li class="p-3 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors flex justify-between items-center group">
                                <div>
                                    <p class="text-xs font-semibold text-slate-700 dark:text-slate-300 truncate w-40" title="{{ $backup->filename }}">{{ $backup->filename }}</p>
                                    <p class="text-[10px] text-slate-400 flex gap-2">
                                        <span>{{ $backup->created_at->diffForHumans() }}</span>
                                        <span>•</span>
                                        <span>{{ $backup->size_formatted }}</span>
                                    </p>
                                </div>
                                <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('settings.backup.download', $backup->id) }}" class="text-indigo-500 hover:text-indigo-400 p-1" title="Download SQL">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    </a>
                                    <form action="{{ route('settings.backup.destroy', $backup->id) }}" method="POST" onsubmit="return confirm('Delete this backup?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-400 p-1" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
