<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\BlockedIp;
use Illuminate\Support\Facades\Log;

class AgentController extends Controller
{
    /**
     * Agent Heartbeat
     * 
     * Agent sends POST /api/agent/heartbeat with Bearer Token.
     * App updates last_seen_at and returns pending commands.
     */
    public function heartbeat(Request $request)
    {
        // 1. Identify Agent by Token
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['error' => 'No token provided'], 401);
        }

        $agent = Asset::where('api_token', $token)->first();
        if (!$agent) {
             return response()->json(['error' => 'Invalid token'], 401);
        }

        // 2. Update Status
        $agent->update([
            'last_seen_at' => now(),
            'status' => 'Online', // Explicitly set online
            'metadata' => array_merge($agent->metadata ?? [], $request->input('metadata', []))
        ]);

        // 3. Fetch Pending Commands (Blocked IPs)
        // We look for 'pending_block' or 'pending_unblock'
        $commands = BlockedIp::where('agent_id', $agent->id)
            ->whereIn('status', ['pending_block', 'pending_unblock'])
            ->get()
            ->map(function ($cmd) {
                return [
                    'id' => $cmd->id,
                    'type' => $cmd->status === 'pending_block' ? 'block' : 'unblock',
                    'ip_address' => $cmd->ip_address,
                    'port' => $cmd->port,
                    'protocol' => $cmd->protocol,
                ];
            });

        // 4. Update status to 'processing' to avoid duplicate dispatch?
        // Or keep as pending until ACK? 
        // Better keep as pending, but maybe add a 'dispatched_at' timestamp to avoid infinite retry loops if agent fails silently?
        // For simplicity: We send them. Agent MUST Ack. 
        // If agent receives same command twice (e.g. heartbeat retry), iptables -I is idempotent usually (or agent handles it).
        
        return response()->json([
            'status' => 'ok',
            'commands' => $commands
        ]);
    }

    /**
     * Agent Acknowledge Command Execution
     */
    public function acknowledge(Request $request)
    {
        $request->validate([
            'command_id' => 'required|exists:blocked_ips,id',
            'status' => 'required|in:success,error',
            'message' => 'nullable|string'
        ]);

        $cmd = BlockedIp::find($request->command_id);
        
        // Update Status
        if ($request->status === 'success') {
            $cmd->status = $cmd->status === 'pending_block' ? 'blocked' : 'unblocked';
            $cmd->blocked_at = now(); 
            
            // Send Email Notification if Blocked
            if ($cmd->status === 'blocked') {
                $recipient = env('MAIL_NOTIFICATION_TO');
                if ($recipient) {
                    try {
                        \Illuminate\Support\Facades\Mail::to($recipient)->send(new \App\Mail\IpBlockedNotification($cmd));
                    } catch (\Exception $e) {
                         \Illuminate\Support\Facades\Log::error('Failed to send IP Blocked Email: ' . $e->getMessage());
                    }
                }
            }
        } else {
            $cmd->status = 'failed';
        }
        
        $cmd->reason .= "\n[Agent Log]: " . $request->message;
        $cmd->save();

        return response()->json(['status' => 'ack_received']);
    }
}
