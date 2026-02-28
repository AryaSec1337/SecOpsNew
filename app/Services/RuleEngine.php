<?php

namespace App\Services;

class RuleEngine
{
    /**
     * Evaluate Activity Logs for Threats
     */
    public static function evaluateLogs($data)
    {
        $payload = json_encode($data);
        
        // 1. Load Rules from XML
        $xmlRules = self::loadRulesFromXml();
        
        foreach ($xmlRules as $rule) {
            // Check if pattern is valid regex
            if (@preg_match('/' . $rule['pattern'] . '/', $payload)) {
                
                // TRIGGER AUTO BLOCK IF ENABLED
                if (isset($rule['auto_block']) && $rule['auto_block'] === true) {
                    // Extract Source IP from data
                    $sourceIp = $data['properties']['ip'] ?? ($data['subject_ip'] ?? null); // Handle ActivityLog structure
                    $agentId = $data['causer_id'] ?? null; // ActivityLog 'causer_id' maps to 'assets.id' in our context? 
                    // OR specifically for Agent logs, we need to know WHICH agent sent this log.
                    // Assuming LogIngestionController passes 'agent_id' somehow or we infer it.
                    // For now, let's look at the 'ActivityLog' model structure. 
                    // ActivityLog usually has 'causer_type' = 'App\Models\Asset' and 'causer_id' = agent_id if logged by agent.
                    
                    if ($sourceIp && filter_var($sourceIp, FILTER_VALIDATE_IP)) {
                         // Find Agent. If causer_type is Asset, use causer_id.
                         // $data is array from json_decode($log->details) or similar? 
                         // Wait, evaluateLogs receives $log->details usually?
                         // Let's check how evaluateLogs is called.
                         // In LogIngestionController: RuleEngine::evaluateLogs($details).
                         // $details comes from $request->input('details').
                         
                         // We need the Agent ID to block it ON that agent.
                         // We can pass the agent_id to evaluateLogs or infer it if it's in details.
                         // Let's assume for now we need to pass agent_id to evaluateLogs.
                         // But changing signature breaks compatibility.
                         
                         // Hack: Check if we can get agent_id from the context or if we just Create BlockedIp for ALL agents?
                         // No, `blocked_ips` table requires `agent_id`.
                         
                         // Let's modify `evaluateLogs` to accept $agentId optional.
                    }
                }

                return [
                    'rule_id' => $rule['id'],
                    'name' => $rule['name'],
                    'severity' => $rule['severity'],
                    'description' => $rule['description'],
                    'auto_block' => $rule['auto_block'] ?? false // Pass back to Controller to handle creation?
                ];
            }
        }

        // 2. Hardcoded fallback / specific logic (Optional, can be removed if XML covers everything)
        // Kept simple Path Traversal as backup
        if (str_contains($payload, '../') || str_contains($payload, '..\\')) {
             return [
                'rule_id' => 'LFI-001',
                'name' => 'Path Traversal / LFI',
                'severity' => 'High',
                'description' => 'Detected directory traversal attempt.'
            ];
        }

        return null; // No match
    }

    /**
     * Load and Parse XML Rules
     */
    private static function loadRulesFromXml()
    {
        $path = storage_path('app/rules/custom_rules.xml');
        $rules = [];

        if (file_exists($path)) {
            try {
                $xml = simplexml_load_file($path);
                foreach ($xml->rule as $item) {
                    $id = (string)$item['id'];
                    $pattern = (string)$item->pattern;
                    
                    if (!empty($id) && !empty($pattern)) {
                        $rules[] = [
                            'id' => $id,
                            'severity' => (string)$item['severity'],
                            'name' => (string)$item->name,
                            'pattern' => $pattern,
                            'description' => (string)$item->description,
                            'auto_block' => (string)$item['auto_block'] === 'true',
                        ];
                    }
                }
            } catch (\Exception $e) {
                // Log error or ignore
            }
        }
        return $rules;
    }

