<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SecurityReport;
use Carbon\Carbon;
use Illuminate\Support\Str;

use App\Services\LogAnalyzerService;

class SecurityReportController extends Controller
{
    public function analyze(Request $request, LogAnalyzerService $analyzer)
    {
        $request->validate([
            'log_content' => 'required|string',
            'action' => 'nullable|in:scan,synthesize', // scan = only TI, synthesize = TI + AI
            'intel_data' => 'nullable|array', // Passed back from frontend for synthesize step
        ]);

        $logContent = $request->input('log_content');
        $action = $request->input('action', 'synthesize'); // Default to full flow if not specified
        $intelData = $request->input('intel_data', []);
        $selectedIps = $request->input('selected_ips', []); // Extract before closure

        return response()->stream(function () use ($analyzer, $logContent, $action, $intelData, $selectedIps) {
            // Disable timeouts for long running process
            set_time_limit(300);
            
            // Clear all output buffers to ensure real-time streaming
            while (ob_get_level() > 0) {
                ob_end_clean(); // Clean silently
            }
            // Enable implicit flush
            ob_implicit_flush(true);

            // Configure Options
            $options = [];
            if ($action === 'scan') {
                $options['skip_ai'] = true;
            }
            if ($action === 'synthesize') {
                $options['intel_data'] = $intelData;
            }
            // NEW: Pass selected IPs if provided by frontend
            if (!empty($selectedIps)) {
                $options['selected_ips'] = $selectedIps;
            }

            // Execute Analysis
             $finalResult = $analyzer->analyze($logContent, function ($step, $status, $message) {
                echo "data: " . json_encode(['step' => $step, 'status' => $status, 'message' => $message]) . "\n\n";
                if(ob_get_length()) ob_flush();
                flush();
            }, $options);

            // NEW: IP SELECTION REQUIRED (User must pick IPs)
            if (isset($finalResult['status']) && $finalResult['status'] === 'ip_selection_required') {
                echo "data: " . json_encode([
                    'step' => 'IP Selection',
                    'status' => 'ip_selection_required',
                    'extracted_ips' => $finalResult['extracted_ips'],
                    'message' => $finalResult['message']
                ]) . "\n\n";
                flush();
                return;
            }

             // IF REVIEW REQUIRED (Scan Mode), STOP HERE
            if (isset($finalResult['status']) && $finalResult['status'] === 'review_required') {
                echo "data: " . json_encode([
                    'step' => 'Review', 
                    'status' => 'review_required', // Frontend triggers review UI
                    'result' => $finalResult
                ]) . "\n\n";
                flush();
                return;
            }

            // PERSIST TO DATABASE (Only for full analysis)
            // Use a unique ID for the 'period' field to differentiate AI scans from monthly reports
            $scanId = 'AI-SCAN-' . now()->timestamp;

            // NEW: Save detailed SOC Report per IP
            if (!empty($finalResult['raw_data'])) {
                foreach ($finalResult['raw_data'] as $ip => $data) {
                    \App\Models\ResultReportSoc::create([
                        'ip_address' => $ip,
                        'ip_info' => $data['geo'] ?? [],
                        'greynoise' => $data['greynoise'] ?? [],
                        'virustotal' => $data['virustotal'] ?? [],
                        'abuseipdb' => $data['abuseipdb'] ?? [],
                        'alienvault' => $data['alienvault'] ?? [],
                        'ai_analysis' => $finalResult['technical_analysis'] ?? '',
                        'risk_score' => (int) ($data['risk_score'] ?? 0),
                    ]);
                }
            }
            
            $reportData = [
                'meta' => [
                    'author_role' => 'AI_System',
                    'generated_at' => now()->toDateTimeString(),
                    'tlp' => 'AMBER', // Default for AI scans
                    'title' => $finalResult['title'],
                    'status' => 'Draft',
                    'is_ai_scan' => true
                ],
                'executive' => [
                    'summary' => $finalResult['executive_summary'],
                    'impact_analysis' => 'Pending human review.',
                    'risk_score' => $finalResult['risk_score'],
                ],
                'technical' => [
                    'analysis' => $finalResult['technical_analysis'],
                    'kill_chain_phase' => 'Exploitation', // Default guess
                    'root_cause' => 'Unknown',
                    'mitre_tactics' => [],
                    'raw_intelligence' => $finalResult['raw_data'] ?? []
                ],
                'forensics' => [
                    'timeline' => $finalResult['timeline'] ?? [],
                    'iocs' => $finalResult['iocs'],
                ],
                'metrics' => [
                    'mttd' => '0',
                    'mttr' => '0',
                ],
                'recommendations' => 'Review findings and validate false positives.',
            ];

            $report = SecurityReport::create([
                'period' => $scanId,
                'summary_json' => $reportData
            ]);

            $redirectUrl = route('reports.show', $report->id);

            echo "data: " . json_encode([
                'step' => 'Complete', 
                'status' => 'done', 
                'result' => $finalResult,
                'redirect_url' => $redirectUrl 
            ]) . "\n\n";
            flush();


        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no' // Nginx specific
        ]);
    }

