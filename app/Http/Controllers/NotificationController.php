<?php

namespace App\Http\Controllers;

use App\Models\WazuhAlert;
use App\Models\WebhookAlert;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get real-time counts of pending items for UI badges.
     */
    public function pending(Request $request)
    {
        $fumAlertsPending = WebhookAlert::where('status', 'Pending')->count();
        $wazuhAlertsNew = WazuhAlert::where('status', 'New')->count();
        $badIpAlertsNew = \App\Models\BadIpAlert::where('status', 'New')->count();

        $lastSeenAt = $request->query('last_seen_at');
        $newBadIps = [];
        
        if ($lastSeenAt) {
            $newBadIps = \App\Models\BadIpAlert::where('last_seen_at', '>', $lastSeenAt)
                                               ->get(['id', 'src_ip', 'rule_description', 'occurrences', 'last_seen_at']);
        }
        
        $latestAlert = \App\Models\BadIpAlert::orderBy('last_seen_at', 'desc')->first();
        $maxSeenAt = $latestAlert && $latestAlert->last_seen_at ? $latestAlert->last_seen_at->format('Y-m-d H:i:s') : null;

        return response()->json([
            'fum_alerts' => $fumAlertsPending,
            'wazuh_alerts' => $wazuhAlertsNew,
            'badip_alerts' => $badIpAlertsNew,
            'new_bad_ips' => $newBadIps,
            'max_seen_at' => $maxSeenAt,
            'webhook_total' => $fumAlertsPending + $wazuhAlertsNew + $badIpAlertsNew,
            // You can add more counts here later
            'total' => $fumAlertsPending + $wazuhAlertsNew + $badIpAlertsNew,
        ]);
    }
}