    /**
     * Evaluate File Integrity for Threats
     */
    public static function evaluateFim($data)
    {
        // 1. Critical System Paths
        $criticalPaths = [
            '/etc/passwd',
            '/etc/shadow',
            '/etc/hosts',
            '/var/www/html/index.php', // Specific critical file
            '/root/.ssh',
            'C:\\Windows\\System32\\drivers\\etc\\hosts',
            // 'C:\\xampp\\htdocs' // REMOVED: Scanning all of htdocs as Critical is too broad
        ];
        
        // Define paths that are "High" risk but not Critical System files (e.g. Webroots)
        $webPaths = [
            '/var/www/html',
            'C:\\xampp\\htdocs'
        ];

        // 2. Refined Severity Logic
        $severity = 'Medium'; // Default
        $description = "File change detected: {$data['change_type']} in {$data['file_path']}";
        $isCriticalPath = false;
        $isWebPath = false;

        // Check Critical Sytem Paths
        foreach ($criticalPaths as $path) {
            if (isset($data['file_path']) && str_starts_with($data['file_path'], $path)) {
                $isCriticalPath = true;
                break;
            }
        }
        
        // Check Web Paths
        foreach ($webPaths as $path) {
            if (isset($data['file_path']) && str_starts_with($data['file_path'], $path)) {
                $isWebPath = true;
                break;
            }
        }

        // Determine Severity based on Path and Extension
        if ($isCriticalPath) {
             $severity = 'Critical';
             $description = "Modification detected in CRITICAL system path: {$data['file_path']}";
        } elseif ($isWebPath) {
             // Web files (htdocs) - Check extension
             // Only flag scripts/executables as High/Critical. Images are Low/Medium.
             $extension = strtolower(pathinfo($data['file_path'], PATHINFO_EXTENSION));
             $riskyExtensions = ['php', 'exe', 'bat', 'sh', 'pl', 'py', 'ps1', 'jsp', 'asp', 'aspx'];
             
             if (in_array($extension, $riskyExtensions)) {
                 $severity = 'High';
                 $description = "Suspicious executable/script modification in webroot: {$data['file_path']}";
             } else {
                 $severity = 'Low'; // Images, Text, CSS, JS etc. are routine
                 $description = "Content modification in webroot: {$data['file_path']}";
             }
        }


        // 3. VirusTotal Integration & Final Decision
        $vtResult = null;
        $hashToCheck = $data['hash'] ?? ($data['details']['hash'] ?? null);
        
        // If the Agent sent VT results in 'details', use them
        if (isset($data['details']['virustotal'])) {
            $vtResult = $data['details']['virustotal'];
        } elseif ($hashToCheck) {
             // Fallback: Check server cache if not provided in payload
             $vtService = new \App\Services\VirusTotalService();
             $vtResult = $vtService->checkHash($hashToCheck); 
        }

        $maliciousCount = 0;
        if ($vtResult) {
            if (isset($vtResult['malicious'])) {
                $maliciousCount = $vtResult['malicious'];
            } elseif (isset($vtResult['stats']['malicious'])) {
                 $maliciousCount = $vtResult['stats']['malicious'];
            } elseif (isset($vtResult['last_analysis_stats']['malicious'])) {
                 $maliciousCount = $vtResult['last_analysis_stats']['malicious'];
            }
        }

        // --- SEVERITY ADJUSTMENT BASED ON VT ---
        if ($maliciousCount > 0) {
            // CASE 1: Malware Detected -> CRITICAL (Overrules everything)
            $severity = 'Critical';
            $description = "MALWARE DETECTED by VirusTotal! ({$maliciousCount} vendors flagged this).";
        
        } elseif ($vtResult && $maliciousCount === 0) {
            // CASE 2: VT Scanned and Clean
            if ($isWebPath && $severity === 'High') {
                // If it was High due to extension (e.g. .php) but VT says clean, downgrade it
                $severity = 'Medium';
                $description = "File change detected (verified clean by VirusTotal): {$data['file_path']}";
            }
            // Note: Critical System Paths (/etc/passwd) remain Critical even if clean, 
            // because they shouldn't be touched remotely.
        }

        // 4. Return Result ONLY if it warrants an Incident (High/Critical)
        // If it's just Medium/Low (routine changes), we return null so no Incident is created (Log only).
        if (in_array($severity, ['High', 'Critical'])) {
             return [
                  'title' => ($maliciousCount > 0) ? 'Malware Detected' : 'Security Alert: File Modified',
                  'severity' => $severity,
                  'description' => $description,
                  'details' => array_merge($data, ['threat_match' => $vtResult ? ['virustotal' => $vtResult] : null])
             ];
        }

        return null; // No Incident for Low/Medium changes
    }
}
