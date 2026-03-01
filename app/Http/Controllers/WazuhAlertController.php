<?php

namespace App\Http\Controllers;

use App\Models\WazuhAlert;
use App\Models\MitigationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function createIncident(Request $request, WazuhAlert $wazuhAlert)
    {
        // Prevent duplicate escalation if already resolved / escalated
        if ($wazuhAlert->status === 'Resolved') {
            return back()->with('error', 'This alert is already resolved or has been escalated.');
        }

        // Map severity to priority
        $priorityMap = [
            'Critical' => 'Critical',
            'High'     => 'High',
            'Medium'   => 'Medium',
            'Low'      => 'Low',
            'Info'     => 'Low',
        ];

        // Format Description from JSON
        $description = "Wazuh Alert ID: {$wazuhAlert->alert_id}\n";
        $description .= "Rule ID: {$wazuhAlert->rule_id}\n";
        $description .= "Agent: {$wazuhAlert->agent_name} ({$wazuhAlert->agent_ip})\n";
        
        if ($wazuhAlert->src_ip) {
            $description .= "Source IP: {$wazuhAlert->src_ip}\n";
        }
        
        $description .= "\n--- Raw JSON Data ---\n";
        $description .= json_encode($wazuhAlert->raw_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        // Create the Incident (Mitigation Log)
        $incident = MitigationLog::create([
            'user_id'         => Auth::id(),
            'title'           => "Wazuh Alert: " . ($wazuhAlert->rule_description ?? "Rule " . $wazuhAlert->rule_id),
            'description'     => $description,
            'system_affected' => trim("{$wazuhAlert->agent_name} - {$wazuhAlert->agent_ip}", " -"),
            'type'            => 'General',
            'priority'        => $priorityMap[$wazuhAlert->severity] ?? 'Low',
            'severity'        => $priorityMap[$wazuhAlert->severity] ?? 'Low',
            'status'          => 'Pending',
            'mitigated_at'    => now(),
            'incident_time'   => $wazuhAlert->created_at,
        ]);

        // Add to Timeline
        $incident->details()->create([
            'action'      => 'Incident Escalated from Wazuh',
            'description' => "This incident was automatically generated from Wazuh Alert #{$wazuhAlert->id} (Rule: {$wazuhAlert->rule_id}).",
            'log_date'    => now(),
            'user_id'     => Auth::id(),
        ]);

        // Auto-resolve the Wazuh Alert
        $wazuhAlert->update(['status' => 'Resolved']);

        return redirect()->route('mitigation-logs.show', $incident->id)
            ->with('success', 'Wazuh Alert successfully escalated into an Incident.');
    }
}
