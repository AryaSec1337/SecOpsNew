<?php

namespace App\Services;

use App\Models\WebhookFileScan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class FileScanPipelineService
{
    protected $virusTotalService;

    public function __construct(VirusTotalService $virusTotalService)
    {
        $this->virusTotalService = $virusTotalService;
    }

    /**
     * Run the full pipeline and return the scan record.
     */
    public function runPipeline(WebhookFileScan $scanRecord, ?string $filePath): WebhookFileScan
    {
        $timestamps = [];
        $timestamps['start'] = now()->toIso8601String();
        $verdict = 'CLEAN';

        // ===================================================================
        // SEQUENTIAL CASCADE: YARA → ClamAV → VirusTotal
        // Stop as soon as a threat is detected at any stage.
        // ===================================================================

        // --- STAGE 1: YARA ---
        if ($filePath) {
            $timestamps['yara_start'] = now()->toIso8601String();
            $yaraResult = $this->runYara($filePath);
            $timestamps['yara_end'] = now()->toIso8601String();
            
            $scanRecord->yara_result = $yaraResult;

            if (isset($yaraResult['matches']) && count($yaraResult['matches']) > 0) {
                // Classify verdict based on rule risk level
                // High-risk prefixes indicate confirmed malware/threat
                $highRiskPrefixes = [
                    'MALW_', 'RANSOM_', 'RAT_', 'APT_', 'TOOLKIT_', 
                    'WShell_', 'Wshell_', 'EK_', 'Maldoc_', 'POS_', 
                    'CVE', 'EMAIL_', 'Email_', 'GEN_PowerShell',
                    'EXPERIMENTAL_', 'MalConfScan',
                ];

                $hasMaliciousRule = false;
                foreach ($yaraResult['matches'] as $match) {
                    $ruleName = $match['rule'] ?? '';
                    foreach ($highRiskPrefixes as $prefix) {
                        if (str_starts_with($ruleName, $prefix)) {
                            $hasMaliciousRule = true;
                            break 2;
                        }
                    }
                    // Also check tags for high_confidence
                    if (isset($match['tags']) && in_array('high_confidence', $match['tags'])) {
                        $hasMaliciousRule = true;
                        break;
                    }
                }

                if ($hasMaliciousRule) {
                    // HIGH-RISK rule matched → Confirmed malware. STOP pipeline.
                    $verdict = 'MALICIOUS';
                    Log::info('YARA high-risk rule matched. Verdict: MALICIOUS. Skipping ClamAV & VirusTotal.');
                    $scanRecord->clamav_result = ['message' => 'Skipped: Threat already detected by YARA'];
                    $scanRecord->vt_result = ['message' => 'Skipped: Threat already detected by YARA'];
                    $timestamps['end'] = now()->toIso8601String();
                    $scanRecord->verdict = $verdict;
                    $scanRecord->timestamps_stages = $timestamps;
                    $scanRecord->save();
                    return $scanRecord;
                } else {
                    // Only GENERIC rules matched (IsPE32, NET_executable, etc.)
                    // These are informational, not proof of malware.
                    // Mark as SUSPICIOUS but CONTINUE pipeline → let ClamAV & VT confirm.
                    $verdict = 'SUSPICIOUS';
                    Log::info('YARA generic rules matched. Verdict tentative: SUSPICIOUS. Continuing to ClamAV & VirusTotal for deeper analysis.');
                }
            }
        } else {
            $scanRecord->yara_result = ['message' => 'Skipped: No physical file provided'];
        }

        // --- STAGE 2: ClamAV (only if YARA found nothing) ---
        if ($filePath) {
            $timestamps['clamav_start'] = now()->toIso8601String();
            $clamAvResult = $this->runClamAv($filePath);
            $timestamps['clamav_end'] = now()->toIso8601String();
            
            $scanRecord->clamav_result = $clamAvResult;

            if (isset($clamAvResult['infected']) && $clamAvResult['infected'] === true) {
                $verdict = 'MALICIOUS';

                // ClamAV detected something → stop here, skip VT
                Log::info('ClamAV detected threat. Verdict: MALICIOUS. Skipping VirusTotal.');
                $scanRecord->vt_result = ['message' => 'Skipped: Threat already detected by ClamAV'];
                $timestamps['end'] = now()->toIso8601String();
                $scanRecord->verdict = $verdict;
                $scanRecord->timestamps_stages = $timestamps;
                $scanRecord->save();
                return $scanRecord;
            }
        } else {
            $scanRecord->clamav_result = ['message' => 'Skipped: No physical file provided'];
        }

        // --- STAGE 3: VirusTotal (only if YARA & ClamAV found nothing) ---
        $timestamps['vt_start'] = now()->toIso8601String();
        $vtResult = $this->runVirusTotal($scanRecord->sha256);
        $timestamps['vt_end'] = now()->toIso8601String();
        
        $scanRecord->vt_result = $vtResult;

        if ($vtResult) {
            $stats = $vtResult['attributes']['last_analysis_stats'] ?? $vtResult['stats'] ?? [];
            $maliciousCount = $stats['malicious'] ?? 0;

            if ($maliciousCount >= 3) {
                $verdict = 'MALICIOUS';
            } elseif ($maliciousCount >= 1) {
                $verdict = 'SUSPICIOUS';
            }
        }

        $timestamps['end'] = now()->toIso8601String();
        $scanRecord->verdict = $verdict;
        $scanRecord->timestamps_stages = $timestamps;
        $scanRecord->save();

        return $scanRecord;
    }

    /**
     * Run YARA scan using native CLI.
     * Ubuntu: `yara` (apt install yara)
     * Windows: `tools/yaraWindows/yara64.exe`
     */
    protected function runYara(string $filePath): array
    {
        try {
            $rulesDir = storage_path('app/yara_rules');
            
            if (!file_exists($rulesDir)) {
                mkdir($rulesDir, 0755, true);
            }

            $indexYar = $rulesDir . DIRECTORY_SEPARATOR . 'index.yar';
            if (!file_exists($indexYar)) {
                return ['status' => 'error', 'message' => 'index.yar not found in rules directory'];
            }

            // Determine YARA binary based on OS
            if (PHP_OS_FAMILY === 'Windows') {
                $yaraBin = base_path('tools/yaraWindows/yara64.exe');
                if (!file_exists($yaraBin)) {
                    return ['status' => 'error', 'message' => 'yara64.exe not found in tools/yaraWindows/'];
                }
            } else {
                // Linux/Ubuntu: use native `yara` CLI (apt install yara)
                $yaraBin = 'yara';
            }

            // -w = suppress warnings
            $command = [$yaraBin, '-w', $indexYar, $filePath];

            Log::debug('Executing YARA: ' . implode(' ', $command));
            $process = new Process($command);
            $process->setTimeout(120);
            $process->run();

            // Parse plain-text output from yara CLI
            // Format per line: "RuleName FilePath"
            $output = trim($process->getOutput());
            $errorOutput = trim($process->getErrorOutput());

            if ($process->getExitCode() !== 0 && empty($output)) {
                Log::error('YARA Scan Failed: ' . $errorOutput . ' | Exit Code: ' . $process->getExitCode());
                return ['error' => 'Scan failed', 'message' => $errorOutput];
            }

            if (empty($output)) {
                return ['status' => 'success', 'matches' => [], 'match_count' => 0];
            }

            $lines = explode("\n", str_replace("\r", "", $output));
            $matches = [];
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line !== '' && !str_starts_with($line, 'error:')) {
                    $parts = explode(' ', $line);
                    $ruleName = $parts[0] ?? $line;
                    $matches[] = [
                        'rule' => $ruleName,
                        'namespace' => 'default',
                        'tags' => [],
                        'meta' => [],
                        'strings' => []
                    ];
                }
            }

            return ['status' => 'success', 'matches' => $matches, 'match_count' => count($matches)];

        } catch (\Exception $e) {
            Log::error('YARA Scan Exception: ' . $e->getMessage());
            return ['error' => 'Exception', 'message' => $e->getMessage()];
        }
    }

    /**
     * Run ClamAV scan using native CLI.
     * Ubuntu: `clamscan` (apt install clamav)
     * Windows: routed through WSL
     */
    protected function runClamAv(string $filePath): array
    {
        try {
            // Determine ClamAV command based on OS
            if (PHP_OS_FAMILY === 'Windows') {
                $wslFilePath = '/mnt/' . strtolower(substr($filePath, 0, 1)) . str_replace('\\', '/', substr($filePath, 2));
                $wslExe = 'C:\\Windows\\System32\\wsl.exe';
                $cmdString = sprintf('"%s" clamscan --no-summary "%s"', $wslExe, $wslFilePath);
                
                Log::debug('Executing ClamAV via WSL: ' . $cmdString);
                $process = Process::fromShellCommandline($cmdString);
            } else {
                // Linux/Ubuntu: use native `clamscan` CLI (apt install clamav)
                $command = ['clamscan', '--no-summary', $filePath];
                Log::debug('Executing ClamAV: ' . implode(' ', $command));
                $process = new Process($command);
            }

            $process->setTimeout(120);
            $process->run();

            $output = trim($process->getOutput());
            $errorOutput = trim($process->getErrorOutput());
            $exitCode = $process->getExitCode();
            $combinedOutput = trim($output . "\n" . $errorOutput);

            Log::debug('ClamAV result: ' . $combinedOutput . ' | Exit Code: ' . $exitCode);

            // Exit code 0 = No virus found
            // Exit code 1 = Virus(es) found
            // Exit code 2 = Some error(s) occurred
            $hasFOUND = str_contains($combinedOutput, 'FOUND');

            if ($exitCode === 1 || $hasFOUND) {
                return ['infected' => true, 'output' => $combinedOutput ?: 'Virus detected'];
            } elseif ($exitCode === 0 && !$hasFOUND) {
                return ['infected' => false, 'output' => $combinedOutput ?: 'No threats found'];
            } else {
                Log::error('ClamAV Error: ' . $combinedOutput . ' | Exit Code: ' . $exitCode);
                return ['error' => 'Scan failed', 'output' => $combinedOutput ?: $errorOutput];
            }

        } catch (\Exception $e) {
            Log::error('ClamAV Scan Exception: ' . $e->getMessage());
            return ['error' => 'Exception', 'message' => $e->getMessage()];
        }
    }

    protected function runVirusTotal(string $sha256): ?array
    {
        $report = $this->virusTotalService->getFileReport($sha256);
        return $report['data'] ?? null;
    }
}
