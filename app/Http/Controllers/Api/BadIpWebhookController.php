<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BadIpAlert;
use Illuminate\Support\Facades\Log;

class BadIpWebhookController extends Controller
{
    /**
     * Handle incoming Wazuh Bad IP alerts.
     */
    public function handle(Request $request)
    {
        try {
            // Wazuh webhook sends an array of JSON objects or a single JSON object.
            // If it's an array, process each log.
            $payloads = is_numeric(key($request->all())) ? $request->all() : [$request->all()];

            foreach ($payloads as $payload) {
                // Ensure we have a valid rule description to work with
                $ruleDesc = $payload['rule']['description'] ?? null;
                if (!$ruleDesc) continue;

                $data = $payload['data'] ?? [];
                
                // Extract network values
                $srcIp = $data['srcip'] ?? $data['src_ip'] ?? null;
                $destIp = $data['destip'] ?? $data['dest_ip'] ?? $data['dstip'] ?? $data['dst_ip'] ?? null;
                $destPort = $data['destport'] ?? $data['dest_port'] ?? $data['dstport'] ?? $data['dst_port'] ?? null;
                $proto = $data['proto'] ?? null;
                
                // Extract Signature Severity (usually array in Suricata)
                $signatureSeverityArray = $data['alert']['metadata']['signature_severity'] ?? [];
                $signatureSeverity = is_array($signatureSeverityArray) && count($signatureSeverityArray) > 0 
                                     ? $signatureSeverityArray[0] 
                                     : ($data['alert']['severity'] ?? null);

                // Check for deduplication: same rule description and src_ip and status = New
                $existingAlert = BadIpAlert::where('rule_description', $ruleDesc)
                                           ->where('src_ip', $srcIp)
                                           ->where('status', 'New')
                                           ->first();

                if ($existingAlert) {
                    $existingAlert->update([
                         'occurrences' => $existingAlert->occurrences + 1,
                         'last_seen_at' => now(),
                         'raw_data' => $payload,
                         'updated_at' => now(), // bump timestamp
                    ]);
                    continue;
                }

                // Insert into BadIpAlert
                BadIpAlert::create([
                    'rule_description' => $ruleDesc,
                    'src_ip'           => $srcIp,
                    'dest_ip'          => $destIp,
                    'dest_port'        => $destPort,
                    'proto'            => $proto,
                    'signature_severity' => $signatureSeverity,
                    'occurrences'      => 1,
                    'last_seen_at'     => now(),
                    'raw_data'         => $payload,
                    'status'           => 'New',
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Bad IP alerts processed']);

        } catch (\Exception $e) {
            Log::error('Bad IP Webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process Bad IP alert'
            ], 500);
        }
    }
}
