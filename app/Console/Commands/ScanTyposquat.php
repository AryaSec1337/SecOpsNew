<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ScanTyposquat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cti:scan-typosquat';
    protected $description = 'Scan for potential typosquatting domains';

    public function handle()
    {
        $domains = \App\Models\Asset::domains()->get();

        foreach ($domains as $domain) {
            $this->info("Generating variations for: {$domain->name}");
            
            $variations = $this->generatePermutations($domain->name);
            $bar = $this->output->createProgressBar(count($variations));
            
            foreach ($variations as $testDomain) {
                // Skip if same as original
                if ($testDomain === $domain->name) continue;

                // Check DNS
                $records = dns_get_record($testDomain, DNS_A + DNS_MX);
                $isRegistered = count($records) > 0;
                $ip = null;
                $mx = null;

                if ($isRegistered) {
                    foreach ($records as $r) {
                        if ($r['type'] === 'A') $ip = $r['ip'];
                        if ($r['type'] === 'MX') $mx = $r['target'];
                    }

                    // Log Findings
                    \App\Models\TyposquatLog::firstOrCreate(
                        ['original_domain' => $domain->name, 'permuted_domain' => $testDomain],
                        [
                            'ip_address' => $ip,
                            'mx_record' => $mx,
                            'is_registered' => true,
                            'scan_date' => now()
                        ]
                    );
                    // $this->warn("\n[!] Found Active Typosquat: $testDomain ($ip)");
                }

                $bar->advance();
            }
            $bar->finish();
            $this->newLine();
        }
    }

    private function generatePermutations($domain)
    {
        $parts = explode('.', $domain);
        $name = $parts[0];
        $tld = implode('.', array_slice($parts, 1));
        
        $variations = [];

        // 1. TLD Swaps
        $tlds = ['com', 'net', 'org', 'co.id', 'id', 'xyz', 'info'];
        foreach ($tlds as $t) {
            if ($t !== $tld) $variations[] = "$name.$t";
        }

        // 2. Omission (goole.com)
        for ($i = 0; $i < strlen($name); $i++) {
            $variations[] = substr($name, 0, $i) . substr($name, $i + 1) . ".$tld";
        }

        // 3. Duplication (gooogle.com)
        for ($i = 0; $i < strlen($name); $i++) {
            $variations[] = substr($name, 0, $i) . $name[$i] . substr($name, $i) . ".$tld";
        }

        // 4. Transposition (goolge.com)
        for ($i = 0; $i < strlen($name) - 1; $i++) {
            $temp = $name;
            $char = $temp[$i];
            $temp[$i] = $temp[$i+1];
            $temp[$i+1] = $char;
            $variations[] = "$temp.$tld";
        }

        return array_unique($variations);
    }
}
