<?php

namespace App\Http\Controllers;

use App\Models\BadIpAlert;
use Illuminate\Http\Request;

class BadIpAlertController extends Controller
{
    /**
     * Display a listing of Bad IP Alerts.
     */
    public function index(Request $request)
    {
        $query = BadIpAlert::latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('src_ip', 'like', "%{$request->search}%")
                  ->orWhere('rule_description', 'like', "%{$request->search}%");
            });
        }

        $alerts = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => BadIpAlert::count(),
            'new' => BadIpAlert::where('status', 'New')->count(),
            'acknowledged' => BadIpAlert::where('status', 'Acknowledged')->count(),
            'resolved' => BadIpAlert::where('status', 'Resolved')->count(),
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'alerts' => $alerts,
                'stats' => $stats
            ]);
        }

        return view('bad-ip-alerts.index', compact('alerts', 'stats'));
    }

    /**
     * Update the status of a specific alert.
     */
    public function updateStatus(Request $request, BadIpAlert $badIpAlert)
    {
        $request->validate([
            'status' => 'required|in:New,Acknowledged,Resolved'
        ]);

        $badIpAlert->update(['status' => $request->status]);

        return back()->with('success', 'Alert status updated successfully.');
    }

    /**
     * Bulk resolve alerts.
     */
    public function bulkResolve(Request $request)
    {
        $request->validate([
            'alert_ids' => 'required|array',
            'alert_ids.*' => 'exists:bad_ip_alerts,id'
        ]);

        $count = BadIpAlert::whereIn('id', $request->alert_ids)->update(['status' => 'Resolved']);

        return back()->with('success', "{$count} alerts have been resolved.");
    }
}
