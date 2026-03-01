<?php

namespace App\Http\Controllers;

use App\Models\WazuhAlert;
use Illuminate\Http\Request;

class WazuhAlertController extends Controller
{
    public function index(Request $request)
    {
        $query = WazuhAlert::latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('level')) {
            $query->where('rule_level', '>=', (int)$request->level);
        }
        if ($request->filled('agent')) {
            $query->where('agent_name', $request->agent);
        }

        $alerts = $query->paginate(20);

        $stats = [
            'total'        => WazuhAlert::count(),
            'new'          => WazuhAlert::where('status', 'New')->count(),
            'acknowledged' => WazuhAlert::where('status', 'Acknowledged')->count(),
            'resolved'     => WazuhAlert::where('status', 'Resolved')->count(),
            'critical'     => WazuhAlert::where('rule_level', '>=', 12)->count(),
            'high'         => WazuhAlert::whereBetween('rule_level', [10, 11])->count(),
        ];

        // Get unique agent names for filter dropdown
        $agents = WazuhAlert::whereNotNull('agent_name')
            ->distinct()
            ->pluck('agent_name')
            ->sort()
            ->values();

        return view('wazuh-alerts.index', compact('alerts', 'stats', 'agents'));
    }

    public function show(WazuhAlert $wazuhAlert)
    {
        return view('wazuh-alerts.show', compact('wazuhAlert'));
    }

    public function updateStatus(Request $request, WazuhAlert $wazuhAlert)
    {
        $request->validate(['status' => 'required|in:New,Acknowledged,Resolved']);
        $wazuhAlert->update(['status' => $request->status]);
        return back()->with('success', 'Alert status updated.');
    }

    public function bulkResolve(Request $request)
    {
        $request->validate([
            'alert_ids' => 'required|array',
            'alert_ids.*' => 'exists:wazuh_alerts,id'
        ]);

        $count = WazuhAlert::whereIn('id', $request->alert_ids)->update(['status' => 'Resolved']);

        return back()->with('success', "{$count} alerts have been resolved.");
    }
}
