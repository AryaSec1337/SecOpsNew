<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\ActivityLog;
use App\Models\FileIntegrityLog;

class ActivityLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Activity Logs (Web Server)
        ActivityLog::truncate();
        $this->seedActivityLogs();

        // 2. Seed FIM Logs (File Integrity)
        FileIntegrityLog::truncate();
        $this->seedFimLogs();
    }

    private function seedActivityLogs() {
        $logs = [];
        $status_codes = [200, 200, 200, 404, 500, 403, 200, 302, 200, 404];
        $methods = ['GET', 'POST', 'PUT', 'DELETE', 'GET', 'GET'];
        $paths = ['/login', '/dashboard', '/api/users', '/images/logo.png', '/config.php', '/admin', '/'];
        $user_agents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (HTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.107 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Gecko/20100101 Firefox/89.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15',
            'Mozilla/5.0 (Linux; Android 10; SM-G960U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.114 Mobile Safari/537.36'
        ];
        $agent_names = ['Web-Srv-01', 'DB-Srv-01', 'App-Srv-02', 'Gateway-01', 'Win-Workstation-05'];
        $log_files = ['/var/log/nginx/access.log', '/var/log/apache2/access.log', '/var/log/syslog', 'C:\Windows\System32\Winevt\Logs\System.evtx'];

        for ($i = 0; $i < 50; $i++) {
            $ua = $user_agents[array_rand($user_agents)];
            $os = 'Unknown';
            if (Str::contains($ua, 'Windows')) $os = 'Windows';
            elseif (Str::contains($ua, 'Linux') || Str::contains($ua, 'Android')) $os = 'Linux';
            elseif (Str::contains($ua, 'Mac')) $os = 'MacOS';

            $logs[] = [
                'timestamp' => now()->subMinutes(rand(10, 200)),
                'ip_address' => '192.168.1.' . rand(1, 255),
                'agent_name' => $agent_names[array_rand($agent_names)],
                'agent_ip' => '10.0.0.' . rand(1, 50),
                'method' => $methods[array_rand($methods)],
                'path' => $paths[array_rand($paths)],
                'status_code' => $status_codes[array_rand($status_codes)],
                'size' => rand(500, 5000),
                'user_agent' => $ua,
                'os' => $os,
                'log_file' => $log_files[array_rand($log_files)],
                'details' => null, // Normal log
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $attacks = [
            [
                'name' => 'SQL Injection Attempt',
                'rule_id' => 'SQLI-001',
                'description' => 'Detected UNION SELECT in query parameters',
                'severity' => 'High'
            ],
            [
                'name' => 'XSS Attack',
                'rule_id' => 'XSS-002',
                'description' => 'Detected <script> tag in POST body',
                'severity' => 'Medium'
            ],
            [
                'name' => 'Directory Traversal',
                'rule_id' => 'TRAV-003',
                'description' => 'Detected ../../ in URL path',
                'severity' => 'High'
            ]
        ];

        for ($j = 0; $j < 10; $j++) {
             $attack = $attacks[array_rand($attacks)];
             $logs[] = [
                'timestamp' => now()->subMinutes(rand(1, 30)),
                'ip_address' => '45.10.22.' . rand(1, 255) . ' (Malicious)',
                'agent_name' => 'Web-Srv-01',
                'agent_ip' => '10.0.0.10',
                'method' => 'POST',
                'path' => '/login',
                'status_code' => 403,
                'size' => rand(500, 1500),
                'user_agent' => 'Mozilla/5.0 (Kali Linux)',
                'os' => 'Linux',
                'log_file' => '/var/log/nginx/error.log',
                'details' => json_encode($attack),
                'created_at' => now(),
                'updated_at' => now(),
             ];
        }

        // Add Backdoor Webshell Incident
        $logs[] = [
            'timestamp' => now()->subMinutes(2),
            'ip_address' => '103.20.10.5 (Attacker)',
            'agent_name' => 'Web-Srv-01',
            'agent_ip' => '10.0.0.10',
            'method' => 'POST',
            'path' => '/uploads/shell.php?cmd=whoami',
            'status_code' => 200,
            'size' => 450,
            'user_agent' => 'Python-urllib/2.7',
            'os' => 'Linux',
            'log_file' => '/var/log/apache2/access.log',
            'details' => json_encode([
                'name' => 'Webshell Detected',
                'rule_id' => 'BKDR-009',
                'description' => 'Known webshell signature detected in request path',
                'severity' => 'Critical'
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        ActivityLog::insert($logs);
    }

    private function seedFimLogs() {
        $fim_logs = [];
        $files = [
            '/etc/passwd', '/etc/shadow', '/var/www/html/index.php', 
            '/etc/nginx/nginx.conf', '/usr/bin/sudo', 'C:\Windows\System32\drivers\etc\hosts'
        ];
        $users = ['root', 'www-data', 'admin', 'ubuntu', 'SYSTEM'];
        $processes = ['vim', 'nano', 'touch', 'gcc', 'apt-get', 'powershell.exe'];

        for ($i = 0; $i < 20; $i++) {
            $change = ['Modified', 'Created', 'Deleted'][rand(0, 2)];
            $severity = 'Medium';
            if ($change === 'Deleted' || Str::contains($files[rand(0,5)], '/etc/shadow')) $severity = 'Critical';
            
            $fim_logs[] = [
                'file_path' => $files[array_rand($files)],
                'change_type' => $change,
                'process_name' => $processes[array_rand($processes)],
                'user' => $users[array_rand($users)],
                'hash_before' => md5(rand()),
                'hash_after' => md5(rand()),
                'severity' => $severity,
                'detected_at' => now()->subMinutes(rand(5, 500)),
                'details' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Add Backdoor Creation Incident
        $fim_logs[] = [
            'file_path' => '/var/www/html/uploads/shell.php',
            'change_type' => 'Created',
            'process_name' => 'www-data (apache2)',
            'user' => 'www-data',
            'hash_before' => null,
            'hash_after' => 'd41d8cd98f00b204e9800998ecf8427e', // malicious hash
            'severity' => 'Critical',
            'detected_at' => now()->subMinutes(2),
            'details' => json_encode(['malware_family' => 'PHP/C99Shell']),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        FileIntegrityLog::insert($fim_logs);
    }
}
