<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\VirusTotalService;
use Illuminate\Support\Facades\Log;

class FileAnalystController extends Controller
{
    protected $virusTotalService;

    public function __construct(VirusTotalService $virusTotalService)
    {
        $this->virusTotalService = $virusTotalService;
    }

    public function index()
    {
        $logs = \App\Models\FileAnalysisLog::where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        // Calculate Stats (Fetching all for aggregation - might need optimization for huge datasets)
        $allLogs = \App\Models\FileAnalysisLog::where('user_id', auth()->id())->get(['status', 'result']);
        
        $stats = [
            'total' => $allLogs->count(),
            'malicious' => $allLogs->filter(function($log) {
                if ($log->status !== 'completed' || !$log->result) return false;
                $attr = $log->result['attributes'] ?? $log->result; // Handle structure variations
                $mal = ($attr['last_analysis_stats']['malicious'] ?? 0) + ($attr['stats']['malicious'] ?? 0);
                return $mal > 0;
            })->count(),
            'clean' => $allLogs->filter(function($log) {
                 if ($log->status !== 'completed' || !$log->result) return false;
                 $attr = $log->result['attributes'] ?? $log->result;
                 $mal = ($attr['last_analysis_stats']['malicious'] ?? 0) + ($attr['stats']['malicious'] ?? 0);
                 return $mal === 0;
            })->count(),
            'pending' => $allLogs->where('status', 'pending')->count(),
        ];
            
        return view('file-analyst.index', compact('logs', 'stats'));
    }

    public function analyze(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:32768',
        ]);

        $file = $request->file('file');
        $hash = hash_file('sha256', $file->getRealPath());
        $fileName = $file->getClientOriginalName();

        // Check if we already have this in OUR DB first (cache) or VT
        // For now, let's just check VT to update our local DB if new.
        
        // 1. Check VirusTotal by Hash
        $report = $this->virusTotalService->getFileReport($hash);

        if ($report) {
            // Run Local YARA Scan even if VT has result
            $yaraResults = $this->scanWithYara($file->getRealPath());

            // Already scanned by VT. Save to local DB as 'completed'
            \App\Models\FileAnalysisLog::create([
                'user_id' => auth()->id(),
                'file_name' => $fileName,
                'file_hash_sha256' => $hash,
                'analysis_id' => null, // Not needed if we have result
                'status' => 'completed',
                'result' => $report['data'],
                'yara_matches' => $yaraResults
            ]);

            return response()->json([
                'status' => 'completed',
                'result' => $report['data']
            ]);
        }

        // 2. Upload to VT
        $uploadResult = $this->virusTotalService->scanFile($file);

        if (!$uploadResult) {
            return response()->json(['error' => 'Upload failed'], 500);
        }

        $analysisId = $uploadResult['data']['id'];

        // 3. Run Local YARA Scan
        $yaraResults = $this->scanWithYara($file->getRealPath());

        // 4. Save as 'pending' to Local DB (VT is pending, YARA is done)
        $log = \App\Models\FileAnalysisLog::create([
            'user_id' => auth()->id(),
            'file_name' => $fileName,
            'file_hash_sha256' => $hash,
            'analysis_id' => $analysisId,
            'status' => 'pending',
            'result' => null,
            'yara_matches' => $yaraResults
        ]);

        return response()->json([
            'status' => 'pending',
            'analysis_id' => $analysisId,
            'log_id' => $log->id,
            'yara_matches' => $yaraResults
        ]);
    }

    public function checkStatus($id)
    {
        $log = \App\Models\FileAnalysisLog::where('user_id', auth()->id())->findOrFail($id);

        if ($log->status === 'completed') {
            return response()->json(['status' => 'completed', 'result' => $log->result]);
        }

        if (!$log->analysis_id) {
            return response()->json(['status' => 'error', 'message' => 'No Analysis ID']);
        }

        // Poll VirusTotal
        $analysis = $this->virusTotalService->getAnalysis($log->analysis_id);

        if ($analysis && isset($analysis['data']['attributes']['status'])) {
            $vtStatus = $analysis['data']['attributes']['status'];
            
            if ($vtStatus === 'completed') {
                // Fetch the full ITEM report now (Analysis object has results too, but getting File object is standard)
                // Actually, the Analysis object 'attributes.results' contains engine data.
                
                // Update DB
                $log->update([
                    'status' => 'completed',
                    'result' => $analysis['data'] // Saving Analysis Object
                ]);

                return response()->json(['status' => 'completed', 'result' => $analysis['data']]);
            }
        }

        return response()->json(['status' => 'pending']);
    }
    private function scanWithYara($filePath)
    {
        try {
            $scannerScript = base_path('tools/yara_scanner.py');
            $rulesDir = storage_path('app/yara_rules');
            
            // Ensure rules directory exists
            if (!file_exists($rulesDir)) {
                mkdir($rulesDir, 0755, true);
            }

            $command = [
                config('app.python_path', 'python'),
                $scannerScript,
                $filePath,
                $rulesDir
            ];

            $process = new \Symfony\Component\Process\Process($command);
            $process->setTimeout(60); 
            $process->run();

            if (!$process->isSuccessful()) {
                Log::error('YARA Scan Failed: ' . $process->getErrorOutput());
                return ['error' => 'Scan failed', 'log' => $process->getErrorOutput()];
            }

            $output = $process->getOutput();
            return json_decode($output, true);

        } catch (\Exception $e) {
            Log::error('YARA Scan Exception: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
}
