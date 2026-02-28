<?php

namespace App\Services;

class LogAnalyzerService
{
    protected $intelService;
    protected $deepSeekService;

    public function __construct(ThreatIntelligenceService $intelService, DeepSeekService $deepSeekService)
    {
        $this->intelService = $intelService;
        $this->deepSeekService = $deepSeekService;
    }

    public function analyze($logContent, \Closure $onProgress = null, array $options = [])
    {
        // Helper to report progress safely
        $report = function($step, $status, $msg) use ($onProgress) {
            if ($onProgress) $onProgress($step, $status, $msg);
        };

        // PHASE 1: PRE-EXISTING INTEL (For Phase 2 Synthesis)
        if (!empty($options['intel_data'])) {
            $report('Extraction', 'skipped', "Using pre-verified Intelligence Data.");
            $intelData = $options['intel_data'];
            // We still need IoCs for the report structure, let's extract them from the passed intel
            $iocs = [];
            foreach ($intelData as $ip => $data) {
                if (($data['risk_score'] ?? 0) > 0) {
                     $iocs[] = [
                        'type' => 'IPv4',
                        'value' => $ip,
                        'desc' => "Risk Score: {$data['risk_score']} (Pre-AI Assessment)"
                    ];
                }
            }
        } 
        // PHASE 1: FRESH EXTRACTION & ENRICHMENT
        else {
            // 1. Extract IPs
            $report('Extraction', 'running', "Parsing logs for IP addresses...");
            preg_match_all('/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b/', $logContent, $matches);
            $allIps = array_unique($matches[0]);
            
            // Filter out Private/Reserved IPs
            $ips = array_filter($allIps, function($ip) {
                return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
            });
            // Re-index array after filtering
            $ips = array_values($ips);

            $count = count($ips);
            
            if ($count === 0) {
                $report('Extraction', 'error', "No public IP addresses found (Internal/Private IPs ignored).");
                return [
                    'title' => 'Analysis Canceled',
                    'executive_summary' => 'Analysis stopped because no public IP addresses were found in the provided logs. Internal IPs are excluded from external threat intelligence checks.',
                    'iocs' => [],
                    'raw_data' => []
                ];
            }

            // NEW: IP SELECTION VALIDATION
            // If user already provided selected_ips, use those
            if (!empty($options['selected_ips'])) {
                $ips = array_intersect($ips, $options['selected_ips']);
                $ips = array_values($ips); // Re-index
                $report('Extraction', 'success', "Using " . count($ips) . " user-selected IPs.");
            }
            // If 2+ IPs found and no selection provided, ask user to select
            elseif ($count >= 2) {
                $report('Extraction', 'pending', "Found $count Public IPs. Awaiting user selection...");
                return [
                    'status' => 'ip_selection_required',
                    'extracted_ips' => $ips,
                    'message' => "Ditemukan $count External IP. Silakan pilih IP yang ingin dianalisis (max 5)."
                ];
            }
            // Only 1 IP found, auto-analyze
            else {
                $report('Extraction', 'success', "Found 1 unique Public IP. Analyzing...");
            }

            // Limit to top 5 distinctive IPs to save API quota/time
            $ips = array_slice($ips, 0, 5);

            $intelData = [];
            $iocs = [];

            foreach ($ips as $ip) {
                $report('Analysis', 'running', "Enriching IP: $ip");
                
                // Pass the callback but prefix the step name so the UI knows it's a substep if needed
                $analysis = $this->intelService->analyzeIp($ip, function($s, $st, $m) use ($report, $ip) {
                    $report("TI [$ip]", $st, "$s: $m");
                });
                
                $intelData[$ip] = $analysis;

                // Stream the raw data to the frontend for visibility
                $report('TI_DATA_PAYLOAD', 'info', json_encode([
                    'ip' => $ip,
                    'details' => $analysis
                ]));

                // If malicious/risky, add to IoCs (Preliminary check)
                if (($analysis['risk_score'] ?? 0) > 0) {
                    $iocs[] = [
                        'type' => 'IPv4',
                        'value' => $ip,
                        'desc' => "Risk Score: {$analysis['risk_score']} (Pre-AI Assessment)"
                    ];
                }
            }
        }

        // CONTROL POINT: If user only requested a scan, stop here.
        if (!empty($options['skip_ai'])) {
            $report('Analysis', 'success', "Enrichment Complete. Waiting for review...");
            return [
                'status' => 'review_required',
                'raw_data' => $intelData,
                'iocs' => $iocs
            ];
        }

        // 2. Construct Prompt for DeepSeek
        $report('DeepSeek', 'running', "Synthesizing forensic report with AI (DeepSeek)...");
        $prompt = $this->buildPrompt($logContent, $intelData);

        // 3. Get AI Analysis
        $aiResponseText = $this->deepSeekService->generateAnalysis($prompt);
        
        // CHECK: Did DeepSeek return an error?
        if (str_starts_with($aiResponseText, 'AI Analysis')) {
            // DeepSeek returned an error, don't try to parse as JSON
            $report('DeepSeek', 'error', $aiResponseText);
            return [
                'title' => 'Security Incident Analysis Failed',
                'executive_summary' => $aiResponseText,
                'technical_analysis' => 'AI analysis could not be completed. Error: ' . $aiResponseText,
                'risk_score' => 'Unknown',
                'iocs' => $iocs,
                'raw_data' => $intelData
            ];
        }
        
        $report('DeepSeek', 'success', "AI Analysis complete.");
        
        // 4. Parse AI Response (Expecting JSON)
        $aiData = $this->parseAiResponse($aiResponseText);

        return [
            'title' => $aiData['title'] ?? "Security Incident Report - " . now()->format('Y-m-d H:i'),
            'executive_summary' => $aiData['executive_summary'] ?? "Analysis could not be generated.",
            'technical_analysis' => $aiData['technical_analysis'] ?? $aiResponseText, // Fallback to raw text if parsing fails
            'risk_score' => $aiData['risk_score'] ?? 'Medium',
            'iocs' => $iocs, // We keep our verified TI-based IoCs + potentially AI ones
            'timeline' => $aiData['timeline'] ?? [], // New Timeline field
            'metrics' => $aiData['metrics'] ?? ['mttd' => 'N/A', 'mttr' => 'N/A'],
            'raw_data' => $intelData 
        ];
    }

