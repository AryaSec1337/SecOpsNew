<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WazuhAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WazuhWebhookController extends Controller
{
    /**
     * Receive Wazuh alert via webhook.
     * Wazuh sends the full alert JSON from its integration script.
     */
    public function handle(Request $request)
    {
        try {
            $payload = $request->all();

            Log::info('Wazuh webhook received', ['keys' => array_keys($payload)]);

            // Extract fields from standard Wazuh alert JSON structure
            $rule = $payload['rule'] ?? [];
            $agent = $payload['agent'] ?? [];
            $manager = $payload['manager'] ?? [];
            $data = $payload['data'] ?? [];
            $decoder = $payload['decoder'] ?? [];
            $syscheck = $payload['syscheck'] ?? null;

            $alert = WazuhAlert::create([
                'alert_id'         => $payload['id'] ?? null,
                'timestamp_wazuh'  => $payload['timestamp'] ?? null,

                // Rule
                'rule_id'          => $rule['id'] ?? null,
                'rule_level'       => (int) ($rule['level'] ?? 0),
                'rule_description' => $rule['description'] ?? null,
                'rule_groups'      => $rule['groups'] ?? null,
                'rule_mitre'       => $rule['mitre'] ?? null,

                // Agent
                'agent_id'         => $agent['id'] ?? null,
                'agent_name'       => $agent['name'] ?? null,
                'agent_ip'         => $agent['ip'] ?? null,

                // Manager
                'manager_name'     => $manager['name'] ?? null,

                // Network (from data object)
                'src_ip'           => $data['srcip'] ?? $data['src_ip'] ?? null,
                'src_port'         => $data['srcport'] ?? $data['src_port'] ?? null,
                'dst_ip'           => $data['dstip'] ?? $data['dst_ip'] ?? $data['dest_ip'] ?? null,
                'dst_port'         => $data['dstport'] ?? $data['dst_port'] ?? $data['dest_port'] ?? null,
                'src_user'         => $data['srcuser'] ?? $data['dstuser'] ?? null,
                'dst_user'         => $data['dstuser'] ?? null,

                // Log
                'full_log'         => $payload['full_log'] ?? null,
                'location'         => $payload['location'] ?? null,
                'decoder_name'     => $decoder['name'] ?? null,

                // Syscheck (FIM)
                'syscheck'         => $syscheck,

                // Full raw JSON — ALL fields captured
                'raw_json'         => $payload,

                'status'           => 'New',
            ]);

            Log::info('Wazuh alert stored', [
                'id' => $alert->id,
                'rule_id' => $alert->rule_id,
                'level' => $alert->rule_level,
                'agent' => $alert->agent_name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Wazuh alert received',
                'alert_id' => $alert->id,
            ], 201);

        } catch (\Exception $e) {
            Log::error('Wazuh webhook error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error processing alert: ' . $e->getMessage(),
            ], 500);
        }
    }
}
