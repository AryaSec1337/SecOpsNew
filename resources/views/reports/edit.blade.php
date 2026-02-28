@extends('layouts.dashboard')

@section('content')
<div class="space-y-8 animate-fade-in">
    
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row items-center justify-between gap-6 glass-panel p-8 rounded-3xl relative overflow-hidden border border-white/10 shadow-2xl">
        <div class="relative z-10">
            <h1 class="text-4xl font-black text-white tracking-tighter flex items-center gap-4">
                <div class="p-3 bg-blue-600 rounded-xl shadow-lg shadow-blue-500/30">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </div>
                <div>
                    Refine Security Report
                    <div class="text-sm font-medium text-blue-300 mt-1 font-mono">ID: {{ $report->period }}-{{ $report->id }}</div>
                </div>
            </h1>
        </div>
        <div class="relative z-10 flex gap-4">
            <a href="{{ route('reports.show', $report->id) }}" class="px-6 py-3 text-slate-400 hover:text-white transition-colors font-bold uppercase tracking-wider text-xs">Cancel</a>
            <button type="submit" form="edit-form" class="px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white rounded-xl font-bold shadow-xl shadow-blue-500/20 transition-all transform hover:scale-105 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Save Changes
            </button>
        </div>
        
        <!-- Background Decoration -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-blue-500/20 rounded-full blur-3xl -mr-16 -mt-16 pointer-events-none"></div>
    </div>

    <!-- MAIN FORM -->
    <form id="edit-form" action="{{ route('reports.update', $report->id) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf
        @method('PUT')

        <!-- SECTION 1: EXECUTIVE & STRATEGY -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
             <div class="lg:col-span-2 space-y-8">
                <!-- Business Impact -->
                <div class="glass-panel p-6 rounded-2xl border border-white/5">
                    <div class="flex items-center gap-3 mb-4 text-red-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg>
                        <h2 class="text-lg font-bold uppercase tracking-wide">Business Impact Analysis</h2>
                    </div>
                    <textarea name="impact_analysis" rows="4" class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-5 py-4 text-slate-200 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 transition-all placeholder-slate-600" placeholder="Describe the operational and financial impact of this incident...">{{ $report->summary_json['executive']['impact_analysis'] ?? '' }}</textarea>
                </div>

                <!-- Recommendations -->
                <div class="glass-panel p-6 rounded-2xl border border-white/5">
                    <div class="flex items-center gap-3 mb-4 text-emerald-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <h2 class="text-lg font-bold uppercase tracking-wide">Strategic Recommendations</h2>
                    </div>
                    <textarea name="recommendations" rows="6" class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-5 py-4 text-slate-200 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all placeholder-slate-600 font-mono text-sm leading-relaxed">{{ 
                        is_array($report->summary_json['recommendations'] ?? '') 
                        ? implode("\n", $report->summary_json['recommendations']) 
                        : ($report->summary_json['recommendations'] ?? '') 
                    }}</textarea>
                    <p class="text-xs text-slate-500 mt-2 px-1">Tip: Enter one recommendation per line.</p>
                </div>
             </div>

             <!-- SIDEBAR: STATUS -->
             <div class="space-y-8">
                 <div class="glass-panel p-6 rounded-2xl border border-white/5 bg-slate-800/50">
                     <label class="block text-xs font-bold uppercase text-slate-400 mb-3 tracking-widest">Report Lifecycle</label>
                     <select name="status" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition-colors cursor-pointer appearance-none">
                        <option value="Draft" {{ ($report->summary_json['meta']['status'] ?? '') == 'Draft' ? 'selected' : '' }}>Draft</option>
                        <option value="Under Review" {{ ($report->summary_json['meta']['status'] ?? '') == 'Under Review' ? 'selected' : '' }}>Under Review</option>
                        <option value="Success Mitigasi" {{ ($report->summary_json['meta']['status'] ?? '') == 'Success Mitigasi' ? 'selected' : '' }}>Success Mitigasi</option>
                        <option value="Final" {{ ($report->summary_json['meta']['status'] ?? '') == 'Final' ? 'selected' : '' }}>Final</option>
                    </select>
                    <div class="mt-4 p-4 bg-slate-900 rounded-xl border border-slate-800">
                        <div class="text-[10px] text-slate-500 uppercase tracking-widest mb-1">Current Risk Score</div>
                        <div class="text-2xl font-black text-white">{{ $report->summary_json['executive']['risk_score'] ?? 'N/A' }}</div>
                    </div>
                 </div>
             </div>
        </div>

        <!-- SECTION 2: TECHNICAL & MITRE -->
        <div class="glass-panel p-8 rounded-3xl border border-white/5 space-y-8">
            <div class="flex items-center gap-3 text-purple-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                <h2 class="text-xl font-bold uppercase tracking-wide">Technical Classification</h2>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                 <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-3 tracking-widest">Root Cause (Confirmed)</label>
                    <input type="text" name="root_cause" value="{{ $report->summary_json['technical']['root_cause'] ?? 'Unknown' }}" class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-5 py-4 text-white focus:outline-none focus:border-purple-500 transition-colors placeholder-slate-600">
                 </div>
            </div>

            <!-- MITRE MATRIX ACCORDION -->
            <div x-data="{ 
                selected: {{ json_encode($report->summary_json['technical']['mitre_techniques'] ?? []) }},
                toggle(value) {
                    if (this.selected.includes(value)) {
                        this.selected = this.selected.filter(i => i !== value);
                    } else {
                        this.selected.push(value);
                    }
                }
            }" class="border-t border-white/5 pt-8">
                
                <label class="block text-xs font-bold uppercase text-slate-400 mb-6 tracking-widest">MITRE ATT&CK Matrix Selection</label>

                <!-- Selected Tags Area -->
                <div class="flex flex-wrap gap-2 mb-6 min-h-[50px] p-4 bg-slate-900/30 rounded-xl border border-dashed border-slate-700/50">
                    <template x-if="selected.length === 0">
                        <div class="text-slate-500 text-sm italic py-1">No techniques selected. Open the matrix below to add tags.</div>
                    </template>
                    <template x-for="tag in selected" :key="tag">
                        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-purple-500/20 text-purple-300 border border-purple-500/30 text-xs font-bold shadow-sm">
                            <span x-text="tag"></span>
                            <button type="button" @click="toggle(tag)" class="hover:text-white transition-colors w-4 h-4 flex items-center justify-center rounded-full hover:bg-purple-500/30">&times;</button>
                        </span>
                    </template>
                    <!-- Hidden Input for Form Submission -->
                    <template x-for="tag in selected" :key="tag">
                        <input type="hidden" name="mitre_techniques[]" :value="tag">
                    </template>
                </div>

                <!-- Accordion List -->
                <div class="space-y-3">
                    @foreach($matrix as $column)
                        <div class="border border-slate-800 rounded-xl overflow-hidden bg-slate-900/20" x-data="{ open: false }">
                            <button type="button" @click="open = !open" class="w-full flex items-center justify-between p-4 hover:bg-slate-800/50 transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="w-2 h-2 rounded-full bg-purple-500 shadow-[0_0_10px_theme('colors.purple.500')]"></div>
                                    <span class="font-bold text-slate-200 uppercase tracking-wider text-sm">{{ $column['tactic']['name'] }}</span>
                                    <span class="text-xs text-slate-500 font-mono">({{ count($column['techniques']) }})</span>
                                </div>
                                <svg class="w-4 h-4 text-slate-500 transition-transform duration-300" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>

                            <div x-show="open" x-collapse class="border-t border-slate-800 bg-black/20">
                                <div class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                    @foreach($column['techniques'] as $tech)
                                        @php 
                                            // Format: "Tactic: ID - Name"
                                            // e.g. "Reconnaissance: T1595 - Active Scanning"
                                            $value = $column['tactic']['name'] . ': ' . $tech['id'] . ' - ' . $tech['name']; 
                                        @endphp
                                        <label class="flex items-start gap-3 p-3 rounded-lg hover:bg-white/5 cursor-pointer group transition-colors select-none">
                                            <input type="checkbox" value="{{ $value }}" class="peer sr-only" 
                                                   :checked="selected.includes('{{ $value }}')" 
                                                   @change="toggle('{{ $value }}')">
                                            
                                            <div class="w-4 h-4 mt-0.5 flex-shrink-0 rounded border border-slate-600 peer-checked:bg-purple-500 peer-checked:border-purple-500 flex items-center justify-center transition-all bg-slate-900">
                                                <svg class="w-3 h-3 text-white opacity-0 peer-checked:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                            </div>
                                            
                                            <div class="text-xs text-slate-400 peer-checked:text-white transition-colors">
                                                <span class="font-mono text-purple-400 font-bold mr-1">{{ $tech['id'] }}</span> {{ $tech['name'] }}
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- SECTION 3: EVIDENCE UPLOAD -->
        <div class="glass-panel p-8 rounded-3xl border border-white/5" x-data="{ 
            files: [],
            isDragging: false,
            handleDrop(e) {
                this.isDragging = false;
                // Note: The input catches the drop natively because it covers the div.
                // We just need to sync the files array on change.
            }
        }">
             <div class="flex items-center gap-3 mb-6 text-cyan-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                <h2 class="text-xl font-bold uppercase tracking-wide">Artifacts & Evidence</h2>
            </div>
            
            <!-- Upload Zone -->
            <div class="relative group">
                <div class="border-2 border-dashed rounded-2xl p-10 text-center transition-all duration-300 ease-out"
                     :class="isDragging ? 'border-cyan-400 bg-cyan-400/10 scale-[1.02] shadow-[0_0_30px_rgba(34,211,238,0.2)]' : 'border-slate-700 bg-slate-900/50 hover:border-cyan-500/30 hover:bg-cyan-500/5'"
                     @dragover.prevent="isDragging = true"
                     @dragleave.prevent="isDragging = false"
                     @drop.prevent="handleDrop($event)">
                    
                    <input type="file" name="artifacts[]" multiple class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                           @change="files = Array.from($event.target.files); isDragging = false"
                           @dragenter="isDragging = true"
                           @dragleave="isDragging = false">
                    
                    <div class="flex flex-col items-center justify-center space-y-4 pointer-events-none transition-transform duration-300"
                         :class="isDragging ? 'scale-110' : ''">
                        <div class="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center text-slate-500 group-hover:text-cyan-400 transition-all duration-300 shadow-lg group-hover:shadow-cyan-500/20"
                             :class="isDragging ? 'bg-cyan-500/20 text-cyan-300 animate-bounce' : ''">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                        </div>
                        <div>
                            <div class="text-lg font-bold text-white mb-1 transition-colors" :class="isDragging ? 'text-cyan-300' : ''">
                                <span x-text="isDragging ? 'Drop files now!' : 'Drag files here or click to upload'"></span>
                            </div>
                            <div class="text-slate-500 text-sm">Valid formats: Logs, Screenshots, PDFs.</div>
                        </div>
                    </div>
                </div>

                <!-- Preview New Files -->
                <div class="mt-6 space-y-3" x-show="files.length > 0" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                    <h3 class="text-xs font-bold text-cyan-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                        Selected Files (<span x-text="files.length"></span>)
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <template x-for="(file, index) in files" :key="index">
                            <div class="flex items-center gap-4 p-3 bg-cyan-500/5 rounded-xl border border-cyan-500/20 backdrop-blur-sm"
                                 x-transition:enter="transition ease-out duration-500"
                                 x-transition:enter-start="opacity-0 scale-90 translate-x-4"
                                 x-transition:enter-end="opacity-100 scale-100 translate-x-0"
                                 :style="`transition-delay: ${index * 50}ms`">
                                
                                <div class="w-10 h-10 bg-cyan-500/10 rounded-lg flex items-center justify-center text-cyan-400 shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-bold text-white truncate" x-text="file.name"></div>
                                    <div class="text-xs text-cyan-300/70" x-text="(file.size/1024).toFixed(1) + ' KB'"></div>
                                </div>
                                <div class="text-cyan-500">
                                    <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Existing Artifacts List -->
            @if(!empty($report->summary_json['forensics']['artifacts']))
                <div class="mt-8 border-t border-white/5 pt-8">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Previously Attached</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($report->summary_json['forensics']['artifacts'] as $artifact)
                            @php
                                $extension = pathinfo($artifact['name'], PATHINFO_EXTENSION);
                                $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                $url = Storage::url($artifact['path']);
                            @endphp
                            <div class="flex items-center gap-4 p-4 bg-slate-800/50 rounded-xl border border-slate-700 hover:border-slate-600 transition-colors group">
                                <button type="button" 
                                        @click="$dispatch('open-preview', { src: '{{ $url }}', type: '{{ $isImage ? 'image' : ($extension === 'pdf' ? 'pdf' : 'other') }}' })"
                                        class="w-10 h-10 bg-slate-700 rounded-lg flex items-center justify-center text-slate-400 hover:opacity-80 transition-opacity overflow-hidden cursor-pointer">
                                     @if($isImage)
                                        <img src="{{ $url }}" class="w-full h-full object-cover" alt="Preview">
                                     @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                     @endif
                                </button>
                                <div class="flex-1 min-w-0">
                                    <button type="button" 
                                            @click="$dispatch('open-preview', { src: '{{ $url }}', type: '{{ $isImage ? 'image' : ($extension === 'pdf' ? 'pdf' : 'other') }}' })"
                                            class="block text-sm font-bold text-white truncate hover:text-cyan-400 text-left">
                                        {{ $artifact['name'] }}
                                    </button>
                                    <div class="text-xs text-slate-500">
                                        {{ number_format($artifact['size'] / 1024, 1) }} KB • {{ strtoupper($extension) }}
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                     <button type="button" 
                                             @click="$dispatch('open-preview', { src: '{{ $url }}', type: '{{ $isImage ? 'image' : ($extension === 'pdf' ? 'pdf' : 'other') }}' })"
                                             class="p-1.5 text-slate-400 hover:text-cyan-400 hover:bg-cyan-500/10 rounded transition-colors" title="View">
                                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                     </button>
                                     <a href="{{ $url }}" download class="p-1.5 text-slate-400 hover:text-green-400 hover:bg-green-500/10 rounded transition-colors" title="Download">
                                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                     </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

    </form>
    <!-- PREVIEW MODAL -->
    <div x-data="{ open: false, src: '', type: '' }" 
         @open-preview.window="open = true; src = $event.detail.src; type = $event.detail.type"
         @keydown.escape.window="open = false"
         x-show="open" 
         class="relative z-[100]" 
         style="display: none;">
        <div x-show="open" 
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" 
             class="fixed inset-0 bg-slate-900/90 backdrop-blur-sm transition-opacity"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="open" @click.away="open = false"
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     class="relative transform overflow-hidden rounded-lg bg-transparent shadow-xl transition-all sm:w-full sm:max-w-5xl">
                    <button type="button" @click="open = false" class="absolute right-0 top-0 pr-4 pt-4 z-50 rounded-md bg-black/50 text-slate-400 hover:text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                    <div class="bg-black/50 p-1 flex items-center justify-center min-h-[50vh]">
                        <template x-if="type === 'image'"><img :src="src" class="max-h-[85vh] w-auto object-contain rounded-md shadow-2xl"></template>
                        <template x-if="type === 'pdf'"><iframe :src="src" class="w-full h-[85vh] rounded-md shadow-2xl bg-white"></iframe></template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
