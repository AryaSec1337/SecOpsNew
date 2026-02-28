<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\BlockedIp;
use App\Models\Backup;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // --- 1. Core KPIs ---
        // Calculate Total Events (Mitigation Logs + Analysis Logs)
        $mitigationCount = \App\Models\MitigationLog::count();
        $emailAnalysisCount = \App\Models\EmailAnalysisHistory::count();
        $fileAnalysisCount = \App\Models\FileAnalysisLog::count();
        $urlAnalysisCount = \App\Models\UrlAnalysisLog::count();
        $ipAnalysisCount = \App\Models\IpAnalysis::count();
        
        $totalLogs = $mitigationCount + $emailAnalysisCount + $fileAnalysisCount + $urlAnalysisCount + $ipAnalysisCount;
        
        // Active Incidents (Pending or In Progress)
        $openIncidentsCount = \App\Models\MitigationLog::whereIn('status', ['Pending', 'In Progress'])->count();
        
        // Blocked IPs
        $blockedIpsCount = BlockedIp::where('status', 'active')->count();
        
        // Critical FIM (Mocking or fetching if available)
        $criticalFim = \App\Models\FileIntegrityLog::whereIn('change_type', ['modified', 'deleted'])->count();

        // --- 2. Investigation Statistics ---
        $investigationStats = [
            'pending' => \App\Models\MitigationLog::where('status', 'Pending')->count(),
            'in_progress' => \App\Models\MitigationLog::where('status', 'In Progress')->count(),
            'resolved' => \App\Models\MitigationLog::where('status', 'Resolved')->count(),
        ];

        $investigationTypes = \App\Models\MitigationLog::select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        $attackStats = \App\Models\MitigationLog::select('attack_classification', DB::raw('count(*) as total'))
            ->whereNotNull('attack_classification')
            ->groupBy('attack_classification')
            ->pluck('total', 'attack_classification')
            ->toArray();

        // --- 3. Top Contributors ---
        $topDepartments = \App\Models\MitigationLog::select('reporter_department', DB::raw('count(*) as total'))
            ->whereNotNull('reporter_department')
            ->groupBy('reporter_department')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $topReporters = \App\Models\MitigationLog::select('reporter_email', DB::raw('count(*) as total'))
            ->whereNotNull('reporter_email')
            ->groupBy('reporter_email')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $reporterCount = \App\Models\MitigationLog::whereNotNull('reporter_email')->distinct()->count('reporter_email');

        // --- 4. Tool Usage Stats & Latest Activity ---
        $toolStats = [
            'ip_analyzer' => $ipAnalysisCount,
            'email_analyzer' => $emailAnalysisCount,
            'url_scanner' => $urlAnalysisCount,
            'file_analysis' => $fileAnalysisCount,
            'blocked_ips' => $blockedIpsCount
        ];

        // Fetch Recent Activity for Cards (Lists)
        $recentIps = \App\Models\IpAnalysis::latest()->take(5)->get();
        $recentEmails = \App\Models\EmailAnalysisHistory::latest()->take(5)->get();
        $recentUrls = \App\Models\UrlAnalysisLog::latest()->take(5)->get();
        $recentFiles = \App\Models\FileAnalysisLog::latest()->take(5)->get();
        $recentBlocked = BlockedIp::latest()->take(5)->get();

        // --- 5. Asset Stats & Matrix ---
        $assetStats = [
            'total' => Asset::count(),
            'server' => Asset::where('type', 'Server')->count(),
            'app' => Asset::where('type', 'Application')->count(),
        ];
        
        $assets = Asset::where('type', 'Server')
            ->orderBy('name')
            ->take(6)
            ->get()
            ->map(function ($asset) {
                $asset->status = $asset->status ?? 'offline'; 
                $asset->cpu = $asset->metadata['cpu'] ?? 0;
                $asset->ram = $asset->metadata['ram'] ?? 0;
                return $asset;
            });

        // --- 6. Chart Data: Traffic Overview (Stubbed for now, can be replaced with real log traffic) ---
        $chartLabels = [];
        $chartValues = [];
        for ($i = 6; $i >= 0; $i--) {
            $chartLabels[] = now()->subDays($i)->format('d M');
            $chartValues[] = rand(10, 100); // Mock data for visual consistency
        }

        // --- 7. Live Feed ---
        $recentActivity = collect([]);

        return view('dashboard.index', compact(
            'totalLogs', 
            'openIncidentsCount',
            'criticalFim', 
            'blockedIpsCount',
            'investigationStats',
            'investigationTypes',
            'attackStats',
            'topDepartments',
            'topReporters',
            'reporterCount',
            'toolStats',
            'recentIps',
            'recentEmails',
            'recentUrls',
            'recentFiles',
            'recentBlocked',
            'chartLabels', 
            'chartValues',
            'recentActivity',
            'assetStats',
            'assets'
        ));
    }

    public function liveStats()
    {
        // 1. Counters
        $counters = [
            'logs' => \App\Models\MitigationLog::count() + \App\Models\EmailAnalysisHistory::count() + \App\Models\FileAnalysisLog::count() + \App\Models\UrlAnalysisLog::count(),
            'open_incidents' => \App\Models\MitigationLog::whereIn('status', ['Pending', 'In Progress'])->count(),
            'blocked_ips' => BlockedIp::where('status', 'active')->count(),
            'critical_fim' => \App\Models\FileIntegrityLog::count(),
        ];

        return response()->json([
            'counters' => $counters,
            'defcon' => 5, // Logic can be added to lower DEFCON based on open critical incidents
            'feed' => [], // Can fetch latest 5 logs here if needed
            'timestamp' => now()->format('H:i:s')
        ]);
    }
}
