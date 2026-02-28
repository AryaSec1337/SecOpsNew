<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ThreatIntelligenceService
{
    private $keys;

    public function __construct()
    {
        $this->keys = [
            'ipinfo' => config('services.ipinfo.token'),
            'greynoise' => config('services.greynoise.key'),
            'alienvault' => config('services.alienvault.key'),
            'virustotal' => config('services.virustotal.api_key'),
            'abuseipdb' => config('services.abuseipdb.key'),
        ];
    }

    /**
     * Analyze an IP address across all available providers
     */
    public function analyzeIp($ip, \Closure $onProgress = null)
    {
        // Helper to report progress safely
        $report = function($step, $status, $msg) use ($onProgress) {
            if ($onProgress) $onProgress($step, $status, $msg);
        };

        // Skip private/local IPs
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
             $report('Validation', 'skipped', "Skipped private IP: $ip");
            return [
                'ip' => $ip,
                'classification' => 'Internal/Private',
                'risk_score' => 0,
                'details' => ['message' => 'Internal IP address ignored']
            ];
        }

        $report('IPinfo', 'running', "Geolocating $ip...");
        $geo = $this->getIpInfo($ip);
        if(!$geo) $report('IPinfo', 'error', "Geo lookup failed");
        else $report('IPinfo', 'success', "Located in " . ($geo['country'] ?? 'Unknown'));

        $report('GreyNoise', 'running', "Checking noise levels...");
        $noise = $this->getGreyNoise($ip);
        $report('GreyNoise', 'success', "Noise check complete");

        $report('AlienVault', 'running', "Querying OTX pulses...");
        $otx = $this->getAlienVault($ip);
        $report('AlienVault', 'success', "OTX check complete");

        $report('VirusTotal', 'running', "Scanning repetition...");
        $vt = $this->getVirusTotal($ip);
        $report('VirusTotal', 'success', "VT check complete");

        $report('AbuseIPDB', 'running', "Checking community reports...");
        $abuse = $this->getAbuseIpDb($ip);
        $report('AbuseIPDB', 'success', "Reputation check complete");

        $results = [
            'ip' => $ip,
            'geo' => $geo,
            'greynoise' => $noise,
            'alienvault' => $otx,
            'virustotal' => $vt,
            'abuseipdb' => $abuse,
        ];

        $results['risk_score'] = $this->calculateRiskScore($results);
        
        return $results;
    }

    private function getIpInfo($ip)
    {
        if (!$this->keys['ipinfo']) return null;
        try {
            return Http::get("https://ipinfo.io/{$ip}?token={$this->keys['ipinfo']}")->json();
        } catch (\Exception $e) {
            Log::error("IPinfo Error: " . $e->getMessage());
            return null;
        }
    }

    private function getGreyNoise($ip)
    {
        if (!$this->keys['greynoise']) return null;
        
        $data = null;
        $endpointUsed = 'enterprise';

        // 1. Try Enterprise / Context Endpoint First (User Preference)
        try {
            $response = Http::withHeaders(['key' => $this->keys['greynoise']])
                ->get("https://api.greynoise.io/v3/ip/{$ip}");
            
            if ($response->successful()) {
                $data = $response->json();
            } else {
                throw new \Exception("Enterprise Endpoint Failed");
            }
        } catch (\Exception $e) {
            // 2. Fallback to Community Endpoint
            $endpointUsed = 'community';
            try {
                $response = Http::withHeaders(['key' => $this->keys['greynoise']])
                    ->get("https://api.greynoise.io/v3/community/{$ip}");
                
                if ($response->successful()) {
                    $data = $response->json();
                }
            } catch (\Exception $ex) {
                return null;
            }
        }

        if (!$data) return null;

        // Check for "IP not observed" message (Common in Community API)
        if (isset($data['message']) && $data['message'] === 'IP not observed') {
            return null;
        }

        // Normalize Data Structure
        $normalized = [
            'classification' => 'unknown',
            'actor' => 'unknown',
            'tags' => [],
            'raw' => $data
        ];

        // Format A: Enterprise / Context API
        if (isset($data['internet_scanner_intelligence'])) {
            $isi = $data['internet_scanner_intelligence'];
            $normalized['classification'] = $isi['classification'] ?? 'unknown';
            $normalized['actor'] = $isi['actor'] ?? 'unknown';
            $normalized['tags'] = array_column($isi['tags'] ?? [], 'name');
        } 
        // Format B: Community API
        elseif (isset($data['classification'])) {
            $normalized['classification'] = $data['classification'];
            $normalized['actor'] = $data['name'] ?? 'unknown'; 
            $normalized['tags'] = $data['tags'] ?? [];
        }
        // Format C: RIOT (Common in Community for benign services)
        elseif (isset($data['riot']) && $data['riot'] === true) {
             $normalized['classification'] = 'benign';
             $normalized['actor'] = $data['name'] ?? 'RIOTA';
             $normalized['tags'] = ['RIOT'];
        }

        // If after all parsing we still have 'unknown', and it's not a noise/riot hit, force null to avoid ugly UI
        if ($normalized['classification'] === 'unknown' && $normalized['actor'] === 'unknown') {
            return null;
        }

        return $normalized;
    }

    private function getAlienVault($ip)
    {
        if (!$this->keys['alienvault']) return null;
        
        try {
            $data = Http::withHeaders(['X-OTX-API-KEY' => $this->keys['alienvault']])
                ->get("https://otx.alienvault.com/api/v1/indicators/IPv4/{$ip}/general")
                ->json();

            if (!$data) return null;

            // Extract useful fields
            $pulses = [];
            $rawPulses = $data['pulse_info']['pulses'] ?? [];
            
            // Limit to Top 3 to prevent API rate limits / slowness
            foreach (array_slice($rawPulses, 0, 3) as $p) {
                $pulseData = [
                    'id' => $p['id'],
                    'name' => $p['name'],
                    'tags' => $p['tags'] ?? [],
                    // Placeholders in case detail fetch fails
                    'description' => '',
                    'malware_families' => [],
                    'targeted_countries' => []
                ];

                // Deep Dive: Fetch full pulse details
                try {
                    $details = Http::withHeaders(['X-OTX-API-KEY' => $this->keys['alienvault']])
                        ->get("https://otx.alienvault.com/api/v1/pulses/{$p['id']}")
                        ->json();

                    if ($details) {
                        $pulseData['description'] = $details['description'] ?? '';
                        $pulseData['malware_families'] = $details['malware_families'] ?? [];
                        $pulseData['targeted_countries'] = $details['targeted_countries'] ?? [];
                        $pulseData['tags'] = $details['tags'] ?? $pulseData['tags'];
                        // Additional Fields requested by User
                        $pulseData['adversary'] = $details['adversary'] ?? '';
                        $pulseData['industries'] = $details['industries'] ?? [];
                        $pulseData['attack_ids'] = $details['attack_ids'] ?? [];
                        $pulseData['references'] = $details['references'] ?? [];
                        $pulseData['author_name'] = $details['author_name'] ?? 'Unknown';
                        $pulseData['created'] = $details['created'] ?? '';
                        $pulseData['modified'] = $details['modified'] ?? '';
                        $pulseData['TLP'] = $details['TLP'] ?? 'white';
                        $pulseData['indicators_count'] = $details['indicators_count'] ?? 0;
                        $pulseData['revision'] = $details['revision'] ?? 1;
                        // Extract sample indicators (Top 10)
                        $pulseData['indicators'] = array_slice($details['indicators'] ?? [], 0, 10);
                    }
                } catch (\Exception $ex) {
                    // Ignore detail fetch errors, keep basic info
                }

                $pulses[] = $pulseData;
            }

            return [
                'reputation' => $data['reputation'] ?? 0,
                'pulse_count' => $data['pulse_info']['count'] ?? 0,
                'pulses' => $pulses,
                'validation' => array_column($data['validation'] ?? [], 'name'),
                'false_positive' => isset($data['false_positive']) ? 'Yes' : 'No',
                // OTX Specific Geo/ASN Data
                'asn' => $data['asn'] ?? 'N/A',
                'geo' => [
                    'city' => $data['city'] ?? null,
                    'country' => $data['country_name'] ?? null,
                ],
                'raw' => $data
            ];

        } catch (\Exception $e) {
            return null;
        }
    }

    private function getVirusTotal($ip)
    {
        if (!$this->keys['virustotal']) return null;
        try {
            $data = Http::withHeaders(['x-apikey' => $this->keys['virustotal']])
                ->get("https://www.virustotal.com/api/v3/ip_addresses/{$ip}")
                ->json('data.attributes');

            return $data ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getAbuseIpDb($ip)
    {
        if (!$this->keys['abuseipdb']) return null;
        try {
            return Http::withHeaders([
                'Key' => $this->keys['abuseipdb'],
                'Accept' => 'application/json'
            ])->get("https://api.abuseipdb.com/api/v2/check", [
                'ipAddress' => $ip,
                'maxAgeInDays' => 90,
                'verbose' => 1
            ])->json('data');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function calculateRiskScore($data)
    {
        $score = 0;

        // VT Malicious Counts
        // VT Malicious Counts
        if (isset($data['virustotal']['last_analysis_stats']['malicious'])) {
            $score += $data['virustotal']['last_analysis_stats']['malicious'] * 10;
        }

        // AbuseIPDB Confidence
        if (isset($data['abuseipdb']['abuseConfidenceScore'])) {
            $score += $data['abuseipdb']['abuseConfidenceScore']; // Max 100
        }

        // GreyNoise (If classified as malicious/noise)
        if (isset($data['greynoise']['classification']) && $data['greynoise']['classification'] === 'malicious') {
             $score += 50;
        }

        // Cap at 100 for normalization logic, but can go higher logically
        return min($score, 100);
    }
}