    private function buildPrompt($logs, $intel)
    {
        // TRUNCATION: Limit log size to prevent token overflow
        // ~4 chars per token, 131K token limit, we allocate ~30K chars for logs
        $maxLogChars = 30000;
        if (strlen($logs) > $maxLogChars) {
            $logs = substr($logs, 0, $maxLogChars) . "\n\n[... LOG TRUNCATED - Showing first {$maxLogChars} characters ...]";
        }

        // SUMMARIZE TI DATA: Keep essential fields only, remove verbose data
        $summarizedIntel = [];
        foreach ($intel as $ip => $data) {
            $summary = [
                'ip' => $ip,
                'risk_score' => $data['risk_score'] ?? 0,
                'geo' => [
                    'country' => $data['geo']['country'] ?? 'Unknown',
                    'city' => $data['geo']['city'] ?? 'Unknown',
                    'org' => $data['geo']['org'] ?? 'Unknown'
                ],
                'greynoise' => [
                    'classification' => $data['greynoise']['classification'] ?? 'unknown',
                    'noise' => $data['greynoise']['noise'] ?? false,
                    'tags' => array_slice($data['greynoise']['tags'] ?? [], 0, 5) // Max 5 tags
                ],
                'virustotal' => [
                    'malicious_count' => $data['virustotal']['last_analysis_stats']['malicious'] ?? 0,
                    'suspicious_count' => $data['virustotal']['last_analysis_stats']['suspicious'] ?? 0,
                    'harmless_count' => $data['virustotal']['last_analysis_stats']['harmless'] ?? 0
                    // Remove individual vendor results to save tokens
                ],
                'abuseipdb' => [
                    'confidence_score' => $data['abuseipdb']['abuseConfidenceScore'] ?? 0,
                    'total_reports' => $data['abuseipdb']['totalReports'] ?? 0,
                    'isp' => $data['abuseipdb']['isp'] ?? 'Unknown',
                    'categories' => array_slice($data['abuseipdb']['categories'] ?? [], 0, 5) // Top 5 categories
                    // Remove individual reports
                ],
                'alienvault' => [
                    'pulse_count' => $data['alienvault']['pulse_count'] ?? 0,
                    'reputation' => $data['alienvault']['reputation'] ?? 0,
                    // Keep only top pulse names
                    'top_pulses' => array_map(function($p) { 
                        return $p['name'] ?? 'Unknown'; 
                    }, array_slice($data['alienvault']['pulses'] ?? [], 0, 3))
                ]
            ];
            $summarizedIntel[$ip] = $summary;
        }

        $intelJson = json_encode($summarizedIntel, JSON_PRETTY_PRINT);
        
        return <<<EOT
You are a Tier 3 SOC Analyst and Cyber Forensics Expert.
Your task is to analyze the provided RAW LOGS and detailed THREAT INTELLIGENCE (TI) DATA to generate a comprehensive security incident report.

DATA SOURCES:
1. RAW LOGS: Server access/error logs showing potential activity.
2. THREAT INTELLIGENCE: Data from 5 specific providers for IPs found in the logs:
   - IPinfo: Geolocation, ASN, ISP, and Organization (Business/Hosting).
   - GreyNoise: Internet background noise, scanner tags, and benign actors.
   - VirusTotal: Multi-vendor detection ratios and specific security vendor flags.
   - AbuseIPDB: Community reports, confidence scores, and specific attack categories (SSH, SQLi, etc.).
   - AlienVault OTX: Pulse associations, malware families, and related indicators.

INPUT DATA:
-----------------------
LOGS:
$logs

THREAT INTELLIGENCE:
$intelJson
-----------------------

INSTRUCTIONS:
1. DEEP DIVE ANALYSIS: You must correlate the logs with the TI data.
   - If AbuseIPDB shows "Brute Force" and logs show "Failed Login", connect them.
   - If VirusTotal shows "Cobalt Strike" and OTX agrees, highlight this high-risk attribution.
   - Citation is key: Explicitly mention "Per AbuseIPDB reports..." or "VirusTotal vendors flagged...".
2. ROOT CAUSE & TACTICS: Determine *why* this happened and map to MITRE ATT&CK.
3. SUMMARY VS DETAIL:
   - Executive Summary: High-level business impact, suitable for a CISO.
   - Technical Analysis: Deep forensic breakdown for engineers, citing specific evidence from the 5 providers.

OUTPUT FORMAT (Strict JSON):
{
    "title": "A professional, distinct title for this incident (e.g., 'Targeted SSH Brute Force from Known Botnet')",
    "executive_summary": "A concise paragraph (2-3 sentences) summarizing what happened, the severity, and the business impact. Do not include technical jargon here.",
    "technical_analysis": "A detailed, multi-paragraph forensic analysis. You MUST discuss the findings from IPinfo, GreyNoise, VirusTotal, AbuseIPDB, and OTX. Explain the attack methodology, the attacker's origin, and the credibility of the threat based on the data.",
    "root_cause": "The most likely technical cause (e.g., 'Weak SSH Credentials', 'Unpatched Services'). You MUST provide a hypothesis even if not 100% certain. Do NOT use 'Unknown'.",
    "mitre_tactics": ["List", "At", "Least", "One", "Tactic", "e.g.", "Initial Access", "Reconnaissance"],
    "risk_score": "Critical, High, Medium, or Low",
    "metrics": {
        "mttd": "Estimate 'Mean Time To Detect' based on the gap between the first log entry and the first defensive action or error. If unknown, estimate based on attack duration (e.g., '5 minutes').",
        "mttr": "Estimate 'Mean Time To Respond' based on when the attack stopped or was blocked. If ongoing, state 'Ongoing'."
    },
    "recommendations": "Provide a tiered defense strategy:\n1. IMMEDIATE: Specific actions to stop the bleeding (e.g., 'Block IP 1.2.3.4 at firewall level').\n2. TACTICAL: Config changes to prevent recurrence (e.g., 'Enable Rate Limiting on Nginx for /api/login').\n3. STRATEGIC: Long-term security posture improvements (e.g., 'Implement MFA', 'Deploy ModSecurity WAF').\nDo NOT used generic phrases like 'Review logs'.",
    "timeline": [
        {"time": "YYYY-MM-DD HH:MM", "type": "Exploit/Scan/Defense", "desc": "Specific event description derived from logs or TI timestamps"}
    ]
}
Do not include markdown formatting like ```json ... ```, just the raw JSON string.
EOT;
    }

    private function parseAiResponse($text)
    {
        // Clean markdown code blocks if Gemini adds them
        $text = str_replace(['```json', '```'], '', $text);
        
        $json = json_decode($text, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            return $json;
        }

        // Fallback for parsing failure
        return [
            'title' => 'Analysis Failed',
            'executive_summary' => 'The AI system failed to generate a valid response. Please review the Raw Data below.',
            'technical_analysis' => "Raw AI Output (JSON Parse Failed): " . $text,
            'root_cause' => 'Unknown (Parse Error)',
            'mitre_tactics' => [],
            'risk_score' => 'Medium', 
            'recommendations' => 'No specific recommendations could be generated due to an AI error.',
            'timeline' => []
        ];
    }
}
