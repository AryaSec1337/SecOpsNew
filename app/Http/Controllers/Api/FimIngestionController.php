<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FileIntegrityLog;
use App\Models\Incident;
use App\Services\RuleEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FimIngestionController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validation
        $request->validate([
            'file_path' => 'required|string',
            'change_type' => 'required|string', // Modified, Created, Deleted
            'detected_at' => 'required|date',
        ]);

        try {
            // 2. Evaluate Rule
            $threat = RuleEngine::evaluateFim($request->all());
            
            // Determine Severity (Rule engine overrides request severity if critical path match)
            $severity = $threat ? $threat['severity'] : ($request->severity ?? 'Medium');

            // 3. Store in FileIntegrityLog
            $fim = FileIntegrityLog::create([
                'file_path' => $request->file_path,
                'change_type' => $request->change_type,
                'process_name' => $request->process_name,
                'user' => $request->user ?? 'root',
                'hash_before' => $request->hash_before,
                'hash_after' => $request->hash_after,
                'severity' => $severity,
                'detected_at' => $request->detected_at,
                'details' => $threat ? array_merge($request->all(), ['threat_match' => $threat]) : $request->all(),
            ]);

            // 4. If Threat Detected (High/Critical) OR Explicit Rule Match -> Create Incident
            if ($threat || in_array($severity, ['High', 'Critical'])) {
                Incident::create([
                    'title' => $threat['title'] ?? ($request->title ?? "FIM Alert: {$request->change_type}"),
                    'severity' => $severity,
                    'status' => 'Open',
                    'description' => $threat['description'] ?? "File {$request->file_path} was {$request->change_type} by {$request->user}",
                    'source_type' => FileIntegrityLog::class,
                    'source_id' => $fim->id,
                    'metadata' => [
                        'agent' => $request->agent_name ?? 'Unknown',
                        'hash_diff' => $request->hash_before . ' -> ' . $request->hash_after
                    ]
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'FIM Log processed',
                'incident_created' => ($threat || in_array($severity, ['High', 'Critical'])) ? true : false
            ]);

        } catch (\Exception $e) {
            Log::error('FIM Ingestion Failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Processing failed'], 500);
        }
    }
    public function checkHash(Request $request)
    {
        $request->validate([
            'hash' => 'required|string',
        ]);

        // Search for recent logs with this hash that have VT data
        $cachedLog = FileIntegrityLog::where('hash_after', $request->hash)
            ->whereNotNull('details')
            ->orderBy('created_at', 'desc')
            ->get()
            ->first(function ($log) {
                // Check if details key 'virustotal' exists and has actual data
                return isset($log->details['virustotal']) && !empty($log->details['virustotal']);
            });

        if ($cachedLog) {
            return response()->json([
                'status' => 'found',
                'virustotal' => $cachedLog->details['virustotal']
            ]);
        }

        return response()->json(['status' => 'not_found']);
    }
}
