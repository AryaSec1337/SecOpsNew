@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen flex flex-col space-y-6 text-slate-300 font-sans" x-data="socCalculator()">
    
    <!-- STRATEGIC HEADER (DEFCON STYLE) -->
    <div class="relative bg-[#080808] border border-slate-800 rounded-sm overflow-hidden shadow-2xl">
        <!-- Background Grid -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.02)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.02)_1px,transparent_1px)] bg-[size:20px_20px] pointer-events-none"></div>
        
        <div class="relative z-10 p-6 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-gradient-to-br from-indigo-900 to-slate-900 border border-indigo-500/30 flex items-center justify-center relative group">
                    <div class="absolute inset-0 bg-indigo-500/20 blur-xl animate-pulse"></div>
                    <svg class="w-8 h-8 text-indigo-400 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
                <div>
                     <div class="flex items-center gap-2 mb-1">
                        <span class="text-[10px] bg-indigo-900/40 text-indigo-400 border border-indigo-900/50 px-2 py-0.5 font-mono uppercase tracking-widest">Enterprise v14.1</span>
                        <span class="text-[10px] text-slate-500 font-mono">STATUS: ACTIVE</span>
                    </div>
                    <h1 class="text-2xl font-black text-white tracking-tighter uppercase">Strategic Defense Matrix</h1>
                    <p class="text-xs text-slate-500 font-mono mt-1">MITRE ATT&CK® COVERAGE ANALYSIS SYSTEM</p>
                </div>
            </div>

            <!-- HUD Stats -->
            <div class="flex gap-8 border-l border-slate-800 pl-8">
                <!-- Total Tactics -->
                <div class="text-right">
                    <p class="text-[9px] text-slate-500 uppercase tracking-widest font-mono mb-1">Tactical Phases</p>
                    <p class="text-2xl font-black text-white">{{ count($matrix) }}</p>
                </div>
                <!-- Total Techniques -->
                <div class="text-right">
                    <p class="text-[9px] text-slate-500 uppercase tracking-widest font-mono mb-1">Total Vectors</p>
                    <p class="text-2xl font-black text-white" x-text="totalTechniques">0</p>
                </div>
                 <!-- Covered -->
                <div class="text-right">
                    <p class="text-[9px] text-emerald-500/70 uppercase tracking-widest font-mono mb-1">Active Defenses</p>
                    <p class="text-2xl font-black text-emerald-500" x-text="selectedCount">0</p>
                </div>
            </div>
        </div>
    </div>

    <!-- READINESS DASHBOARD -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Coverage Gauge -->
        <div class="col-span-1 bg-[#0b0b0b] border border-slate-800 rounded-sm p-6 relative overflow-hidden group">
             <div class="absolute right-0 top-0 w-32 h-32 bg-indigo-600/10 blur-3xl rounded-full translate-x-10 -translate-y-10 group-hover:bg-indigo-600/20 transition-all"></div>
             
             <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-800 pb-2 mb-4">Defense Coverage</h3>
             
             <div class="flex items-center gap-6">
                 <div class="relative w-24 h-24 flex items-center justify-center">
                     <svg class="w-full h-full transform -rotate-90">
                         <circle cx="48" cy="48" r="40" stroke="#1e293b" stroke-width="8" fill="transparent"></circle>
                         <circle cx="48" cy="48" r="40" stroke="#4f46e5" stroke-width="8" fill="transparent"
                                 :stroke-dasharray="251.2"
                                 :stroke-dashoffset="251.2 - (251.2 * coverageValid / 100)"
                                 class="transition-all duration-1000 ease-out"></circle>
                     </svg>
                     <div class="absolute inset-0 flex items-center justify-center flex-col">
                         <span class="text-2xl font-black text-white" x-text="coverageValid + '%'">0%</span>
                     </div>
                 </div>
                 <div class="flex-1">
                     <p class="text-xs text-slate-400 mb-2">Current defense density against known vector catalog.</p>
                     <div class="flex items-center gap-2">
                         <div class="h-2 w-2 rounded-full" :class="coverageValid > 50 ? 'bg-emerald-500' : (coverageValid > 20 ? 'bg-yellow-500' : 'bg-red-500')"></div>
                         <span class="text-sm font-bold text-white" x-text="coverageValid > 50 ? 'OPTIMAL' : (coverageValid > 20 ? 'MODERATE' : 'CRITICAL')">CRITICAL</span>
                     </div>
                 </div>
             </div>
        </div>

        <!-- Maturity Calculator -->
        <div class="col-span-1 lg:col-span-2 bg-[#0b0b0b] border border-slate-800 rounded-sm p-6 relative">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-800 pb-2 mb-4 flex justify-between">
                <span>SOC Maturity Assessment</span>
                <span class="text-indigo-500 font-mono" x-text="maturityLevel">INITIAL</span>
            </h3>

            <div class="relative pt-6 pb-2">
                <!-- Progress Track -->
                <div class="h-2 bg-slate-800 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-red-600 via-yellow-500 to-emerald-500 transition-all duration-700 w-0" :style="'width: ' + coverageValid + '%'"></div>
                </div>
                
                <!-- Milestones -->
                <div class="flex justify-between mt-2 text-[9px] font-mono text-slate-600 uppercase tracking-wider">
                    <div class="text-center w-1/5" :class="coverageValid >= 0 ? 'text-red-500' : ''">
                        <div class="mb-1">|</div> Initial
                    </div>
                    <div class="text-center w-1/5" :class="coverageValid >= 20 ? 'text-orange-500' : ''">
                        <div class="mb-1">|</div> Managed
                    </div>
                    <div class="text-center w-1/5" :class="coverageValid >= 40 ? 'text-yellow-500' : ''">
                        <div class="mb-1">|</div> Defined
                    </div>
                    <div class="text-center w-1/5" :class="coverageValid >= 60 ? 'text-cyan-500' : ''">
                        <div class="mb-1">|</div> Quantitative
                    </div>
                    <div class="text-center w-1/5" :class="coverageValid >= 80 ? 'text-emerald-500' : ''">
                        <div class="mb-1">|</div> Optimizing
                    </div>
                </div>
            </div>
            
            <p class="text-xs text-slate-500 mt-4 border-l-2 border-indigo-500 pl-3 italic">
                <span x-show="coverageValid < 20">"Defense posture is reactive. Prioritize implementing controls for Reconnaissance & Initial Access techniques."</span>
                <span x-show="coverageValid >= 20 && coverageValid < 40">"Foundational controls established. Begin expanding coverage to Lateral Movement & Exfiltration."</span>
                <span x-show="coverageValid >= 40 && coverageValid < 60">"Proactive defense capabilities detected. Focus on Detection Engineering and Response automation."</span>
                <span x-show="coverageValid >= 60 && coverageValid < 80">"High maturity achieved. Optimization and Threat Hunting should be primary focus."</span>
                <span x-show="coverageValid >= 80">"State-of-the-art defense matrix. Maintain vigilance and adapt to emerging TTPs."</span>
            </p>
        </div>
    </div>

    <!-- THE MATRIX GRID -->
    @if(empty($matrix) || count($matrix) == 0)
        <!-- Error State -->
        <div class="p-12 text-center bg-slate-900/50 rounded-sm border border-red-900/30">
            <h3 class="text-red-500 font-bold font-mono text-lg">MATRIX_LOAD_FAILURE</h3>
            <p class="text-slate-500 text-sm mt-2">Unable to retrieve tactical data from MITRE CTI feed.</p>
        </div>
    @else
        <div class="overflow-x-auto pb-12" x-init="initMatrix({{ count(collect($matrix)->pluck('techniques')->flatten(1)) }})">
            <div class="inline-flex gap-px bg-slate-800 border border-slate-800 min-w-full">
                
                @foreach($matrix as $column)
                <div class="w-64 flex-shrink-0 flex flex-col gap-px">
                     <!-- Tactic Column Header -->
                    <div class="bg-[#111] p-3 text-center border-b-2 border-slate-700 h-24 flex flex-col justify-center items-center relative group">
                        <div class="text-[10px] font-mono text-slate-500 uppercase mb-1">{{ $column['tactic']['external_id'] ?? 'TA0000' }}</div>
                        <h3 class="text-xs font-bold text-white uppercase break-words w-full px-2">{{ $column['tactic']['name'] }}</h3>
                        <div class="text-[9px] text-slate-600 mt-1">{{ count($column['techniques']) }} items</div>
                        
                         <!-- Header Tooltip -->
                         <div class="absolute top-full left-0 w-full bg-slate-900 p-2 text-[10px] text-slate-400 text-left border border-slate-700 z-50 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                            {{ $column['tactic']['description'] }}
                        </div>
                    </div>

                    <!-- Techniques Cells -->
                    <div class="flex flex-col gap-px bg-slate-800">
                        @foreach($column['techniques'] as $tech)
                        <div 
                            @click="toggle('{{ $tech['id'] }}')"
                            :class="isSelected('{{ $tech['id'] }}') 
                                ? 'bg-indigo-600/90 text-white shadow-[inset_0_0_10px_rgba(255,255,255,0.2)]' 
                                : 'bg-[#0a0a0a] text-slate-500 hover:text-slate-300 hover:bg-[#151515]'"
                            class="p-2 text-[10px] h-16 cursor-pointer transition-all relative group flex flex-col justify-between select-none"
                        >
                            <div class="flex justify-between w-full">
                                <span class="font-bold leading-tight line-clamp-2 w-[85%]">{{ $tech['name'] }}</span>
                                <span x-show="isSelected('{{ $tech['id'] }}')" class="text-indigo-200">●</span>
                            </div>
                            <!-- ID Footer -->
                            <div class="text-[9px] font-mono opacity-50">{{ $tech['id'] }}</div>

                            <!-- Hover Details -->
                            <div class="absolute left-full top-0 ml-2 w-64 bg-slate-900 border border-slate-700 p-3 z-50 rounded shadow-2xl skew-x-0 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none hidden md:block">
                                <h4 class="text-white font-bold text-xs mb-1">{{ $tech['name'] }}</h4>
                                <p class="text-[10px] text-slate-400 leading-relaxed">{{ Str::limit($tech['description'], 150) }}</p>
                                <div class="mt-2 pt-2 border-t border-slate-800 flex justify-between text-[9px] font-mono text-indigo-400">
                                    <span>{{ $tech['platforms'][0] ?? 'Generic' }}</span>
                                    <span>MITRE ATT&CK</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach

            </div>
        </div>
    @endif

