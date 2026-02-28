<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HeartbeatController extends Controller
{
    public function store(Request $request)
    {
        // Simple Validation
        $request->validate([
            'hostname' => 'required|string',
            'type' => 'required|in:server,application',
        ]);

        try {
            // Find or Create Asset
            $asset = Asset::updateOrCreate(
                [
                    'name' => $request->hostname, // or app name
                    'type' => $request->type
                ],
                [
                    'ip_address' => $request->ip_address,
                    'os_name' => $request->os_name ?? $request->os, // Handle variations
                    'os_version' => $request->os_version,
                    'role' => $request->role,
                    'app_version' => $request->version,
                    'vendor' => $request->vendor,
                    'app_type' => $request->app_type,
                    'criticality' => $request->criticality,
                    'owner' => $request->owner,
                    'location' => $request->location,
                    'status' => 'Online', // Always set to Online when receiving heartbeat
                    'last_seen_at' => now(),
                    'metadata' => $request->metadata ?? [],
                ]
            );

            // Check for pending actions
            $actions = [];
            
            // 1. IP Blocking Actions
            $pendingBlocks = \App\Models\BlockedIp::where('agent_id', $asset->id)
                ->whereIn('status', ['pending_block', 'pending_unblock'])
                ->get();

            foreach ($pendingBlocks as $block) {
                $actions[] = [
                    'id' => $block->id,
                    'type' => $block->status == 'pending_block' ? 'block_ip' : 'unblock_ip',
                    'ip' => $block->ip_address,
                ];
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Heartbeat received',
                'asset_id' => $asset->id,
                'actions' => $actions
            ]);

        } catch (\Exception $e) {
            Log::error('Heartbeat Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Internal Server Error'], 500);
        }
    }
}
