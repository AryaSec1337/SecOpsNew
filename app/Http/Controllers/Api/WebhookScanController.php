<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WebhookFileScan;
use App\Services\FileScanPipelineService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\ThreatDetectedMail;

class WebhookScanController extends Controller
{
    protected $pipelineService;

    public function __construct(FileScanPipelineService $pipelineService)
    {
        $this->pipelineService = $pipelineService;
    }

    public function handle(Request $request)
    {
        // Handle physical file upload or just metadata payload
        $hasFile = $request->hasFile('file');
        
        $request->validate([
            'file' => 'nullable|file', // Make file optional to support pure-JSON metadata pushes
            'file_id' => 'required|string',
            'sha256' => 'required|string',
            'size_bytes' => 'nullable|integer',
            'original_filename' => 'nullable|string',
            'uploaded_at' => 'nullable',
            'fullpath' => 'nullable|string',
            'endpoint' => 'nullable|string',
            'request_id' => 'nullable|string',
            'server_hostname' => 'nullable|string',
        ]);

        $file = $hasFile ? $request->file('file') : null;
        
        try {
            // 1. Create WebhookFileScan Record
            $scanRecord = new WebhookFileScan();
            $scanRecord->file_id = $request->input('file_id');
            $scanRecord->sha256 = $request->input('sha256');
            $scanRecord->size_bytes = $request->input('size_bytes') ?? ($hasFile ? $file->getSize() : 0);
            $scanRecord->original_filename = $request->input('original_filename') ?? ($hasFile ? $file->getClientOriginalName() : 'unknown_file');
            $scanRecord->server_hostname = $request->input('server_hostname');
            $scanRecord->fullpath = $request->input('fullpath');
            
            // If we have a physical file, run the full pipeline (YARA, ClamAV, VT)
            // If we only have metadata, run the pipeline with a null path (Pipeline logic handles skipping local scans)
            $filePathToScan = $hasFile ? $file->getRealPath() : null;

            // Run the pipeline (YARA, ClamAV, VT)
            $scanRecord = $this->pipelineService->runPipeline($scanRecord, $filePathToScan);

            // 2. Create WebhookAlert (FUM Ticket)
            $detectedBy = 'N/A';
            if (isset($scanRecord->yara_result['matches']) && count($scanRecord->yara_result['matches']) > 0) {
                $yaraRules = array_map(fn($m) => $m['rule'] ?? 'unknown', $scanRecord->yara_result['matches']);
                $detectedBy = 'YARA (' . count($yaraRules) . ' rules: ' . implode(', ', array_slice($yaraRules, 0, 5)) . ')';
            }
            if (isset($scanRecord->clamav_result['infected']) && $scanRecord->clamav_result['infected']) {
                $detectedBy .= ($detectedBy !== 'N/A' ? ' + ' : '') . 'ClamAV';
            }
            $vtStats = $scanRecord->vt_result['attributes']['last_analysis_stats'] ?? null;
            if ($vtStats && ($vtStats['malicious'] ?? 0) > 0) {
                $detectedBy .= ($detectedBy !== 'N/A' ? ' + ' : '') . 'VirusTotal (' . $vtStats['malicious'] . ' engines)';
            }

            // Build detailed description
            $descLines = [];
            $descLines[] = "=== File Upload Monitoring (FUM) ===";
            $descLines[] = "File: {$scanRecord->original_filename}";
            $descLines[] = "SHA256: {$scanRecord->sha256}";
            $descLines[] = "Size: " . number_format($scanRecord->size_bytes / 1024, 2) . " KB";
            $descLines[] = "Server: " . ($scanRecord->server_hostname ?? 'N/A');
            $descLines[] = "Path: " . ($scanRecord->fullpath ?? 'N/A');
            $descLines[] = "Verdict: {$scanRecord->verdict}";
            $descLines[] = "";

            // YARA detail
            $descLines[] = "--- YARA Scan ---";
            if (isset($scanRecord->yara_result['matches']) && count($scanRecord->yara_result['matches']) > 0) {
                $rules = array_map(fn($m) => $m['rule'] ?? 'unknown', $scanRecord->yara_result['matches']);
                $descLines[] = "Status: " . count($rules) . " rule(s) matched";
                $descLines[] = "Rules: " . implode(', ', $rules);
            } elseif (isset($scanRecord->yara_result['message'])) {
                $descLines[] = "Status: " . $scanRecord->yara_result['message'];
            } else {
                $descLines[] = "Status: Clean (no matches)";
            }
            $descLines[] = "";

            // ClamAV detail
            $descLines[] = "--- ClamAV Scan ---";
            if (isset($scanRecord->clamav_result['infected']) && $scanRecord->clamav_result['infected']) {
                $descLines[] = "Status: INFECTED";
                $descLines[] = "Detail: " . ($scanRecord->clamav_result['output'] ?? 'Virus detected');
            } elseif (isset($scanRecord->clamav_result['message'])) {
                $descLines[] = "Status: " . $scanRecord->clamav_result['message'];
            } else {
                $descLines[] = "Status: Clean";
            }
            $descLines[] = "";

            // VirusTotal detail
            $descLines[] = "--- VirusTotal Lookup ---";
            if ($vtStats) {
                $descLines[] = "Malicious: " . ($vtStats['malicious'] ?? 0) . " engines";
                $descLines[] = "Suspicious: " . ($vtStats['suspicious'] ?? 0) . " engines";
                $descLines[] = "Undetected: " . ($vtStats['undetected'] ?? 0) . " engines";
            } elseif (isset($scanRecord->vt_result['message'])) {
                $descLines[] = "Status: " . $scanRecord->vt_result['message'];
            } else {
                $descLines[] = "Status: Not available";
            }

            // Build structured scan results for the alert
            $scanResults = [
                'yara_matches' => isset($scanRecord->yara_result['matches']) ? count($scanRecord->yara_result['matches']) : 0,
                'yara_rules' => isset($scanRecord->yara_result['matches']) ? array_map(fn($m) => $m['rule'] ?? 'unknown', $scanRecord->yara_result['matches']) : [],
                'clamav_infected' => $scanRecord->clamav_result['infected'] ?? false,
                'clamav_output' => $scanRecord->clamav_result['output'] ?? null,
                'vt_malicious' => $vtStats['malicious'] ?? null,
                'vt_suspicious' => $vtStats['suspicious'] ?? null,
                'vt_undetected' => $vtStats['undetected'] ?? null,
            ];

            $webhookAlert = \App\Models\WebhookAlert::create([
                'title' => "FUM Alert: " . $scanRecord->original_filename,
                'verdict' => $scanRecord->verdict,
                'status' => 'Pending',
                'server_hostname' => $scanRecord->server_hostname,
                'fullpath' => $scanRecord->fullpath,
                'original_filename' => $scanRecord->original_filename,
                'sha256' => $scanRecord->sha256,
                'size_bytes' => $scanRecord->size_bytes,
                'description' => implode("\n", $descLines),
                'detected_by' => $detectedBy,
                'scan_results' => $scanResults,
                'webhook_file_scan_id' => $scanRecord->id,
            ]);

            $scanRecord->save();

            // Send email notification if threat detected
            if (in_array($scanRecord->verdict, ['SUSPICIOUS', 'MALICIOUS'])) {
                try {
                    // Determine which engine detected the threat
                    $detectedBy = 'Unknown';
                    $yaraMatches = $scanRecord->yara_result['matches'] ?? [];
                    $clamInfected = $scanRecord->clamav_result['infected'] ?? false;
                    $vtMalicious = $scanRecord->vt_result['attributes']['last_analysis_stats']['malicious'] 
                                   ?? $scanRecord->vt_result['stats']['malicious'] 
                                   ?? 0;

                    if (!empty($yaraMatches)) {
                        $ruleNames = array_map(fn($m) => $m['rule'] ?? '', $yaraMatches);
                        $detectedBy = 'YARA (' . count($yaraMatches) . ' rules: ' . implode(', ', array_slice($ruleNames, 0, 5)) . ')';
                    } elseif ($clamInfected) {
                        $detectedBy = 'ClamAV';
                    } elseif ($vtMalicious > 0) {
                        $detectedBy = 'VirusTotal (' . $vtMalicious . ' engines)';
                    }

                    $notifyTo = config('mail.notification_to', env('MAIL_NOTIFICATION_TO'));
                    if ($notifyTo) {
                        Mail::to($notifyTo)->send(new ThreatDetectedMail($scanRecord, $detectedBy));
                        Log::info('Threat notification email sent to: ' . $notifyTo);
                    }
                } catch (\Exception $mailEx) {
                    Log::error('Failed to send threat notification email: ' . $mailEx->getMessage());
                }
            }

            return response()->json([
                'file_id' => $scanRecord->file_id,
                'sha256' => $scanRecord->sha256,
                'verdict' => $scanRecord->verdict,
                'yara_result' => $scanRecord->yara_result,
                'clamav_result' => $scanRecord->clamav_result,
                'vt_summary' => isset($scanRecord->vt_result) ? ($scanRecord->vt_result['attributes']['last_analysis_stats'] ?? null) : null,
                'timestamps_stages' => $scanRecord->timestamps_stages,
                'webhook_alert_id' => $webhookAlert->id
            ]);

        } catch (\Exception $e) {
            Log::error('WebhookScanController Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'An error occurred processing the file scan pipeline.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
