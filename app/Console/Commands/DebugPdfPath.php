<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SecurityReport;
use Illuminate\Support\Facades\Storage;

class DebugPdfPath extends Command
{
    protected $signature = 'debug:pdf-path';
    protected $description = 'Debug PDF path resolution';

    public function handle()
    {
        $log = [];
        $log[] = "Starting Debug...";

        $report = SecurityReport::find(27);
        if (!$report) {
            $log[] = "Report 27 not found.";
            file_put_contents('debug_results_v2.txt', implode("\n", $log));
            return;
        }

        $artifacts = $report->summary_json['forensics']['artifacts'] ?? [];
        if (empty($artifacts)) {
            $log[] = "No artifacts found.";
            file_put_contents('debug_results_v2.txt', implode("\n", $log));
            return;
        }

        $targetName = 'Screenshot 2025-06-18 063842.png';
        $found = null;
        foreach ($artifacts as $a) {
            if ($a['name'] == $targetName) {
                $found = $a;
                break;
            }
        }

        if (!$found) {
            $log[] = "Target artifact not found. Using first.";
            $found = $artifacts[0];
        }

        $log[] = "--- Artifact Info ---";
        $log[] = "Name: " . $found['name'];
        $log[] = "Path: " . $found['path'];
        $log[] = "Hex Path: " . bin2hex($found['path']);

        // Check 1: Storage Path
        $p1 = storage_path('app/public/' . $found['path']);
        $log[] = "\n--- Storage Path Check ---";
        $log[] = "Path: $p1";
        $log[] = "Exists: " . (file_exists($p1) ? "YES" : "NO");
        
        // Check 2: Public Path
        $p2 = public_path('storage/' . $found['path']);
        $log[] = "\n--- Public Path Check ---";
        $log[] = "Path: $p2";
        $log[] = "Exists: " . (file_exists($p2) ? "YES" : "NO");

        // Check 3: Raw scan
        $log[] = "\n--- Directory Scan ---";
        $relativeDir = dirname($found['path']); // removing filename
        // If path is reports/27/artifacts/file.png -> reports/27/artifacts
        // If path is public/reports/27/artifacts/file.png -> public/reports/27/artifacts
        
        $absDir =storage_path('app/public/' . $relativeDir);
        $log[] = "Scanning: $absDir";
        
        if (is_dir($absDir)) {
            $files = scandir($absDir);
            foreach ($files as $f) {
                if ($f == '.' || $f == '..') continue;
                $log[] = " - $f";
                if (strpos($f, 'Screenshot') !== false) {
                     $log[] = "   Match check: " . ($f == basename($found['path']) ? "EXACT MATCH" : "NO MATCH");
                }
            }
        } else {
            $log[] = "Directory DOES NOT EXIST.";
             // Check parent
             $parent = dirname($absDir);
             $log[] = "Checking parent: $parent";
             if (is_dir($parent)) {
                 $log[] = "Parent exists. Content:";
                 $files = scandir($parent);
                 foreach ($files as $f) {
                    if($f=='.'||$f=='..')continue;
                    $log[] = " - $f";
                 }
             }
        }
        
        file_put_contents('debug_results_v2.txt', implode("\n", $log));
        $this->info("Debug log written to debug_results_v2.txt");
    }
}
