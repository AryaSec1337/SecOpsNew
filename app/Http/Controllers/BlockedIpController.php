<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BlockedIp;
use App\Models\Asset;
use Illuminate\Support\Facades\DB;

class BlockedIpController extends Controller
{
    public function index()
    {
        // Removed initial pagination variable as we are moving to Alpine
        $servers = Asset::where('type', 'server')->get(); 
        return view('blocked_ips.index', compact('servers'));
    }

    public function list()
    {
        $blockedIps = BlockedIp::with('agent')
            ->latest()
            ->get()
            ->map(function ($ip) {
                return [
                    'id' => $ip->id,
                    'ip_address' => $ip->ip_address,
                    'port' => $ip->port,
                    'protocol' => $ip->protocol,
                    'agent_name' => $ip->agent?->name ?? 'Unknown',
                    'agent_status' => $ip->agent?->status ?? 'Offline',
                    'status' => $ip->status,
                    'reason' => $ip->reason,
                    'created_at' => $ip->created_at->format('d M Y H:i:s'), // Pending Time
                    'blocked_at' => $ip->blocked_at ? $ip->blocked_at->format('d M Y H:i:s') : '-', // Blocked Time
                    'status_label' => ucwords(str_replace('_', ' ', $ip->status)),
                    'row_class' => match($ip->status) {
                        'pending_block' => 'bg-yellow-50 dark:bg-yellow-900/10',
                        'blocked' => 'bg-white dark:bg-slate-900', // Default
                        'pending_unblock' => 'bg-blue-50 dark:bg-blue-900/10',
                        'unblocked' => 'bg-gray-50 dark:bg-gray-900/10 text-gray-400',
                        default => ''
                    },
                    'badge_class' => match($ip->status) {
                        'pending_block' => 'bg-yellow-100 text-yellow-800',
                        'blocked' => 'bg-red-100 text-red-800',
                        'pending_unblock' => 'bg-blue-100 text-blue-800',
                        'unblocked' => 'bg-gray-100 text-gray-800',
                        'failed' => 'bg-orange-100 text-orange-800',
                        default => 'bg-gray-100'
                    }
                ];
            });

        return response()->json($blockedIps);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ip_address' => 'required|ip',
            'agent_id' => 'required|exists:assets,id',
            'port' => 'nullable|integer|min:1|max:65535',
            'protocol' => 'required|in:tcp,udp',
            'reason' => 'required|string',
        ]);

        // Check availability
        $exists = BlockedIp::where('ip_address', $validated['ip_address'])
            ->where('agent_id', $validated['agent_id'])
            ->where('port', $validated['port'])
            ->where('protocol', $validated['protocol'])
            ->whereIn('status', ['blocked', 'pending_block'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Rule already exists (IP + Port + Protocol combination).');
        }

        // QUEUE ONLY - Agent will pickup via heartbeat
        BlockedIp::create([
            'ip_address' => $validated['ip_address'],
            'port' => $validated['port'],
            'protocol' => $validated['protocol'],
            'agent_id' => $validated['agent_id'],
            'status' => 'pending_block', // Agent will update to 'blocked' upon success
            'reason' => 'Manual Block: ' . $validated['reason'],
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', 'Block command queued. Waiting for agent to process.');
    }

    public function destroy($id)
    {
        $blockedIp = BlockedIp::findOrFail($id);
        
        // Future: Call unblock API
        $blockedIp->delete();
        
        return back()->with('success', 'Block rule removed from database (Manual unblock required on agent).');
    }
}
