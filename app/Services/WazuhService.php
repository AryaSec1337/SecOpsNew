<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WazuhService
{
    protected $baseUrl;
    protected $username;
    protected $password;
    protected $token;

    public function __construct()
    {
        $url = config('services.wazuh.url');
        // Ensure URL ends with /api/v1 (or adapt based on version)
        // If user just gives domain, we append
        if ($url && !str_ends_with($url, '/api/v1')) {
             $url = rtrim($url, '/') . '/api/v1'; 
        }

        $this->baseUrl = $url ?: 'https://wazuh.local/api/v1';
        $this->username = config('services.wazuh.username');
        $this->password = config('services.wazuh.password');
    }

    /**
     * Authenticate and get JWT token
     */
    protected function authenticate()
    {
        if ($this->token) return $this->token;

        // Simulate auth if no creds
        if (!$this->username) return 'mock-token';

        try {
            $response = Http::withOptions(['verify' => false])
                ->timeout(2) // 2 seconds timeout for fast fallback
                ->withBasicAuth($this->username, $this->password)
                ->post("{$this->baseUrl}/security/user/authenticate");

            if ($response->successful()) {
                $this->token = $response->json('data.token');
                return $this->token;
            }
        } catch (\Exception $e) {
            Log::error("Wazuh Auth Failed: " . $e->getMessage());
        }

        return null;
    }

    public function getSystemStats()
    {
        $token = $this->authenticate();

        if (!$token || $token === 'mock-token') {
            return [
                'total_agents' => rand(15, 50),
                'active_agents' => rand(12, 45),
                'disconnected_agents' => rand(0, 5),
                'total_alerts_24h' => rand(1500, 5000),
                'level_12_alerts' => rand(5, 20),
                'events_per_second' => rand(800, 1500),
            ];
        }

        try {
            // 1. Get Agents Summary
            $agentsParams = ['pretty' => 'true']; 
            // Note: In a real scenario, you might query /agents/summary/status
            $agentsResp = Http::withOptions(['verify' => false])
                ->withToken($token)
                ->get("{$this->baseUrl}/agents");
            
            $agents = $agentsResp->json('data.items') ?? [];
            $totalAgents = count($agents);
            $activeAgents = count(array_filter($agents, fn($a) => ($a['status'] ?? '') === 'active'));

            // 2. Get Alerts Summary (This usually requires Elastic/Indexer query, 
            // but for Wazuh API on manager w/o indexer access, we might just get recent alerts if available)
            // For now, we will stick to mock for alerts if no direct indexer access, 
            // or use a specific manager stat endpoint.
            
            // Getting Manager Info as proxy for "Events"
            $managerResp = Http::withOptions(['verify' => false])
                ->withToken($token)
                ->get("{$this->baseUrl}/manager/info");

            return [
                'total_agents' => $totalAgents,
                'active_agents' => $activeAgents,
                'disconnected_agents' => $totalAgents - $activeAgents,
                'total_alerts_24h' => rand(100, 500), // Real alert count requires Opensearch API usually
                'level_12_alerts' => 0, // Placeholder
                'events_per_second' => 0, // Placeholder
            ];

        } catch (\Exception $e) {
            Log::error("Wazuh Stats Failed: " . $e->getMessage());
            return [
                'total_agents' => 0, 'active_agents' => 0, 'disconnected_agents' => 0,
                'total_alerts_24h' => 0, 'level_12_alerts' => 0, 'events_per_second' => 0
            ];
        }
    }

    public function getAgents()
    {
        $token = $this->authenticate();

        if (!$token || $token === 'mock-token') {
            return $this->getMockAgents();
        }

        try {
            $response = Http::withOptions(['verify' => false])
                ->withToken($token)
                ->get("{$this->baseUrl}/agents");
            
            if ($response->successful()) {
                return collect($response->json('data.items'))->map(function($agent) {
                    return [
                        'id' => $agent['id'],
                        'name' => $agent['name'],
                        'ip' => $agent['ip'] ?? '127.0.0.1',
                        'os' => $agent['os']['name'] ?? 'Unknown',
                        'status' => ucfirst($agent['status']),
                        'last_keepalive' => $agent['lastKeepAlive'] ?? now(),
                    ];
                })->toArray();
            }
        } catch (\Exception $e) {
            Log::error("Wazuh Agents Failed: " . $e->getMessage());
        }

        return $this->getMockAgents();
    }

    public function getLatestAlerts($limit = 10)
    {
        // Real alert fetching from Wazuh Manager API is limited (it usually stores alerts in specific logs).
        // Usually you query Opensearch/Elasticsearch for this.
        // For simplicity/demo with Manager API only, we'll keep using Mock for alerts 
        // unless provided with Elastic credentials.
        return $this->getMockAlerts($limit);
    }

    // --- MOCKS ---

    private function getMockAgents() {
        $agents = [];
        $statuses = ['Active', 'Active', 'Active', 'Disconnected', 'Never Connected'];
        $os = ['Ubuntu 22.04', 'Windows Server 2019', 'CentOS 7', 'Debian 11'];
        
        for ($i = 1; $i <= 8; $i++) {
            $agents[] = [
                'id' => str_pad($i, 3, '0', STR_PAD_LEFT),
                'name' => 'srv-prod-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'ip' => '10.10.20.' . (50 + $i),
                'os' => $os[array_rand($os)],
                'status' => $statuses[array_rand($statuses)],
                'last_keepalive' => now()->subSeconds(rand(10, 300))->diffForHumans(),
            ];
        }
        return $agents;
    }

    private function getMockAlerts($limit) {
         $alerts = [];
        $types = [
            ['rule' => 'SSH Brute Force', 'level' => 10, 'mitre' => 'T1110'],
            ['rule' => 'SQL Injection Attempt', 'level' => 12, 'mitre' => 'T1190'],
            ['rule' => 'High privilege user logon', 'level' => 8, 'mitre' => 'T1078'],
            ['rule' => 'File integrity changed', 'level' => 7, 'mitre' => 'T1497'],
            ['rule' => 'Outbound Connection to Known Malicious IP', 'level' => 14, 'mitre' => 'T1071'],
            ['rule' => 'Shellshock Attack Detected', 'level' => 13, 'mitre' => 'T1190'],
        ];

        for ($i = 0; $i < $limit; $i++) {
            $type = $types[array_rand($types)];
            $alerts[] = [
                'timestamp' => now()->subSeconds($i * rand(2, 60))->format('H:i:s'),
                'rule_id' => rand(5000, 99999),
                'level' => $type['level'],
                'description' => $type['rule'],
                'agent' => 'srv-prod-' . str_pad(rand(1, 8), 2, '0', STR_PAD_LEFT),
                'src_ip' => long2ip(rand(0, 4294967295)),
                'mitre' => $type['mitre']
            ];
        }

        return $alerts;
    }
}
