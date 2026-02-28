<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ThreatIntelligenceService;

class IpAnalyzerController extends Controller
{
    protected $intelService;

    public function __construct(ThreatIntelligenceService $intelService)
    {
        $this->intelService = $intelService;
    }

    public function index()
    {
        return view('investigation.ip_analyzer.index');
    }

    public function analyze(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
        ]);

        $ip = $request->input('ip');

        $result = $this->intelService->analyzeIp($ip, function($step, $status, $message) {
            // Callback not needed for synchronous return
        });

        // Save to Database
        \App\Models\IpAnalysis::create([
            'ip_address' => $ip,
            'risk_score' => $result['risk_score'] ?? 0,
            'geo_data' => $result['geo'] ?? null,
            'virustotal_data' => $result['virustotal'] ?? null,
            'abuseipdb_data' => $result['abuseipdb'] ?? null,
            'greynoise_data' => $result['greynoise'] ?? null,
            'alienvault_data' => $result['alienvault'] ?? null,
        ]);

        return response()->json([
            'ip' => $ip,
            'result' => $result
        ]);
    }

    public function history()
    {
        $history = \App\Models\IpAnalysis::latest()->take(10)->get();
        return response()->json($history);
    }

    public function export(Request $request) 
    {
        $json = $request->input('data');
        $data = json_decode($json, true);

        if (!$data) {
            return back()->with('error', 'Invalid data for export');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('investigation.ip_analyzer.pdf', ['data' => $data]);
        $pdf->setOptions(['isRemoteEnabled' => true]);
        $pdf->setPaper('a4', 'portrait');
        
        return $pdf->download('IP_Report_' . ($data['ip'] ?? 'unknown') . '.pdf');
    }
}
