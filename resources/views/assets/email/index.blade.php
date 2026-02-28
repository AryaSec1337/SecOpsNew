@extends('layouts.dashboard')

@section('content')
<div class="space-y-8" x-data="emailManager()">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-white tracking-tight">Email Users</h1>
            <p class="text-slate-400 mt-1">Manage organization email accounts and departments.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="px-4 py-2 bg-slate-800/60 border border-white/5 rounded-xl text-sm text-slate-300">
                <span class="text-white font-bold">{{ $totalCount }}</span> Total Accounts
            </div>
            <button @click="showImportModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-medium rounded-xl transition-colors border border-white/5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                Import
            </button>
            <button @click="openCreate()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-bold rounded-xl transition-colors shadow-lg shadow-emerald-600/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Add Email
            </button>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="glass-panel rounded-2xl p-5 border border-slate-800 bg-slate-900/30 backdrop-blur-md">
        <form method="GET" action="{{ route('assets.email.index') }}" class="flex flex-col md:flex-row gap-4">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email, or department..." class="w-full pl-11 pr-4 py-3 bg-slate-800/50 border border-white/5 rounded-xl text-white placeholder-slate-500 focus:border-emerald-500/50 focus:ring-1 focus:ring-emerald-500/50 focus:outline-none transition-all text-sm">
            </div>
            <div class="relative">
                <select name="department" onchange="this.form.submit()" class="appearance-none pl-4 pr-10 py-3 bg-slate-800/50 border border-white/5 rounded-xl text-white focus:border-emerald-500/50 focus:ring-1 focus:ring-emerald-500/50 focus:outline-none transition-all text-sm min-w-[200px]">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}" {{ request('department') === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                    @endforeach
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>
            <button type="submit" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold rounded-xl transition-colors shadow-lg shadow-emerald-600/20">
                Search
            </button>
            @if(request('search') || request('department'))
            <a href="{{ route('assets.email.index') }}" class="px-6 py-3 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-medium rounded-xl transition-colors border border-white/5">
                Clear
            </a>
            @endif
        </form>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
    <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-emerald-400 text-sm flex items-center gap-2">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="p-4 bg-red-500/10 border border-red-500/20 rounded-xl text-red-400 text-sm flex items-center gap-2">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        {{ session('error') }}
    </div>
    @endif

    <!-- Email Users Table -->
    <div class="glass-panel rounded-2xl overflow-hidden border border-slate-800 bg-slate-900/30 backdrop-blur-md shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-900/80 text-slate-400 border-b border-white/5">
                        <th class="px-6 py-5 font-semibold uppercase tracking-wider text-xs w-10">#</th>
                        <th class="px-6 py-5 font-semibold uppercase tracking-wider text-xs">Name</th>
                        <th class="px-6 py-5 font-semibold uppercase tracking-wider text-xs">Email Address</th>
                        <th class="px-6 py-5 font-semibold uppercase tracking-wider text-xs">Type</th>
                        <th class="px-6 py-5 font-semibold uppercase tracking-wider text-xs">Department</th>
                        <th class="px-6 py-5 font-semibold uppercase tracking-wider text-xs text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($emails as $index => $email)
                    <tr class="hover:bg-white/[0.02] transition-colors group">
                        <td class="px-6 py-4 text-slate-500 text-xs font-mono">{{ $emails->firstItem() + $index }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-emerald-500/10 flex items-center justify-center text-emerald-400 text-sm font-bold uppercase shrink-0">
                                    {{ substr($email->display_name ?? $email->email_address, 0, 1) }}
                                </div>
                                <span class="font-medium text-white">{{ $email->display_name ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-slate-300 font-mono text-xs">{{ $email->email_address }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $typeStyles = [
                                    'UserMailbox' => ['bg' => 'bg-blue-500/10', 'text' => 'text-blue-400', 'border' => 'border-blue-500/20', 'label' => 'User'],
                                    'SharedMailbox' => ['bg' => 'bg-purple-500/10', 'text' => 'text-purple-400', 'border' => 'border-purple-500/20', 'label' => 'Shared'],
                                    'RoomMailbox' => ['bg' => 'bg-amber-500/10', 'text' => 'text-amber-400', 'border' => 'border-amber-500/20', 'label' => 'Room'],
                                    'EquipmentMailbox' => ['bg' => 'bg-cyan-500/10', 'text' => 'text-cyan-400', 'border' => 'border-cyan-500/20', 'label' => 'Equipment'],
                                    'MailUser' => ['bg' => 'bg-green-500/10', 'text' => 'text-green-400', 'border' => 'border-green-500/20', 'label' => 'Mail User'],
                                ];
                                $ts = $typeStyles[$email->recipient_type] ?? ['bg' => 'bg-slate-700/50', 'text' => 'text-slate-400', 'border' => 'border-slate-600/30', 'label' => $email->recipient_type ?? '-'];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $ts['bg'] }} {{ $ts['text'] }} {{ $ts['border'] }}">
                                {{ $ts['label'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($email->department)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    {{ $email->department }}
                                </span>
                            @else
                                <span class="text-slate-600 text-xs italic">Not set</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button @click="openEdit({{ json_encode($email) }})" class="p-2 text-slate-400 hover:text-blue-400 hover:bg-blue-500/10 rounded-lg transition-all" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                                <button @click="openDelete({{ $email->id }}, '{{ addslashes($email->display_name ?? $email->email_address) }}')" class="p-2 text-slate-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-all" title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 rounded-full bg-slate-800/50 flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                </div>
                                <h3 class="text-lg font-medium text-white mb-1">No email users found</h3>
                                <p class="text-slate-500 text-sm">
                                    @if(request('search') || request('department'))
                                        Try adjusting your search or filter criteria.
                                    @else
                                        Add email users or import from Excel to get started.
                                    @endif
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-white/5 bg-slate-900/50">
            {{ $emails->links() }}
        </div>
    </div>

    <!-- ========== CREATE / EDIT MODAL ========== -->
    <div x-show="showFormModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showFormModal = false"></div>
        <div class="relative bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl w-full max-w-lg" @click.stop>
            <div class="flex items-center justify-between p-6 border-b border-slate-800">
                <h3 class="text-lg font-bold text-white" x-text="editId ? 'Edit Email User' : 'Add Email User'"></h3>
                <button @click="showFormModal = false" class="p-2 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form :action="editId ? '{{ url('assets/email') }}/' + editId : '{{ route('assets.email.store') }}'" method="POST" class="p-6 space-y-5">
                @csrf
                <template x-if="editId">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Display Name <span class="text-red-400">*</span></label>
                    <input type="text" name="display_name" x-model="formData.display_name" required class="w-full bg-slate-950/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-emerald-500/50 focus:ring-1 focus:ring-emerald-500/50 focus:outline-none transition-all text-sm" placeholder="John Doe">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Email Address <span class="text-red-400">*</span></label>
                    <input type="email" name="email_address" x-model="formData.email_address" required class="w-full bg-slate-950/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-emerald-500/50 focus:ring-1 focus:ring-emerald-500/50 focus:outline-none transition-all text-sm" placeholder="john.doe@company.com">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-2">Recipient Type</label>
                        <select name="recipient_type" x-model="formData.recipient_type" class="w-full bg-slate-950/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-emerald-500/50 focus:ring-1 focus:ring-emerald-500/50 focus:outline-none transition-all text-sm appearance-none">
                            <option value="">Select type</option>
                            <option value="UserMailbox">User Mailbox</option>
                            <option value="SharedMailbox">Shared Mailbox</option>
                            <option value="RoomMailbox">Room Mailbox</option>
                            <option value="EquipmentMailbox">Equipment Mailbox</option>
                            <option value="MailUser">Mail User</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-2">Department</label>
                        <input type="text" name="department" x-model="formData.department" class="w-full bg-slate-950/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-emerald-500/50 focus:ring-1 focus:ring-emerald-500/50 focus:outline-none transition-all text-sm" placeholder="IT Department">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="showFormModal = false" class="px-5 py-2.5 text-slate-400 hover:text-white hover:bg-white/5 rounded-xl transition-all font-medium text-sm">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-bold rounded-xl transition-colors shadow-lg shadow-emerald-600/20">
                        <span x-text="editId ? 'Update' : 'Create'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========== DELETE CONFIRMATION MODAL ========== -->
    <div x-show="showDeleteModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showDeleteModal = false"></div>
        <div class="relative bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl w-full max-w-md" @click.stop>
            <div class="p-6 text-center">
                <div class="w-14 h-14 rounded-full bg-red-500/10 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">Delete Email User</h3>
                <p class="text-slate-400 text-sm">Are you sure you want to delete <span class="text-white font-semibold" x-text="deleteName"></span>? This action cannot be undone.</p>
            </div>
            <div class="flex items-center justify-center gap-3 p-6 pt-0">
                <button @click="showDeleteModal = false" class="px-5 py-2.5 text-slate-400 hover:text-white hover:bg-white/5 rounded-xl transition-all font-medium text-sm">Cancel</button>
                <form :action="'{{ url('assets/email') }}/' + deleteId" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-6 py-2.5 bg-red-600 hover:bg-red-500 text-white text-sm font-bold rounded-xl transition-colors shadow-lg shadow-red-600/20">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <!-- ========== IMPORT MODAL ========== -->
    <div x-show="showImportModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showImportModal = false"></div>
        <div class="relative bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl w-full max-w-lg" @click.stop>
            <div class="flex items-center justify-between p-6 border-b border-slate-800">
                <h3 class="text-lg font-bold text-white">Import Email Users</h3>
                <button @click="showImportModal = false" class="p-2 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form action="{{ route('assets.email.import') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
                @csrf
                <div>
                    <div class="relative w-full h-36 rounded-xl border-2 border-dashed border-slate-700 bg-slate-950/30 hover:bg-slate-950/50 hover:border-emerald-500/50 transition-all flex flex-col items-center justify-center cursor-pointer" @click="$refs.importFile.click()">
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" required x-ref="importFile" @change="importFileName = $event.target.files[0]?.name || ''" class="hidden">
                        <svg class="w-10 h-10 text-slate-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <p class="text-sm" :class="importFileName ? 'text-emerald-400 font-semibold' : 'text-slate-400'" x-text="importFileName || 'Click to select an Excel file (.xlsx, .xls, .csv)'"></p>
                    </div>
                </div>

                <div class="bg-slate-800/50 rounded-xl p-4 border border-slate-700/50">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Expected Columns</h4>
                    <div class="grid grid-cols-2 gap-2 text-xs text-slate-300">
                        <div class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Display name</div>
                        <div class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Email address</div>
                        <div class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span> Recipient type</div>
                        <div class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span> Department</div>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-2">Existing emails will be updated, new emails will be created.</p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="showImportModal = false" class="px-5 py-2.5 text-slate-400 hover:text-white hover:bg-white/5 rounded-xl transition-all font-medium text-sm">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-bold rounded-xl transition-colors shadow-lg shadow-emerald-600/20">
                        Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function emailManager() {
    return {
        showFormModal: false,
        showDeleteModal: false,
        showImportModal: false,
        editId: null,
        deleteId: null,
        deleteName: '',
        importFileName: '',
        formData: {
            display_name: '',
            email_address: '',
            recipient_type: '',
            department: '',
        },
        openCreate() {
            this.editId = null;
            this.formData = { display_name: '', email_address: '', recipient_type: '', department: '' };
            this.showFormModal = true;
        },
        openEdit(email) {
            this.editId = email.id;
            this.formData = {
                display_name: email.display_name || '',
                email_address: email.email_address || '',
                recipient_type: email.recipient_type || '',
                department: email.department || '',
            };
            this.showFormModal = true;
        },
        openDelete(id, name) {
            this.deleteId = id;
            this.deleteName = name;
            this.showDeleteModal = true;
        }
    };
}
</script>
@endsection
