<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\VirusTotalService;
use App\Models\UrlAnalysisLog;

class UrlScannerController extends Controller
{
    protected $virusTotalService;

    public function __construct(VirusTotalService $virusTotalService)
    {
        $this->virusTotalService = $virusTotalService;
    }

    public function index()
    {
        $logs = UrlAnalysisLog::where('user_id', auth()->id())
            ->latest()
            ->paginate(10);
        
        // Calculate Stats
        $allLogs = UrlAnalysisLog::where('user_id', auth()->id())->get(['status', 'result']);
        
        $stats = [
            'total' => $allLogs->count(),
            'malicious' => $allLogs->filter(function($log) {
                if ($log->status !== 'completed' || !$log->result) return false;
                $attr = $log->result['attributes'] ?? $log->result;
                $mal = ($attr['last_analysis_stats']['malicious'] ?? 0) + ($attr['stats']['malicious'] ?? 0);
                return $mal > 0;
            })->count(),
            'safe' => $allLogs->filter(function($log) {
                 if ($log->status !== 'completed' || !$log->result) return false;
                 $attr = $log->result['attributes'] ?? $log->result;
                 $mal = ($attr['last_analysis_stats']['malicious'] ?? 0) + ($attr['stats']['malicious'] ?? 0);
                 return $mal === 0;
            })->count(),
            'pending' => $allLogs->where('status', 'pending')->count(),
        ];
            
        return view('url-scanner.index', compact('logs', 'stats'));
    }

    public function scan(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        $url = $request->input('url');
        
        // 1. Check Previous Scan (Optimization)
        // VT URL ID is base64 without padding
        $report = $this->virusTotalService->getUrlReport($url);

        if ($report) {
            // Found existing report. Save as completed.
            $log = UrlAnalysisLog::create([
                'user_id' => auth()->id(),
                'url' => $url,
                'url_hash' => rtrim(base64_encode($url), '='),
                'status' => 'completed',
                'result' => $report['data'],
            ]);

            return response()->json([
                'status' => 'completed',
                'result' => $report['data']
            ]);
        }

        // 2. Submit for New Scan
        $scanResult = $this->virusTotalService->scanUrl($url);

        if (!$scanResult) {
            return response()->json(['error' => 'Scanning failed. Check API Key.'], 500);
        }

        $analysisId = $scanResult['data']['id'];

        $log = UrlAnalysisLog::create([
             'user_id' => auth()->id(),
             'url' => $url,
             'url_hash' => rtrim(base64_encode($url), '='),
             'analysis_id' => $analysisId,
             'status' => 'pending',
             'result' => null,
        ]);

        return response()->json([
            'status' => 'pending',
            'analysis_id' => $analysisId,
            'log_id' => $log->id
        ]);
    }

    public function checkStatus($id)
    {
        $log = UrlAnalysisLog::where('user_id', auth()->id())->findOrFail($id);

        if ($log->status === 'completed') {
             return response()->json(['status' => 'completed', 'result' => $log->result]);
        }
        
        if (!$log->analysis_id) {
             return response()->json(['status' => 'error', 'message' => 'No Analysis ID']);
        }

        // Poll by Analysis ID
        $analysis = $this->virusTotalService->getAnalysis($log->analysis_id);

        if ($analysis && isset($analysis['data']['attributes']['status'])) {
             $vtStatus = $analysis['data']['attributes']['status'];
             
             if ($vtStatus === 'completed') {
                 // Analysis Object contains results too!
                 $log->update([
                     'status' => 'completed',
                     'result' => $analysis['data']
                 ]);
                 
                 return response()->json(['status' => 'completed', 'result' => $analysis['data']]);
             }
        }
        
        return response()->json(['status' => 'pending']);
    }
}
