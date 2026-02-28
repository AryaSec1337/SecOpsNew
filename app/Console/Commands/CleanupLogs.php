<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;
use App\Models\Backup;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CleanupLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siem:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune old logs based on retention policy settings (with Auto Backup)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting Database Cleanup Process...");
        Log::info("Starting Database Cleanup Process...");

        // --- 1. Perform Backup ---
        $this->performBackup();

        // --- 2. Get Retention Settings ---
        $retentionActivity = Setting::where('key', 'retention_activity_logs')->value('value') ?? 30;
        $retentionFIM = Setting::where('key', 'retention_fim_logs')->value('value') ?? 30;
        $retentionIPs = Setting::where('key', 'retention_blocked_ips')->value('value') ?? 90;
        $retentionIncidents = Setting::where('key', 'retention_incidents')->value('value') ?? 365;

        // --- 3. Prune Data ---
        
        // Activity Logs
        $countActivity = DB::table('activity_logs')
            ->where('created_at', '<', Carbon::now()->subDays((int)$retentionActivity))
            ->delete();
        $this->info("Deleted $countActivity old Activity Logs (> $retentionActivity days)");
        
        // FIM Logs
        $countFIM = DB::table('file_integrity_logs')
            ->where('created_at', '<', Carbon::now()->subDays((int)$retentionFIM))
            ->delete();
        $this->info("Deleted $countFIM old FIM Logs (> $retentionFIM days)");
        
        // Blocked IPs
        $countIPs = DB::table('blocked_ips')
            ->where('created_at', '<', Carbon::now()->subDays((int)$retentionIPs))
            ->delete();
        $this->info("Pruned $countIPs old Blocked IP records (> $retentionIPs days)");
        
        // Incidents
        $countIncidents = DB::table('incidents')
            ->where('created_at', '<', Carbon::now()->subDays((int)$retentionIncidents))
            ->delete();
        $this->info("Archived/Deleted $countIncidents old Incidents (> $retentionIncidents days)");

        $this->info("Cleanup Complete.");
        Log::info("Database Cleanup Complete.");
    }

    private function performBackup()
    {
        $this->info("Initiating Auto-Backup...");
        
        try {
            // Configuration
            $dbName = env('DB_DATABASE', 'skripsi_new');
            $dbUser = env('DB_USERNAME', 'postgres');
            $dbHost = env('DB_HOST', '127.0.0.1');
            $dbPort = env('DB_PORT', '5432');
            // Assuming PGPASSWORD is set in env or .pgpass, or trusted local connection. 
            // For Windows without interaction, setting PGPASSWORD env var for the process is easiest.
            $dbPassword = env('DB_PASSWORD', '');

            // Ensure backup directory exists
            $backupDir = storage_path('app/backups');
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            $filename = 'backup_auto_' . date('Y-m-d_H-i-s') . '.sql';
            $filePath = $backupDir . '/' . $filename;
            
            // Path to pg_dump (Auto-detected previously or fallback)
            // Hardcoded based on auto-detection for user environment
            $pgDumpPath = '"C:\\Program Files\\PostgreSQL\\18\\bin\\pg_dump.exe"'; 

            // Command Construction
            // Note: On Windows PowerShell, setting env var inline is different. 
            // We'll use putenv in PHP.
            putenv("PGPASSWORD=$dbPassword");
            
            $command = "$pgDumpPath -h $dbHost -p $dbPort -U $dbUser -F c -b -v -f \"$filePath\" $dbName";

            $output = [];
            $returnVar = 0;
            
            // Execute
            exec($command . ' 2>&1', $output, $returnVar);

            if ($returnVar === 0 && file_exists($filePath)) {
                $size = filesize($filePath);
                
                // Record in DB
                Backup::create([
                    'filename' => $filename,
                    'path' => 'backups/' . $filename, // Relative for Storage facade
                    'size_bytes' => $size,
                    'type' => 'retention_auto'
                ]);

                $this->info("Backup Success: $filename (" . number_format($size / 1024, 2) . " KB)");
                Log::info("Auto-Backup Success: $filename");
            } else {
                $this->error("Backup Failed. Return Code: $returnVar");
                Log::error("Backup Failed. Output: " . implode("\n", $output));
                $this->error("Output: " . implode("\n", $output));
            }

        } catch (\Exception $e) {
            $this->error("Backup Error: " . $e->getMessage());
            Log::error("Backup Error: " . $e->getMessage());
        }
    }
}
