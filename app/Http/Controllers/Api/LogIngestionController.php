<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Incident;
use App\Services\RuleEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogIngestionController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validation
        $request->validate([
            'timestamp' => 'required|date',
            'agent_ip' => 'required|ip',
            'log_file' => 'required|string',
            // Allow flexible details
        ]);

        try {
            // 2. Evaluate Rule
            $threat = RuleEngine::evaluateLogs($request->all());

            // 3. Store in ActivityLog
            $log = ActivityLog::create([
                'timestamp' => $request->timestamp,
                'status_code' => $request->status_code ?? '200',
                'method' => $request->method ?? 'UNKNOWN',
                'path' => $request->path, // Do not fallback to log_file
                'ip_address' => $request->client_ip ?? null, // Attacker IP if web log
                'agent_name' => $request->agent_name,
                'agent_ip' => $request->agent_ip,
                'os' => $request->os,
                'user_agent' => $request->user_agent,
                'log_file' => $request->log_file,
                'details' => $threat ? array_merge($request->all(), ['threat_match' => $threat]) : $request->all(),
                'size' => $request->size ?? 0,
            ]);

            // 4. If Threat Detected, Create Incident
            if ($threat) {
                Incident::create([
                    'title' => $threat['name'],
                    'severity' => $threat['severity'],
                    'status' => 'Open',
                    'description' => $threat['description'],
                    'source_type' => ActivityLog::class, // Polymorphic
                    'source_id' => $log->id,
                    'metadata' => [
                        'agent_ip' => $request->agent_ip,
                        'client_ip' => $request->client_ip ?? 'Unknown',
                        'rule_id' => $threat['rule_id']
                    ]
                ]);

                // 5. Auto Block IP if enabled
                // DEBUG: Trace Auto Block Logic
                Log::info('Auto-Block Check:', [
                    'auto_block_flag' => $threat['auto_block'] ?? 'null',
                    'client_ip' => $request->client_ip ?? 'null',
                    'request_all' => $request->all() // Temporary: see full payload
                ]);

                if (!empty($threat['auto_block']) && !empty($request->client_ip)) {
                    // Find Agent ID
                    $agent = $request->user();
                    
                    Log::info('Auto-Block: Attempting to block.', ['agent' => $agent ? $agent->id : 'NULL', 'ip' => $request->client_ip]);

                    if ($agent && filter_var($request->client_ip, FILTER_VALIDATE_IP)) {
                        try {
                            // Check if there is an ACTIVE block (pending or blocked)
                            $activeBlock = \App\Models\BlockedIp::where('ip_address', $request->client_ip)
                                ->where('agent_id', $agent->id)
                                ->whereIn('status', ['pending_block', 'blocked', 'pending_unblock'])
                                ->first();

                            if ($activeBlock) {
                                Log::info('Auto-Block: IP already actively blocked/pending.', ['ip' => $request->client_ip]);
                            } else {
                                // Create NEW block record (even if unblocked history exists)
                                $block = \App\Models\BlockedIp::create([
                                    'ip_address' => $request->client_ip,
                                    'agent_id' => $agent->id,
                                    'rule_id' => $threat['rule_id'],
                                    'status' => 'pending_block',
                                    'reason' => $threat['name'] . ': ' . $threat['description'],
                                ]);
                                Log::info('Auto-Block: Success (New Entry)', ['block_id' => $block->id]);
                            }
                        } catch (\Exception $e) {
                            Log::error('Auto-Block: Database Error', ['error' => $e->getMessage()]);
                        }
                    } else {
                        Log::warning('Auto-Block: Aborted. Agent not found or Invalid IP.');
                    }
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Log processed',
                'incident_created' => $threat ? true : false
            ]);

        } catch (\Exception $e) {
            Log::error('Log Ingestion Failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Processing failed'], 500);
        }
    }
}
