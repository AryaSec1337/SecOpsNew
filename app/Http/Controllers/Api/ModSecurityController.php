<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ModSecurityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ModSecurityController extends Controller
{
    /**
     * Handle incoming ModSecurity JSON logs from Agent.
     */
    public function store(Request $request)
    {
        try {
            Log::info('ModSec Ingest Request START');
            Log::info('Payload:', $request->all());

            // ModSecurity Native JSON Format usually wraps in "transaction"
            $payload = $request->json()->all();
            
            if (!isset($payload['transaction'])) {
                Log::warning('ModSec: Missing transaction key');
                return response()->json(['message' => 'Invalid Log Format. Expected "transaction" root.'], 400);
            }

            $tx = $payload['transaction'];
            $asset = $request->user(); // Resolved by CheckAgentToken

            // Extract Rule Messages & Auto-Block Logic
            $rules = [];
            $rulesTriggeredBlock = false;
            $highestSeverityRule = null; // Track most critical rule for Incident creation

            if (isset($tx['messages']) && is_array($tx['messages'])) {
                foreach ($tx['messages'] as $msg) {
                    $severity = $msg['details']['severity'] ?? '0'; // Default to 0 (Emergency) if missing? Actually check numeric. 
                    // ModSec strict: 0=Emergency, 1=Alert, 2=Critical, 3=Error, 4=Warning...
                    
                    $domLog = [
                        'id' => $msg['details']['ruleId'] ?? null,
                        'msg' => $msg['message'] ?? 'Unknown Alert',
                        'data' => $msg['details']['data'] ?? null,
                        'severity' => $severity
                    ];
                    $rules[] = $domLog;

                    // TRACK HIGHEST SEVERITY (Lowest number is higher severity)
                    // We care about severity <= 2 for incidents
                    if (is_numeric($severity) && (int)$severity <= 2) {
                        if (!$highestSeverityRule || (int)$severity < (int)$highestSeverityRule['severity']) {
                            $highestSeverityRule = $domLog;
                        }
                    }

                    // DEBUG LOG
                    Log::info("ModSec Check: Rule {$domLog['id']} Severity Raw: '{$severity}' Parsed: " . (int)$severity);
                    if (is_numeric($severity) && (int)$severity <= 2) {
                        Log::info("-> Severity is CRITICAL/HIGH. Updating Highest Severity Rule.");
                    } else {
                        Log::info("-> Severity is LOW/INFO. Skipping.");
                    }

                    // AUTO BLOCKING LOGIC
                    // Block if Severity <= 2 (Critical, Alert, Emergency)
                    if (is_numeric($severity) && (int)$severity <= 2) {
                        $clientIp = $tx['client_ip'] ?? '0.0.0.0';
                        
                        // Check if already blocked to avoid duplicates
                        $isBlocked = \App\Models\BlockedIp::where('ip_address', $clientIp)->exists();
                        
                        // Log::info("ModSec Critical: IP $clientIp. Already Blocked? " . ($isBlocked ? 'Yes' : 'No'));

                        if (!$isBlocked && !$rulesTriggeredBlock) {
                            \App\Models\BlockedIp::create([
                                'ip_address' => $clientIp,
                                'agent_id' => $asset->id,
                                'rule_id' => $domLog['id'],
                                'status' => 'pending_block',
                                'reason' => "Auto-Blocked by WAF: " . \Illuminate\Support\Str::limit($domLog['msg'], 200),
                                'blocked_at' => null, // Set null, will be updated by ACK
                            ]);
                            $rulesTriggeredBlock = true;
                            Log::warning("⚠️ Auto-Blocked IP: {$clientIp} due to Rule {$domLog['id']} (Queued for Agent)");
                        }
                    }
                }
            }

            // Create Log Entry
            $log = ModSecurityLog::create([
                'asset_id' => $asset->id,
                'transaction_id' => $tx['unique_id'] ?? uniqid('modsec_'),
                'client_ip' => $tx['client_ip'] ?? '0.0.0.0',
                'uri' => $tx['request']['uri'] ?? ($tx['request']['headers']['Host'] ?? 'unknown'),
                'method' => $tx['request']['method'] ?? 'UNKNOWN',
                'user_agent' => $tx['request']['headers']['User-Agent'] ?? null,
                'rule_matches' => $rules,
                'raw_log' => $payload,
                'created_at' => now(),
            ]);

            // UNIFY: Also create ActivityLog for the main Log Monitor
            // This ensures WAF events appear in the general "Log Monitor" page
            \App\Models\ActivityLog::create([
                'timestamp' => now(), // or parse $tx['time_stamp']
                'status_code' => 403, // WAF usually blocks
                'method' => $tx['request']['method'] ?? 'UNKNOWN',
                'path' => $tx['request']['uri'] ?? 'unknown',
                'ip_address' => $tx['client_ip'] ?? null,
                'agent_name' => $asset->name,
                'agent_ip' => $asset->ip_address,
                'os' => $asset->os_name ?? 'Linux',
                'user_agent' => $tx['request']['headers']['User-Agent'] ?? null,
                'log_file' => 'ModSecurity',
                'details' => array_merge(['rule_matches' => $rules], $payload),
                'size' => 0,
            ]);


            // --- 1. STANDARD WAF INCIDENT LOGIC ---
            // If any standard ModSecurity rule (Severity <= 2) was triggered, create an Incident
            if ($highestSeverityRule) {
                 \App\Models\Incident::create([
                    'title' => "WAF Alert: " . \Illuminate\Support\Str::limit($highestSeverityRule['msg'], 100),
                    'severity' => $highestSeverityRule['severity'] == 0 ? 'Critical' : ($highestSeverityRule['severity'] <= 1 ? 'High' : 'Medium'),
                    'status' => 'Open',
                    'description' => "Standard ModSecurity Rule Triggered [Rule ID: {$highestSeverityRule['id']}]",
                    'source_type' => ModSecurityLog::class,
                    'source_id' => $log->id,
                    'metadata' => [
                        'rule_source' => 'ModSecurity',
                        'rule_id' => $highestSeverityRule['id']
                    ]
                ]);
            }

            // --- 2. CUSTOM RULE ENGINE CHECK ---
            // Check against user-defined XML rules (RuleEngine)
            $customThreat = \App\Services\RuleEngine::evaluateLogs($payload);

            if ($customThreat) {
                // Create Incident
                \App\Models\Incident::create([
                    'title' => $customThreat['name'],
                    'severity' => $customThreat['severity'],
                    'status' => 'Open',
                    'description' => $customThreat['description'],
                    'source_type' => ModSecurityLog::class,
                    'source_id' => $log->id, // Linked to the stored log
                    'metadata' => [
                        'rule_source' => 'CustomRule',
                        'rule_id' => $customThreat['rule_id']
                    ]
                ]);

                // Custom Rule Auto-Block
                if (!empty($customThreat['auto_block'])) {
                     $clientIp = $tx['client_ip'] ?? '0.0.0.0';
                     $isBlocked = \App\Models\BlockedIp::where('ip_address', $clientIp)->exists();

                     if (!$isBlocked) {
                        \App\Models\BlockedIp::create([
                            'ip_address' => $clientIp,
                            'agent_id' => $asset->id,
                            'rule_id' => $customThreat['rule_id'] ?? 'CUSTOM',
                            'status' => 'pending_block', // Queued for Agent
                            'reason' => "Custom Rule Block: " . $customThreat['name'],
                            'blocked_at' => null,
                        ]);
                        Log::warning("⚠️ Custom Rule Block: {$clientIp} matched {$customThreat['name']}");
                     }
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Log ingested',
                'id' => $log->id
            ], 201);

        } catch (\Exception $e) {
            Log::error('ModSec Ingestion Failed: ' . $e->getMessage());
            return response()->json(['message' => 'Server Error'], 500);
        }
    }
}
