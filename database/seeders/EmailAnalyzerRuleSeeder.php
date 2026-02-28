<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailAnalyzerRule;

class EmailAnalyzerRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            [
                'name' => 'Suspicious Subject Keywords',
                'pattern' => '/(urgent|verify|account|suspended|password|bank|winner|lottery|invoice|payment|segera|blokir|hadiah)/i',
                'score' => 15,
                'description' => 'Detects common effective words used in phishing subjects.',
                'is_active' => true,
            ],
            [
                'name' => 'Cryptocurrency Spam',
                'pattern' => '/(bitcoin|crypto|wallet|btc|eth|usdt|binance|coinbase)/i',
                'score' => 20,
                'description' => 'Detects unsolicited cryptocurrency related terms.',
                'is_active' => true,
            ],
            [
                'name' => 'Bulk Mailer Tool (Supmailer)',
                'pattern' => '/X-Mailer:.*supmailer/i',
                'score' => 30,
                'description' => 'Detects usage of Supmailer, a common tool for spam/phishing.',
                'is_active' => true,
            ],
            [
                'name' => 'PHP Script Mailer',
                'pattern' => '/X-Mailer:.*(php|script)/i',
                'score' => 15,
                'description' => 'Detects emails sent via raw PHP scripts instead of proper mail servers.',
                'is_active' => true,
            ],
            [
                'name' => 'Financial Scam / 419',
                'pattern' => '/(inheritance|beneficiary|funds|wire transfer|million dollars|western union|money gram)/i',
                'score' => 25,
                'description' => 'Common keywords in Nigerian Prince / 419 scams.',
                'is_active' => true,
            ],
            [
                'name' => 'Generic Phishing Alert',
                'pattern' => '/(please login|verify your identity|unauthorized access|security alert|account limited)/i',
                'score' => 20,
                'description' => 'Phrases used to scare users into clicking malicious links.',
                'is_active' => true,
            ],
            [
                'name' => 'Free Email Corporate Impersonation',
                'pattern' => '/From:.*(gmail\.com|yahoo\.com|hotmail\.com).*(ceo|director|hrd|payroll|invoice|admin)/i',
                'score' => 30,
                'description' => 'Detects corporate titles sending from free email providers (BEC/CEO Fraud).',
                'is_active' => true,
            ]
        ];

        foreach ($rules as $rule) {
            EmailAnalyzerRule::updateOrCreate(['name' => $rule['name']], $rule);
        }
    }
}
