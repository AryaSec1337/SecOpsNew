@extends('layouts.dashboard')

@section('content')
<div class="space-y-6" x-data="emailAnalyzer()">
    
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Email Header Analyzer</h1>
            <p class="text-slate-400 text-sm mt-1">Analyze raw email headers to trace path, delays, and security authentication.</p>
        </div>
        <div class="flex gap-2">
            <button @click="openHistoryModal = true; fetchHistory()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg text-sm font-medium transition-colors border border-slate-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                History
            </button>
            <button @click="openRulesModal = true; fetchRules()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg text-sm font-medium transition-colors border border-slate-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                Rules
            </button>
            <button @click="reset()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg text-sm font-medium transition-colors border border-slate-700">
                Clear
            </button>
            <button @click="analyze()" :disabled="loading" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-sm font-bold shadow-lg shadow-blue-500/20 transition-all flex items-center gap-2">
                <svg x-show="loading" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span x-text="loading ? 'Analyzing...' : 'Analyze Header'"></span>
            </button>
        </div>
    </div>

    <!-- History Modal -->
    <div x-show="openHistoryModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
            <div x-show="openHistoryModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/90 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div x-show="openHistoryModal" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 class="inline-block align-bottom bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-slate-700/50">
                
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold text-white">Analysis History</h3>
                        <button @click="openHistoryModal = false" class="text-slate-400 hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="overflow-y-auto max-h-[500px] custom-scrollbar">
                        <table class="w-full text-left">
                            <thead class="bg-slate-800/50 text-slate-400 uppercase font-bold text-[10px] sticky top-0 backdrop-blur-md">
                                <tr>
                                    <th class="px-4 py-3">Date</th>
                                    <th class="px-4 py-3">Subject / Sender</th>
                                    <th class="px-4 py-3">Risk Score</th>
                                    <th class="px-4 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700/50">
                                <template x-for="item in history" :key="item.id">
                                    <tr class="hover:bg-slate-800/30 transition-colors">
                                        <td class="px-4 py-3 text-xs text-slate-400 whitespace-nowrap" x-text="item.date"></td>
                                        <td class="px-4 py-3">
                                            <div class="text-sm font-bold text-white truncate max-w-xs" x-text="item.subject"></div>
                                            <div class="text-xs text-slate-500 truncate max-w-xs" x-text="item.sender"></div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wide border"
                                                  :class="{
                                                      'border-red-500/30 bg-red-500/10 text-red-400': item.level === 'High',
                                                      'border-amber-500/30 bg-amber-500/10 text-amber-400': item.level === 'Medium',
                                                      'border-blue-500/30 bg-blue-500/10 text-blue-400': item.level === 'Low'
                                                  }" x-text="item.score + ' - ' + item.level"></span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <button @click="loadHistory(item.id)" class="px-3 py-1 bg-blue-600 hover:bg-blue-500 text-white text-xs rounded transition-colors">Load</button>
                                                <button @click="deleteHistory(item.id)" class="p-1 text-slate-500 hover:text-red-400 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="!history.length">
                                    <td colspan="4" class="px-4 py-8 text-center text-slate-500">No history found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Rules Modal -->
    <div x-show="openRulesModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
            <div x-show="openRulesModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/90 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div x-show="openRulesModal" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 class="inline-block align-bottom bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-slate-700/50">
                
                <div class="flex h-[600px]">
                    <!-- Sidebar: Add Rule -->
                    <div class="w-1/3 bg-slate-800/50 p-6 border-r border-slate-700/50 flex flex-col">
                        <h3 class="text-lg font-bold text-white mb-1">New Rule</h3>
                        <p class="text-xs text-slate-400 mb-6">Define extraction patterns using Regex.</p>
                        
                        <div class="space-y-4 flex-1">
                            <div>
                                <label class="block text-xs font-bold text-slate-300 mb-1.5 uppercase tracking-wider">Rule Name</label>
                                <input x-model="newRule.name" type="text" class="w-full bg-slate-900/50 text-sm border-slate-700 rounded-lg text-white px-3 py-2.5 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors placeholder-slate-600" placeholder="e.g. CEO Fraud Detection">
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-slate-300 mb-1.5 uppercase tracking-wider">Regex Pattern</label>
                                <div class="relative">
                                    <input x-model="newRule.pattern" type="text" class="w-full bg-slate-900/50 text-sm border-slate-700 rounded-lg text-white pl-8 pr-3 py-2.5 font-mono text-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors placeholder-slate-600" placeholder="/pattern/i">
                                    <span class="absolute left-3 top-2.5 text-slate-500 font-mono text-xs">/</span>
                                </div>
                                <p class="text-[10px] text-slate-500 mt-1.5 leading-relaxed">
                                    Use PCRE compatible regex. Example: <code class="bg-slate-800 px-1 rounded text-slate-300">/urgent|verify/i</code>
                                </p>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-slate-300 mb-1.5 uppercase tracking-wider">Risk Score (0-100)</label>
                                <div class="flex items-center gap-3">
                                    <input x-model="newRule.score" type="range" min="0" max="100" class="w-full h-2 bg-slate-700 rounded-lg appearance-none cursor-pointer accent-blue-500">
                                    <span class="text-sm font-bold text-blue-400 w-8" x-text="newRule.score"></span>
                                </div>
                            </div>
                        </div>

                        <button @click="saveRule()" class="w-full py-3 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-sm font-bold transition-all shadow-lg shadow-blue-500/20 flex items-center justify-center gap-2 mt-4 group">
                            <svg class="w-4 h-4 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Add Detection Rule
                        </button>
                    </div>

                    <!-- Main Content: Rules List -->
                    <div class="w-2/3 bg-slate-900 p-6 flex flex-col">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-lg font-bold text-white">Active Rules</h3>
                                <p class="text-xs text-slate-400"><span x-text="rules.length"></span> custom heuristics active</p>
                            </div>
                            <div class="bg-slate-800/50 rounded-lg p-1 flex">
                                <button class="px-3 py-1 rounded text-xs font-medium bg-slate-700 text-white shadow-sm">All</button>
                                <button class="px-3 py-1 rounded text-xs font-medium text-slate-400 hover:text-white transition-colors">High Risk</button>
                            </div>
                        </div>

                        <div class="flex-1 overflow-y-auto pr-2 space-y-3 custom-scrollbar">
                            <template x-for="rule in rules" :key="rule.id">
                                <div class="group flex items-center justify-between p-4 bg-slate-800/30 hover:bg-slate-800/80 rounded-xl border border-slate-700/50 hover:border-slate-600 transition-all duration-200">
                                    <div class="flex-1 min-w-0 pr-4">
                                        <div class="flex items-center gap-2 mb-1">
                                            <h4 class="text-sm font-bold text-white truncate" x-text="rule.name"></h4>
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide border"
                                                  :class="{
                                                      'border-red-500/30 bg-red-500/10 text-red-400': rule.score >= 50,
                                                      'border-amber-500/30 bg-amber-500/10 text-amber-400': rule.score < 50 && rule.score >= 20,
                                                      'border-blue-500/30 bg-blue-500/10 text-blue-400': rule.score < 20
                                                  }" x-text="rule.score + ' PTS'"></span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <code class="text-xs text-slate-400 font-mono bg-slate-900/50 px-1.5 py-0.5 rounded" x-text="rule.pattern"></code>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button @click="deleteRule(rule.id)" class="p-2 text-slate-500 hover:text-red-400 hover:bg-red-400/10 rounded-lg transition-colors" title="Delete Rule">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                            
                            <div x-show="!rules.length" class="flex flex-col items-center justify-center h-48 text-slate-500">
                                <svg class="w-12 h-12 mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                <p class="text-sm">No detection rules found</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Footer area if needed, e.g. close button specifically for mobile but here accessible via X top right or outside click if implemented -->
                <button @click="openRulesModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Input Section -->
    <div class="glass-panel rounded-xl p-1 border-slate-700/50">
        <textarea 
            x-model="headers" 
            placeholder="Paste raw email headers here..." 
            class="w-full h-48 bg-slate-900/50 text-xs font-mono text-slate-300 p-4 rounded-lg border-0 focus:ring-1 focus:ring-blue-500/50 placeholder-slate-600 resize-y focus:bg-slate-900 transition-colors"
        ></textarea>
    </div>

    <!-- Results Section -->
    <div x-show="result" x-transition.opacity class="space-y-6">
        
        <!-- Risk Score Assessment -->
        <div class="glass-panel rounded-xl p-6 relative overflow-hidden">
             <!-- Background Glow based on logic -->
             <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full blur-3xl opacity-20 pointer-events-none"
                  :class="{
                      'bg-emerald-500': result?.risk?.level === 'Low',
                      'bg-amber-500': result?.risk?.level === 'Medium',
                      'bg-red-500': result?.risk?.level === 'High'
                  }"></div>

             <div class="flex flex-col md:flex-row gap-6 items-center relative z-10">
                 <!-- Score Circle -->
                 <div class="flex-shrink-0 text-center">
                     <div class="relative w-32 h-32 flex items-center justify-center rounded-full border-4 shadow-xl backdrop-blur-sm"
                          :class="{
                              'border-emerald-500/50 bg-emerald-500/10 text-emerald-400': result?.risk?.level === 'Low',
                              'border-amber-500/50 bg-amber-500/10 text-amber-400': result?.risk?.level === 'Medium',
                              'border-red-500/50 bg-red-500/10 text-red-400': result?.risk?.level === 'High'
                          }">
                         <div>
                             <div class="text-4xl font-black tracking-tighter" x-text="result?.risk?.score ?? 0"></div>
                             <div class="text-[10px] font-bold uppercase tracking-widest opacity-80" x-text="result?.risk?.level ?? 'Low'">Risk</div>
                         </div>
                     </div>
                 </div>
                 
                 <!-- Analysis & Indicators -->
                 <div class="flex-1 w-full">
                     <h3 class="text-lg font-bold text-white mb-2 ml-1 flex items-center gap-2">
                         <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                         Phishing Risk Analysis
                     </h3>
                     
                     <div class="space-y-2">
                         <template x-for="indicator in result?.risk?.indicators" :key="indicator.msg">
                             <div class="flex items-start gap-3 p-3 rounded-lg border bg-slate-900/50 backdrop-blur-sm transition-all hover:bg-slate-900/80"
                                  :class="{
                                      'border-red-500/30 text-red-300': indicator.severity === 'high',
                                      'border-amber-500/30 text-amber-300': indicator.severity === 'medium',
                                      'border-blue-500/30 text-blue-300': indicator.severity === 'low'
                                  }">
                                 <svg x-show="indicator.severity === 'high'" class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                 <svg x-show="indicator.severity === 'medium'" class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                 <svg x-show="indicator.severity === 'low'" class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                 
                                 <div>
                                     <div class="text-sm font-medium" x-text="indicator.msg"></div>
                                     <div x-show="indicator.matches" class="mt-1.5 inline-block">
                                         <code class="text-[10px] font-mono text-amber-200 bg-amber-500/10 px-2 py-1 rounded border border-amber-500/20" x-text="indicator.matches"></code>
                                     </div>
                                 </div>
                             </div>
                         </template>
                         
                         <!-- Safe State -->
                         <div x-show="!result?.risk?.indicators?.length" class="p-4 rounded-lg border border-emerald-500/30 bg-emerald-500/5 text-emerald-400 flex items-center gap-3">
                             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                             <div>
                                 <div class="font-bold">No High Risks Detected</div>
                                 <div class="text-xs opacity-80">This email header looks mostly clean based on heuristic analysis.</div>
                             </div>
                         </div>
                     </div>
                 </div>
             </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- Subject -->
            <div class="glass-panel rounded-xl p-4 border-l-4 border-blue-500">
                <div class="text-[10px] uppercase font-bold text-slate-500 tracking-wider mb-1">Subject</div>
                <div class="text-sm font-medium text-white truncate" :title="result?.summary?.subject" x-text="result?.summary?.subject"></div>
            </div>

            <!-- From -->
            <div class="glass-panel rounded-xl p-4 border-l-4 border-purple-500">
                <div class="text-[10px] uppercase font-bold text-slate-500 tracking-wider mb-1">From</div>
                <div class="text-sm font-medium text-white truncate" :title="result?.summary?.from" x-text="result?.summary?.from"></div>
            </div>
            
            <!-- To -->
            <div class="glass-panel rounded-xl p-4 border-l-4 border-emerald-500">
                <div class="text-[10px] uppercase font-bold text-slate-500 tracking-wider mb-1">To</div>
                <div class="text-sm font-medium text-white truncate" :title="result?.summary?.to" x-text="result?.summary?.to"></div>
            </div>

            <!-- Date -->
            <div class="glass-panel rounded-xl p-4 border-l-4 border-amber-500">
                <div class="text-[10px] uppercase font-bold text-slate-500 tracking-wider mb-1">Date</div>
                <div class="text-sm font-medium text-white truncate" :title="result?.summary?.date" x-text="result?.summary?.date"></div>
            </div>
        </div>

        <!-- Two Column Layout: Security & Details -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left: Security Checks -->
            <div class="space-y-6">
                
                <!-- Authentication Status -->
                <div class="glass-panel rounded-xl overflow-hidden">
                     <div class="bg-slate-900/50 px-4 py-3 border-b border-white/5 flex items-center justify-between">
                        <h3 class="font-bold text-sm text-white">Authentication</h3>
                    </div>
                    <div class="p-4 space-y-4">
                        <!-- SPF -->
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs font-bold text-slate-400">SPF</span>
                                <span x-show="!getAuthStatus(result?.security?.auth_results, 'spf')" class="text-[10px] bg-slate-800 text-slate-400 px-2 py-0.5 rounded">Unknown</span>
                                <span x-show="getAuthStatus(result?.security?.auth_results, 'spf') === 'pass'" class="text-[10px] bg-emerald-500/20 text-emerald-400 px-2 py-0.5 rounded uppercase font-bold">PASS</span>
                                <span x-show="getAuthStatus(result?.security?.auth_results, 'spf') === 'fail'" class="text-[10px] bg-red-500/20 text-red-400 px-2 py-0.5 rounded uppercase font-bold">FAIL</span>
                            </div>
                           
                        </div>
                        
                        <!-- DKIM -->
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs font-bold text-slate-400">DKIM</span>
                               <span x-show="!getAuthStatus(result?.security?.auth_results, 'dkim')" class="text-[10px] bg-slate-800 text-slate-400 px-2 py-0.5 rounded">Unknown</span>
                                <span x-show="getAuthStatus(result?.security?.auth_results, 'dkim') === 'pass'" class="text-[10px] bg-emerald-500/20 text-emerald-400 px-2 py-0.5 rounded uppercase font-bold">PASS</span>
                                <span x-show="getAuthStatus(result?.security?.auth_results, 'dkim') === 'fail'" class="text-[10px] bg-red-500/20 text-red-400 px-2 py-0.5 rounded uppercase font-bold">FAIL</span>
                            </div>
                        </div>

                         <!-- DMARC -->
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs font-bold text-slate-400">DMARC</span>
                                <span x-show="!getAuthStatus(result?.security?.auth_results, 'dmarc')" class="text-[10px] bg-slate-800 text-slate-400 px-2 py-0.5 rounded">Unknown</span>
                                <span x-show="getAuthStatus(result?.security?.auth_results, 'dmarc') === 'pass'" class="text-[10px] bg-emerald-500/20 text-emerald-400 px-2 py-0.5 rounded uppercase font-bold">PASS</span>
                                <span x-show="getAuthStatus(result?.security?.auth_results, 'dmarc') === 'fail'" class="text-[10px] bg-red-500/20 text-red-400 px-2 py-0.5 rounded uppercase font-bold">FAIL</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Raw Auth Results -->
                <div class="glass-panel rounded-xl overflow-hidden">
                    <div class="bg-slate-900/50 px-4 py-3 border-b border-white/5">
                        <h3 class="font-bold text-sm text-white">Authentication-Results Header</h3>
                    </div>
                    <div class="p-4">
                        <code class="text-[10px] text-slate-300 break-words font-mono" x-text="result?.security?.auth_results || 'Not Found'"></code>
                    </div>
                </div>

                 <!-- Message Info -->
                <div class="glass-panel rounded-xl overflow-hidden">
                    <div class="bg-slate-900/50 px-4 py-3 border-b border-white/5">
                        <h3 class="font-bold text-sm text-white">Message Info</h3>
                    </div>
                    <div class="p-4 space-y-3">
                         <div>
                            <div class="text-[10px] text-slate-500 uppercase">Message-ID</div>
                            <div class="text-xs text-white break-all select-all font-mono" x-text="result?.summary?.message_id"></div>
                        </div>
                         <div>
                            <div class="text-[10px] text-slate-500 uppercase">Return-Path</div>
                            <div class="text-xs text-white break-all select-all font-mono" x-text="result?.summary?.return_path"></div>
                        </div>
                         <div>
                            <div class="text-[10px] text-slate-500 uppercase">X-Mailer / User-Agent</div>
                            <div class="text-xs text-white break-all font-mono" x-text="result?.summary?.x_mailer"></div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right: Hops Visualization -->
            <div class="lg:col-span-2 space-y-6">
                <div class="glass-panel rounded-xl overflow-hidden">
                     <div class="bg-slate-900/50 px-4 py-3 border-b border-white/5 flex justify-between items-center">
                        <h3 class="font-bold text-sm text-white">Mail Delivery Path (Hops)</h3>
                        <span class="text-xs text-slate-500">Ordered: Sender &rarr; Recipient</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-900/50 text-slate-400 uppercase font-bold text-[10px]">
                                <tr>
                                    <th class="px-4 py-3">#</th>
                                    <th class="px-4 py-3">Sending Host (From)</th>
                                    <th class="px-4 py-3">Receiving Host (By)</th>
                                    <th class="px-4 py-3 text-right">Time Received</th>
                                    <th class="px-4 py-3 text-right">Delay</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5 text-slate-300">
                                <template x-for="hop in result?.hops" :key="hop.number">
                                    <tr class="hover:bg-white/5 transition-colors">
                                        <td class="px-4 py-3 font-mono text-slate-500" x-text="hop.number"></td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-white" x-text="hop.from"></div>
                                            <div x-show="hop.ip" class="text-[10px] text-blue-400 font-mono mt-0.5" x-text="hop.ip"></div>
                                        </td>
                                        <td class="px-4 py-3 text-slate-400" x-text="hop.by"></td>
                                        <td class="px-4 py-3 text-right whitespace-nowrap font-mono text-slate-400" x-text="formatDate(hop.time)"></td>
                                        <td class="px-4 py-3 text-right">
                                            <span 
                                                class="px-2 py-0.5 rounded text-[10px] font-bold font-mono"
                                                :class="getDelayClass(hop.delay)"
                                                x-text="hop.display_delay"
                                            ></span>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="!result?.hops?.length">
                                    <td colspan="5" class="px-4 py-8 text-center text-slate-500 italic">No hops detected or unable to parse received headers.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('emailAnalyzer', () => ({
            headers: '',
            loading: false,
            result: null,
            openRulesModal: false,
            openHistoryModal: false,
            rules: [],
            history: [],
            newRule: { name: '', pattern: '', score: 20 },

            async init() {
                // Pre-load rules if needed, or lazy load
            },

            async fetchHistory() {
                const res = await fetch('{{ route("investigation.email-analyzer.history.index") }}');
                this.history = await res.json();
            },

            async loadHistory(id) {
                // First get the item from local list to be quick
                const item = this.history.find(h => h.id === id);
                if(item && item.results) {
                    this.result = item.results;
                    this.openHistoryModal = false;
                } else {
                    // Fetch full if needed
                }
            },

            async deleteHistory(id) {
                if(!confirm('Delete this history item?')) return;
                await fetch(`{{ url('/investigation/email-analyzer/history') }}/${id}`, {
                     method: 'DELETE',
                     headers: {
                         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                     }
                });
                this.fetchHistory();
            },

            async fetchRules() {
                const res = await fetch('{{ route("investigation.email-analyzer.rules.index") }}');
                this.rules = await res.json();
            },

            async saveRule() {
                if(!this.newRule.name || !this.newRule.pattern) return;

                try {
                    const response = await fetch('{{ route("investigation.email-analyzer.rules.store") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(this.newRule)
                    });
                    
                    if (!response.ok) throw new Error('Failed');
                    
                    this.newRule = { name: '', pattern: '', score: 20 };
                    this.fetchRules();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 1500,
                        background: '#1e293b',
                        color: '#fff'
                    });

                } catch (e) {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Invalid Regex or Server Error' });
                }
            },

            async deleteRule(id) {
                if(!confirm('Delete this rule?')) return;
                
                await fetch(`{{ url('/investigation/email-analyzer/rules') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                this.fetchRules();
            },

            async analyze() {
                if (!this.headers.trim()) return;
                
                this.loading = true;
                this.result = null;

                try {
                    const response = await fetch('{{ route("investigation.email-analyzer.analyze") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ headers: this.headers })
                    });
                    
                    if (!response.ok) throw new Error('Analysis failed');
                    
                    this.result = await response.json();
                } catch (error) {
                    console.error(error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to analyze email headers.',
                        background: '#0f172a',
                        color: '#fff'
                    });
                } finally {
                    this.loading = false;
                }
            },

            reset() {
                this.headers = '';
                this.result = null;
            },

            getDelayClass(seconds) {
                if (seconds < 0) return 'text-slate-500'; 
                if (seconds < 2) return 'bg-emerald-500/10 text-emerald-400';
                if (seconds < 10) return 'bg-amber-500/10 text-amber-400';
                return 'bg-red-500/10 text-red-400';
            },
            
            formatDate(dateStr) {
                if(!dateStr) return '-';
                // Try parse
                try {
                    const d = new Date(dateStr);
                    return d.toLocaleString();
                } catch(e) { return dateStr; }
            },

            getAuthStatus(authHeader, type) {
                if (!authHeader) return null;
                // Simple regex check for type=pass within the string
                // Note: This is an approximation as auth results are complex
                const regex = new RegExp(type + '\\s*=\\s*([a-z0-9]+)', 'i');
                const match = authHeader.match(regex);
                return match ? match[1].toLowerCase() : null;
            }
        }));
    });
</script>
@endsection