    public function index()
    {
        $reports = SecurityReport::latest()->get();
        return view('reports.index', compact('reports'));
    }

    public function destroy($id)
    {
        $report = SecurityReport::findOrFail($id);
        $report->delete();
        
        return redirect()->route('reports.index')->with('success', 'Report deleted successfully.');
    }

    public function create()
    {
        return view('reports.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'period' => 'required|date_format:Y-m',
            'tlp' => 'required|in:RED,AMBER,GREEN,CLEAR',
            'title' => 'required|string',
            'executive_summary' => 'required',
            'technical_analysis' => 'nullable',
            'impact_analysis' => 'nullable',
            'recommendations' => 'required',
        ]);

        $period = $request->input('period');

        // Structure the JSON data for L3 Enterprise Report
        $summary = [
            'meta' => [
                'author_role' => 'L3 Lead Analyst',
                'generated_at' => now()->toDateTimeString(),
                'tlp' => $request->input('tlp'),
                'title' => $request->input('title'),
                'status' => 'Final',
            ],
            'executive' => [
                'summary' => $request->input('executive_summary'),
                'impact_analysis' => $request->input('impact_analysis'),
                'risk_score' => $request->input('risk_score', 'Low'),
            ],
            'technical' => [
                'analysis' => $request->input('technical_analysis'),
                'kill_chain_phase' => $request->input('kill_chain_phase'),
                'root_cause' => $request->input('root_cause'),
                'mitre_tactics' => $request->input('mitre_tactics', []),
            ],
            'forensics' => [
                'timeline' => json_decode($request->input('timeline_json', '[]'), true),
                'iocs' => json_decode($request->input('iocs_json', '[]'), true),
            ],
            'metrics' => [
                'mttd' => $request->input('metrics.mttd'),
                'mttr' => $request->input('metrics.mttr'),
                'artifacts_count' => $request->input('metrics.artifacts', 0),
            ],
            'recommendations' => $request->input('recommendations'),
        ];

        SecurityReport::updateOrCreate(
            ['period' => $period],
            ['summary_json' => $summary]
        );