</div>

<!-- Alpine Logic -->
<script>
    function socCalculator() {
        return {
            selected: [],
            totalTechniques: 0,
            
            initMatrix(total) {
                this.totalTechniques = total;
                const saved = localStorage.getItem('mitre_selected_v2');
                if (saved) {
                    this.selected = JSON.parse(saved);
                }
            },

            toggle(id) {
                if (this.selected.includes(id)) {
                    this.selected = this.selected.filter(item => item !== id);
                } else {
                    this.selected.push(id);
                }
                localStorage.setItem('mitre_selected_v2', JSON.stringify(this.selected));
            },

            isSelected(id) {
                return this.selected.includes(id);
            },

            get selectedCount() {
                return this.selected.length;
            },

            get coverageValid() {
                if (this.totalTechniques === 0) return 0;
                return Math.round((this.selected.length / this.totalTechniques) * 100);
            },

            get maturityLevel() {
                const cov = this.coverageValid;
                // Official CMMI-inspired Levels
                if (cov < 10) return 'LEVEL 1: INITIAL';
                if (cov < 30) return 'LEVEL 2: MANAGED';
                if (cov < 50) return 'LEVEL 3: DEFINED';
                if (cov < 70) return 'LEVEL 4: QUANTITATIVE';
                return 'LEVEL 5: OPTIMIZING';
            }
        }
    }
</script>
@endsection
