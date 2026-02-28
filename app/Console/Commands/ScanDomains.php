<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ScanDomains extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cti:scan-domains {domain? : The specific domain to scan}';
    protected $description = 'Scan monitored domains (VirusTotal, SSL, DNS)';

    public function handle()
    {
        $targetDomain = $this->argument('domain');
        \Illuminate\Support\Facades\Log::info("ScanDomains Command Triggered. Target: " . ($targetDomain ?? 'ALL'));

        if ($targetDomain) {
            $domains = \App\Models\Asset::domains()->where('name', $targetDomain)->get();
            if ($domains->isEmpty()) {
                $this->error("Domain {$targetDomain} not found in monitored assets.");
                return 1;
            }
        } else {
            $domains = \App\Models\Asset::domains()->get();
        }
        $apiKey = env('VT_API_KEY');

        if (!$apiKey) {
            $this->error('VirusTotal API Key not found in .env (VT_API_KEY)');
            return 1;
        }

        $this->info("Scanning " . $domains->count() . " domains...");

        foreach ($domains as $domain) {
            $this->line("Scanning: {$domain->name}");
            
            try {
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'x-apikey' => $apiKey
                ])->get("https://www.virustotal.com/api/v3/domains/{$domain->name}");

                if ($response->successful()) {
                    $data = $response->json('data');
                    $attrs = $data['attributes'];
                    $stats = $attrs['last_analysis_stats'];
                    
                    // Reputation Score (Custom Calc or rely on malicious count)
                    // VT v3 doesn't give a single integer "reputation" like v2 IP, but we can infer.
                    // Let's use malicious count as negative reputation for now, or just store 0 if clean.
                    $reputation = $stats['malicious'] > 0 ? -($stats['malicious'] * 10) : 100;

                    // Store Log
                    \App\Models\DomainScanLog::create([
                        'asset_id' => $domain->id,
                        'scan_date' => now(),
                        'reputation_score' => $reputation,
                        'vt_stats' => $stats,
                        'permalink' => "https://www.virustotal.com/gui/domain/{$domain->name}"
                    ]);

                    $this->info("✔ Scanned {$domain->name} (Malicious: {$stats['malicious']})");

                    // Create Incident if Malicious
                    if ($stats['malicious'] > 0) {
                        \App\Models\Incident::create([
                            'title' => "Malicious Domain Detected: {$domain->name}",
                            'severity' => 'Critical',
                            'status' => 'Open',
                            'description' => "VirusTotal detected {$stats['malicious']} malicious flags for monitored domain {$domain->name}.",
                            'source_type' => \App\Models\DomainScanLog::class,
                            'source_id' => $domain->latestScan->id ?? 0,
                            'metadata' => $stats
                        ]);
                        $this->warn("⚠ Incident Created!");
                    }

                } else {
                    $this->error("✘ Failed to scan {$domain->name}: " . $response->body());
                }

            } catch (\Exception $e) {
                $this->error("✘ Error scanning {$domain->name}: " . $e->getMessage());
            }

            // Rate Limit Prevention (VT Free Tier: 4 req/min) 
            // 15 seconds sleep
            
            // --- NEW: SSL & DNS Checks (No Rate Limit Needed) ---
            $this->checkSSL($domain);
            $this->checkDNS($domain);

            // --- NEW: Ransomware Check ---
            $this->checkRansomware($domain);

            // Rate Limit Prevention (VT Free Tier: 4 req/min) 
            // 15 seconds sleep ONLY if scanning multiple domains and not the last one
            if ((!isset($targetDomain) || !$targetDomain) && $domain->id !== $domains->last()->id) {
                sleep(15);
            }
        }

        $this->info("Scan complete.");
    }

    private function checkRansomware($domain)
    {
        $this->line("Checking Ransomware Live for {$domain->name}...");
        $apiKey = env('RANSOMWARE_LIVE_API_KEY', 'bd446354-2877-42d9-91e1-23784c3e590e'); // Default key from user request if env missing

        try {
            $response = \Illuminate\Support\Facades\Http::get("https://api-pro.ransomware.live/victims/search", [
                'q' => $domain->name,
                'order' => 'discovered'
            ]);

            // Note: API requires headers usually, but user provided example uses headers.
            // Let's retry with headers if the simple get above might miss them, 
            // but Http::get with query params is cleaner. 
            // Actually user example uses X-API-KEY header.
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'accept' => 'application/json',
                'X-API-KEY' => $apiKey
            ])->get("https://api-pro.ransomware.live/victims/search", [
                'q' => $domain->name
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $victims = $data['victims'] ?? [];

                if (count($victims) > 0) {
                    $this->warn("⚠ RANSOMWARE VICTIM FOUND: " . count($victims));

                    foreach ($victims as $victim) {
                        // Store Victim Data
                        \App\Models\RansomwareVictim::updateOrCreate(
                            [
                                'asset_id' => $domain->id, 
                                'group_name' => $victim['group_name'],
                                'published_at' => $victim['published'] ?? null
                            ],
                            [
                                'post_title' => $victim['post_title'],
                                'description' => \Illuminate\Support\Str::limit($victim['description'] ?? '', 500),
                                'discovered_at' => $victim['discovered'] ?? null,
                                'post_url' => $victim['post_url'] ?? null,
                                'screenshot_url' => $victim['screenshot'] ?? null,
                                'country' => $victim['country'] ?? null,
                                'activity' => $victim['activity'] ?? null,
                                'infostealer_data' => $victim['infostealer'] ?? null,
                            ]
                        );

                        // Create Critical Incident
                        \App\Models\Incident::firstOrCreate(
                            [
                                'title' => "Ransomware Victim Alert: {$domain->name}",
                                'source_type' => \App\Models\RansomwareVictim::class,
                                'description' => "Domain listed on {$victim['group_name']} leak site.",
                            ],
                            [
                                'severity' => 'Critical',
                                'status' => 'Open',
                                'source_id' => $domain->id, // Linking to Asset ID primarily, or find the ID above
                                'metadata' => $victim
                            ]
                        );
                    }
                } else {
                    $this->info("✔ clean (No ransomware records)");
                }

            } else {
                $this->error("✘ Ransomware API Error: " . $response->status());
            }

        } catch (\Exception $e) {
            $this->error("✘ Ransomware Scan Error: " . $e->getMessage());
        }
    }

    private function checkSSL($domain)
    {
        $this->line("Checking SSL for {$domain->name}...");
        try {
            $url = "ssl://{$domain->name}:443";
            $context = stream_context_create(["ssl" => ["capture_peer_cert" => true]]);
            $client = @stream_socket_client($url, $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $context);

            if ($client) {
                $params = stream_context_get_params($client);
                $cert = openssl_x509_parse($params["options"]["ssl"]["peer_certificate"]);
                
                $validFrom = date('Y-m-d H:i:s', $cert['validFrom_time_t']);
                $validTo = date('Y-m-d H:i:s', $cert['validTo_time_t']);
                $daysRemaining = (int) now()->diffInDays(\Carbon\Carbon::parse($validTo), false);

                \App\Models\DomainSslStatus::updateOrCreate(
                    ['asset_id' => $domain->id],
                    [
                        'issuer' => $cert['issuer']['O'] ?? $cert['issuer']['CN'] ?? 'Unknown',
                        'subject' => $cert['subject']['CN'] ?? 'Unknown',
                        'valid_from' => $validFrom,
                        'valid_until' => $validTo,
                        'is_valid' => $daysRemaining > 0,
                        'days_remaining' => $daysRemaining
                    ]
                );

                if ($daysRemaining < 30) {
                    \App\Models\Incident::create([
                        'title' => "SSL Expiry Warning: {$domain->name}",
                        'severity' => 'High',
                        'status' => 'Open',
                        'description' => "SSL Certificate for {$domain->name} expires in {$daysRemaining} days.",
                        'source_type' => \App\Models\DomainSslStatus::class,
                        'source_id' => $domain->sslStatus->id ?? 0,
                    ]);
                }
                $this->info("✔ SSL Valid ({$daysRemaining} days left)");
            } else {
                $this->error("✘ SSL Connect Failed");
            }
        } catch (\Exception $e) {
            $this->error("✘ SSL Check Error: " . $e->getMessage());
        }
    }

    private function checkDNS($domain)
    {
        $this->line("Checking DNS for {$domain->name}...");
        $records = dns_get_record($domain->name, DNS_A + DNS_MX + DNS_NS);
        
        foreach ($records as $record) {
            $type = $record['type'];
            $value = $record['ip'] ?? $record['target'] ?? $record['mname'] ?? 'N/A';
            $hash = md5($type . $value);

            // Check if record exists
            $exists = \App\Models\DomainDnsRecord::where('asset_id', $domain->id)
                ->where('record_type', $type)
                ->where('value', $value)
                ->exists();

            if (!$exists) {
                // New or Changed Record
                \App\Models\DomainDnsRecord::create([
                    'asset_id' => $domain->id,
                    'record_type' => $type,
                    'value' => $value,
                    'hash' => $hash
                ]);
                $this->info("  + Found {$type} Record: {$value}");
            }
        }
    }
}