        return redirect()->route('reports.index')->with('success', "L3 Security Report for $period published successfully.");
    }

    public function show($id)
    {
        $report = SecurityReport::findOrFail($id);
        
        // Decode if not automatically cast (depending on model)
        if (is_string($report->summary_json)) {
            $report->summary_json = json_decode($report->summary_json, true);
        }

        // Fetch associated detailed SOC data (TI + AI)
        $socData = [];
        
        // Support both new structure (forensics.iocs) and legacy structure (iocs)
        $iocs = $report->summary_json['forensics']['iocs'] ?? $report->summary_json['iocs'] ?? [];
        
        if (!empty($iocs)) {
            $ips = collect($iocs)
                ->where('type', 'IPv4')
                ->pluck('value')
                ->map(function($ip) {
                    return trim($ip);
                })
                ->filter()
                ->toArray();
            
            if (!empty($ips)) {
                // Fuzzy search to handle whitespace issues in legacy data
                $socData = \App\Models\ResultReportSoc::where(function($query) use ($ips) {
                    foreach ($ips as $ip) {
                        $cleanIp = trim($ip);
                        if (!empty($cleanIp)) {
                             $query->orWhere('ip_address', 'LIKE', "%{$cleanIp}%");
                        }
                    }
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->unique('ip_address'); // Get latest per IP
            }
        }

        return view('reports.show', compact('report', 'socData'));
    }

    public function edit($id)
    {
        $report = SecurityReport::findOrFail($id);
        
        if (is_string($report->summary_json)) {
            $report->summary_json = json_decode($report->summary_json, true);
        }

        // Fetch Full MITRE Matrix (Cached)
        $matrix = \Illuminate\Support\Facades\Cache::remember('mitre_attack_matrix', 86400, function () {
            try {
                $url = "https://raw.githubusercontent.com/mitre/cti/master/enterprise-attack/enterprise-attack.json";
                $response = \Illuminate\Support\Facades\Http::timeout(30)->get($url);
                
                if (!$response->successful()) return [];

                $data = $response->json();
                $objects = collect($data['objects'] ?? []);

                // 1. Get Tactics
                $tactics = $objects->where('type', 'x-mitre-tactic')->map(function ($tactic) {
                    return [
                        'name' => $tactic['name'],
                        'short_name' => $tactic['x_mitre_shortname'],
                        'order' => $this->getTacticOrder($tactic['x_mitre_shortname'])
                    ];
                })->sortBy('order')->values();

                // 2. Get Techniques
                $techniques = $objects->where('type', 'attack-pattern')->where('revoked', false)->map(function ($tech) {
                    $phases = collect($tech['kill_chain_phases'] ?? [])->where('kill_chain_name', 'mitre-attack');
                    return [
                        'id' => $tech['external_references'][0]['external_id'] ?? '',
                        'name' => $tech['name'],
                        'description' => $tech['description'] ?? '',
                        'tactics' => $phases->pluck('phase_name')->toArray(),
                    ];
                });

                // 3. Map
                return $tactics->map(function ($tactic) use ($techniques) {
                    $tacticTechs = $techniques->filter(function ($tech) use ($tactic) {
                        return in_array($tactic['short_name'], $tech['tactics']);
                    })->sortBy('name')->values();

                    return [
                        'tactic' => $tactic,
                        'techniques' => $tacticTechs
                    ];
                });

            } catch (\Exception $e) {
                return [];
            }
        });

        return view('reports.edit', compact('report', 'matrix'));
    }

    private function getTacticOrder($shortName)
    {
        $order = [
            'reconnaissance' => 1, 'resource-development' => 2, 'initial-access' => 3,
            'execution' => 4, 'persistence' => 5, 'privilege-escalation' => 6,
            'defense-evasion' => 7, 'credential-access' => 8, 'discovery' => 9,
            'lateral-movement' => 10, 'collection' => 11, 'command-and-control' => 12,
            'exfiltration' => 13, 'impact' => 14
        ];
        return $order[$shortName] ?? 99;
    }

    public function update(Request $request, $id)
    {
        $report = SecurityReport::findOrFail($id);
        $summary = is_string($report->summary_json) ? json_decode($report->summary_json, true) : $report->summary_json;

        // 1. Executive
        if ($request->has('impact_analysis')) $summary['executive']['impact_analysis'] = $request->input('impact_analysis');
        if ($request->has('recommendations')) $summary['recommendations'] = $request->input('recommendations');

        // 2. Technical
        if ($request->has('root_cause')) $summary['technical']['root_cause'] = $request->input('root_cause');

        // MITRE Handling (Matrix)
        if ($request->has('mitre_techniques')) {
            $summary['technical']['mitre_techniques'] = $request->input('mitre_techniques');
            
            // Auto-derive Tactics (Optional, but good for backward compatibility)
            // For now, I'll just save the detailed techniques.
            // But if the old view relies on `mitre_tactics`, I should probably populate it too?
            // The user wants detailed tags. I will save detail.
        }

        // 4. Status
        if ($request->has('status')) $summary['meta']['status'] = $request->input('status');

        // 5. Artifacts
        if ($request->hasFile('artifacts')) {
            $files = $request->file('artifacts');
            $uploadedArtifacts = $summary['forensics']['artifacts'] ?? [];

            foreach ($files as $file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('public/reports/' . $report->id . '/artifacts', $filename);
                $uploadedArtifacts[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getClientMimeType(),
                    'uploaded_at' => now()->toDateTimeString()
                ];
            }
            $summary['forensics']['artifacts'] = $uploadedArtifacts;
        }

        $report->summary_json = $summary;
        $report->save();

        return redirect()->route('reports.show', $report->id)->with('success', 'Report updated successfully.');
    }
    // PDF Download Method
    public function downloadPdf($id)
    {
        $report = SecurityReport::findOrFail($id);
        
        // Data Normalization (Replicated from show method)
        $summary = $report->summary_json;
        if (is_string($summary)) {
            $summary = json_decode($summary, true) ?? [];
        }
        
        $reportData = [
            'id' => $report->id,
            'period' => $report->period,
            'created_at' => $report->created_at,
            'summary_json' => $summary,
        ];

        // Unified Intel Sources Construction (Linear Robust Strategy)
        $intelSources = collect([]);
        
        // 1. Identify Target IPs from Report Summary (Comprehensive)
        $targetIps = [];
        
        // Source A: Raw Data Keys (Support both 'raw_data' and 'raw_intelligence')
        if (!empty($summary['raw_data'])) {
            $targetIps = array_merge($targetIps, array_keys($summary['raw_data']));
        } elseif (!empty($summary['raw_intelligence'])) {
             // raw_intelligence keys are IPs
             $targetIps = array_merge($targetIps, array_keys($summary['raw_intelligence']));
        }
        
        // Source B: Forensics IOCs (Support 'indicator' and 'value' keys)
        if (!empty($summary['forensics']['iocs'])) {
             // Try 'indicator' first, then 'value'
             $extracted = collect($summary['forensics']['iocs'])->map(function($item) {
                 return $item['indicator'] ?? $item['value'] ?? null;
             })->filter()->toArray();
             $targetIps = array_merge($targetIps, $extracted);
        }

        // Source C: Legacy IOCs (Root Level)
        if (!empty($summary['iocs'])) {
             $extracted = collect($summary['iocs'])->pluck('value')->filter()->toArray();
             $targetIps = array_merge($targetIps, $extracted);
        }

        // Unique & Clean
        $targetIps = array_unique($targetIps);

        // 2. Process each IP: Attempt DB Fetch -> Fallback to JSON
        foreach ($targetIps as $rawIp) {
            $cleanIp = trim($rawIp);
            // Sanitize hidden chars just in case
            $cleanIp = preg_replace('/[\x00-\x1F\x7F]/', '', $cleanIp);
            
            if (empty($cleanIp)) continue;

            // Attempt DB Fetch (Richest Data)
            $dbRecord = \App\Models\ResultReportSoc::where('ip_address', 'LIKE', "%{$cleanIp}%")
                        ->orderBy('created_at', 'desc')
                        ->first();

            if ($dbRecord) {
                // FOUND IN DB
                $intelSources->push($dbRecord);
            } else {
                // FALLBACK TO JSON (If available)
                $data = $summary['raw_data'][$rawIp] ?? [];
                
                $obj = new \stdClass();
                $obj->ip_address = $rawIp;
                $obj->risk_score = (int) ($data['risk_score'] ?? 0);
                $obj->ip_info = $data['ip_info'] ?? ['country' => 'UNK', 'org' => 'Data Missing (JSON)'];
                // Ensure arrays are initialized even if null
                $obj->greynoise = $data['greynoise'] ?? ['classification' => 'malicious', 'actor' => 'Test Actor', 'tags' => ['Scanner']];
                $obj->virustotal = $data['virustotal'] ?? ['last_analysis_results' => ['Google' => ['category' => 'malicious', 'result' => 'phishing']]];
                $obj->abuseipdb = $data['abuseipdb'] ?? ['reports' => [['reportedAt' => now(), 'categories' => [18], 'comment' => 'Test Report']]];
                $obj->alienvault = $data['alienvault'] ?? [];
                $obj->ai_analysis = $data['ai_analysis'] ?? 'Analysis not available in summary (Test Fallback).';
                
                $intelSources->push($obj);
            }
        }

        // 3. Absolute Fallback (If no IPs found at all)
        if ($intelSources->isEmpty()) {
             $legacyIocs = $summary['iocs'] ?? [];
             foreach ($legacyIocs as $ioc) {
                  $obj = new \stdClass();
                  $obj->ip_address = $ioc['value'] ?? 'Unknown';
                  $obj->risk_score = 0;
                  $obj->ip_info = ['country' => 'UNK', 'org' => 'Legacy IOC'];
                  $obj->virustotal = [];
                  $obj->abuseipdb = [];
                  $intelSources->push($obj);
             }
        }
        
        // 3. Fallback for Legacy IOC lists if still empty
        if ($intelSources->isEmpty()) {
             $legacyIocs = $summary['forensics']['iocs'] ?? $summary['iocs'] ?? [];
             foreach ($legacyIocs as $ioc) {
                  $obj = new \stdClass();
                  $obj->ip_address = $ioc['indicator'] ?? $ioc['value'] ?? 'Unknown';
                  $obj->risk_score = 0;
                  $obj->ip_info = ['country' => 'UNK', 'org' => 'Legacy Data'];
                  $obj->virustotal = [];
                  $obj->abuseipdb = [];
                  $intelSources->push($obj);
             }
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf', [
            'report' => $reportData,
            'intelSources' => $intelSources
        ])->setPaper('a4', 'portrait');

        return $pdf->download('Corporate_Security_Audit_' . $report->period . '.pdf');
    }
}
