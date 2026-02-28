@extends('layouts.dashboard')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Edit Server</h1>
            <p class="text-slate-500 text-sm mt-1">Update server details manually.</p>
        </div>
        <div class="px-3 py-1 bg-slate-100 dark:bg-slate-800 rounded text-xs text-slate-500 font-mono">
            Token: {{ $server->api_token }}
        </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white dark:bg-slate-900 rounded-xl p-8 border border-slate-200 dark:border-slate-800 shadow-lg">
         <form action="{{ route('assets.server.update', $server->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Hostname / Agent Name <span class="text-red-500">*</span></label>
                    <input type="text" name="hostname" value="{{ old('hostname', $server->name) }}" required class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">IP Address</label>
                    <input type="text" name="ip_address" value="{{ old('ip_address', $server->ip_address) }}" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Operating System</label>
                    <input type="text" name="os" value="{{ old('os', $server->os_name) }}" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all">
                </div>
            </div>

            <div class="pt-6 flex justify-end gap-3 border-t border-slate-100 dark:border-slate-800 mt-6">
                <a href="{{ route('assets.server') }}" class="px-6 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-medium rounded-lg transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors shadow-lg shadow-indigo-500/30">
                    Update Server
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
