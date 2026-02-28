<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SecOpsAgentService
{
    protected $apiKey;

    public function __construct()
    {
        // API Key is still shared/global for now, or could be per-agent if needed.
        // For now assuming same shared secret or stored in Asset model (future improvement)
        $this->apiKey = env('SECOPS_AGENT_KEY', 'default-secret-key-change-me');
    }

    /**
     * Block an IP address on the remote agent.
     *
     * @param string $targetIp The IP address of the AGENT (where the command is sent)
     * @param string $ipToBlock The IP address to BLOCK
     * @param int|null $port
     * @param string $protocol
     * @return array
     */
    public function blockIp($targetAgentIp, $ipToBlock, $port = null, $protocol = 'tcp')
    {
        // Assume Agent runs on Port 5000 by default
        // In future, Asset model could have 'agent_port' column
        $agentPort = env('SECOPS_AGENT_PORT', 5000);
        $baseUrl = "http://{$targetAgentIp}:{$agentPort}";

        try {
            $payload = [
                'ip_address' => $ipToBlock,
                'protocol' => $protocol,
            ];

            if ($port) {
                $payload['port'] = (int) $port;
            }

            $response = Http::withToken($this->apiKey)
                ->timeout(5) // 5 seconds timeout
                ->post("{$baseUrl}/block-ip", $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'IP blocked successfully on agent.',
                    'data' => $response->json()
                ];
            }

            Log::error("SecOps Agent ({$targetAgentIp}) Error: " . $response->body());
            
            return [
                'success' => false,
                'message' => 'Agent returned error: ' . $response->status(),
                'error' => $response->json()
            ];

        } catch (\Exception $e) {
            Log::error("SecOps Agent ({$targetAgentIp}) Connection Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "Failed to connect to SecOps Agent at {$targetAgentIp}.",
                'error' => $e->getMessage()
            ];
        }
    }
}
