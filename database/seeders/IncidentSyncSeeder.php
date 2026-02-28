<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ActivityLog;
use App\Models\Incident;

class IncidentSyncSeeder extends Seeder
{
    public function run()
    {
        $count = 0;
        ActivityLog::whereNotNull('details')->chunk(100, function($logs) use (&$count) {
            foreach ($logs as $log) {
                $details = $log->details;
                // Check for threat_match key
                $threat = $details['threat_match'] ?? null;
                
                // Backward compatibility: maybe details IS the threat?
                if (!$threat && isset($details['rule_id']) && isset($details['severity'])) {
                     $threat = $details;
                }

                if ($threat) {
                     // Check if an incident already exists for this log
                     $exists = Incident::where('source_type', ActivityLog::class)
                         ->where('source_id', $log->id)
                         ->exists();
                     
                     if (!$exists) {
                         Incident::create([
                            'title' => $threat['name'] ?? 'Detected Threat',
                            'severity' => $threat['severity'] ?? 'High',
                            'status' => 'Open',
                            'description' => $threat['description'] ?? 'No description',
                            'source_type' => ActivityLog::class,
                            'source_id' => $log->id,
                            'metadata' => [
                                'agent_ip' => $log->agent_ip,
                                'client_ip' => $log->ip_address ?? 'Unknown',
                                'rule_id' => $threat['rule_id'] ?? null
                            ]
                         ]);
                         $count++;
                         $this->command->info("Created incident for Log ID: {$log->id}");
                     }
                }
            }
        });
        $this->command->info("Sync Complete. Created {$count} missing incidents.");
    }
}
