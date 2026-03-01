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

        return response()->json([
            'fum_alerts' => $fumAlertsPending,
            'wazuh_alerts' => $wazuhAlertsNew,
            'badip_alerts' => $badIpAlertsNew,
            'webhook_total' => $fumAlertsPending + $wazuhAlertsNew + $badIpAlertsNew,
            // You can add more counts here later
            'total' => $fumAlertsPending + $wazuhAlertsNew + $badIpAlertsNew,
        ]);
    }
}
