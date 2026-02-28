<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BlockedIp;

class BlockIpController extends Controller
{
    /**
     * Agent acknowledges action (Block/Unblock success or fail)
     */
    public function ack(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:blocked_ips,id',
            'status' => 'required|in:blocked,unblocked,failed',
            'error' => 'nullable|string'
        ]);

        $blockedIp = BlockedIp::find($validated['id']);
        
        // Update status
        $blockedIp->status = $validated['status'];
        
        if ($validated['status'] === 'blocked' && !$blockedIp->blocked_at) {
            $blockedIp->blocked_at = now();
        }

        if ($request->has('error')) {
            $blockedIp->reason = $blockedIp->reason . " | Error: " . $request->error;
        }
        $blockedIp->save();

        return response()->json(['status' => 'ok']);
    }
}
