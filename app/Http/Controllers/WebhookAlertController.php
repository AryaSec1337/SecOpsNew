<?php

namespace App\Http\Controllers;

use App\Models\WebhookAlert;
use Illuminate\Http\Request;

class WebhookAlertController extends Controller
{
    public function index(Request $request)
    {
        $query = WebhookAlert::with('webhookFileScan')->latest();

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by verdict
        if ($request->has('verdict') && $request->verdict) {
            $query->where('verdict', $request->verdict);
        }

        $alerts = $query->paginate(15);

        $stats = [
            'total' => WebhookAlert::count(),
            'pending' => WebhookAlert::where('status', 'Pending')->count(),
            'in_progress' => WebhookAlert::where('status', 'In Progress')->count(),
            'resolved' => WebhookAlert::where('status', 'Resolved')->count(),
            'malicious' => WebhookAlert::where('verdict', 'MALICIOUS')->count(),
            'suspicious' => WebhookAlert::where('verdict', 'SUSPICIOUS')->count(),
        ];

        return view('webhook-alerts.index', compact('alerts', 'stats'));
    }

    public function show(WebhookAlert $webhookAlert)
    {
        $webhookAlert->load('webhookFileScan');
        return view('webhook-alerts.show', compact('webhookAlert'));
    }

    public function updateStatus(Request $request, WebhookAlert $webhookAlert)
    {
        $request->validate(['status' => 'required|in:Pending,In Progress,Resolved']);
        $webhookAlert->update(['status' => $request->status]);
        return back()->with('success', 'Alert status updated.');
    }
}
