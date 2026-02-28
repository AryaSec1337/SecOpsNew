<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\DomainScanLog;
use Illuminate\Http\Request;

class CtiController extends Controller
{
    public function domainMonitoring()
    {
        $domains = Asset::domains()->with('latestScan')->paginate(10);
        
        $stats = [
            'total_domains' => Asset::domains()->count(),
            'monitored' => Asset::domains()->where('status', 'Monitored')->count(),
            'avg_reputation' => DomainScanLog::avg('reputation_score') ?? 0,
            'recent_scans' => DomainScanLog::where('created_at', '>=', now()->subDay())->count()
        ];

        // Chart Data 1: SSL Status
        $sslStats = [
            'valid' => \App\Models\DomainSslStatus::where('is_valid', true)->where('days_remaining', '>', 30)->count(),
            'expiring' => \App\Models\DomainSslStatus::where('days_remaining', '<=', 30)->where('days_remaining', '>', 0)->count(),
            'expired' => \App\Models\DomainSslStatus::where('is_valid', false)->count(),
        ];

        // Chart Data 2: Typosquatting
        $typoStats = [
            'total' => \App\Models\TyposquatLog::count(),
            'suspicious' => \App\Models\TyposquatLog::where('is_registered', true)->count(),
            'clean' => \App\Models\TyposquatLog::where('is_registered', false)->count(),
        ];

        if (request()->wantsJson()) {
            return response()->json($domains);
        }

        // Ransomware Stats
        $ransomwareStats = [
            'total' => \App\Models\RansomwareVictim::count(),
            'recent' => \App\Models\RansomwareVictim::latest('discovered_at')->take(5)->get(),
            'groups' => \App\Models\RansomwareVictim::select('group_name', \DB::raw('count(*) as total'))
                ->groupBy('group_name')
                ->orderByDesc('total')
                ->take(5)
                ->pluck('total', 'group_name')
        ];

        return view('cti.domain_monitoring.index', compact('domains', 'stats', 'sslStats', 'typoStats', 'ransomwareStats'));
    }

    public function storeDomain(Request $request)
    {
        $request->validate([
            'domain' => 'required|string|unique:assets,name,NULL,id,type,domain',
        ]);

        Asset::create([
            'name' => $request->domain,
            'type' => 'domain',
            'status' => 'Monitored',
            'metadata' => ['provider' => 'VirusTotal'],
            'last_seen_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Domain added for monitoring.');
    }

    public function show($id)
    {
        $domain = Asset::with(['sslStatus', 'dnsRecords', 'latestScan'])->findOrFail($id);
        $scanHistory = $domain->scanHistory()->take(10)->get();
        // Fetch detected typosquats
        $typosquats = \App\Models\TyposquatLog::where('original_domain', $domain->name)
            ->where('is_registered', true)
            ->orderBy('scan_date', 'desc')
            ->get();
            
        return view('cti.domain_monitoring.show', compact('domain', 'scanHistory', 'typosquats'));
    }

    public function scan($id)
    {
        $domain = Asset::findOrFail($id);
        
        try {
            \Illuminate\Support\Facades\Log::info("Manual Scan Initiated for: {$domain->name} (ID: {$domain->id})");
            
            // Run command
            $exitCode = \Illuminate\Support\Facades\Artisan::call('cti:scan-domains', ['domain' => $domain->name]);
            $output = \Illuminate\Support\Facades\Artisan::output();

            \Illuminate\Support\Facades\Log::info("Scan Command Output: " . $output);

            if ($exitCode === 0) {
                return redirect()->back()->with('success', "Scan successful for {$domain->name}.");
            } else {
                return redirect()->back()->withErrors(['msg' => "Scan failed with exit code {$exitCode}. Check logs."]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Manual Scan Exception: " . $e->getMessage());
            return redirect()->back()->withErrors(['msg' => 'Scan error: ' . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            $domain = Asset::where('type', 'domain')->findOrFail($id);
            $name = $domain->name;
            $domain->delete();
            
            return redirect()->route('cti.domain.index')->with('success', "Domain {$name} deleted successfully.");
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['msg' => 'Failed to delete domain: ' . $e->getMessage()]);
        }
    }
}
