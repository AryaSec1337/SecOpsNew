<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;

class AssetServerController extends Controller
{
    public function index()
    {
        // Initial load (can be empty or paginated)
        $servers = Asset::where('type', 'server')->paginate(10);
        return view('assets.server.index', ['servers' => $servers]);
    }

    public function list()
    {
        // JSON endpoint for polling
        $servers = Asset::where('type', 'server')
            ->orderBy('last_seen_at', 'desc')
            ->get()
            ->map(function ($server) {
                return [
                    'id' => $server->id,
                    'hostname' => $server->name,
                    'ip_address' => $server->ip_address,
                    'os' => $server->os_name . ' ' . $server->os_version,
                    'role' => $server->role,
                    'status' => $server->status, // You might want to calc status based on last_seen_at diff
                    'last_audit' => $server->last_seen_at ? $server->last_seen_at->diffForHumans() : 'Never',
                    'is_online' => $server->last_seen_at && $server->last_seen_at->diffInSeconds(now()) < 60, // Online if seen in last 60s
                    'location' => $server->location ?? 'Unknown',
                    'metadata' => $server->metadata, // Expose CPU/RAM/Disk stats
                    'api_token' => $server->api_token, // Expose API Token for deployment
                ];
            });

        return response()->json($servers);
    }

    public function create()
    {
        return view('assets.server.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'hostname' => 'required|string|unique:assets,name',
            'manager_url' => 'required|url',
        ]);

        try {
            $token = bin2hex(random_bytes(32)); // Generate 64-char token

            \Illuminate\Support\Facades\Log::info("Attempting to create server: {$request->hostname}");

            $server = Asset::create([
                'name' => $request->hostname,
                'type' => 'server',
                'status' => 'Offline', // Initial status
                'api_token' => $token,
            ]);

            \Illuminate\Support\Facades\Log::info("Server created successfully: {$server->id}");

            // Use User Provided URL
            $baseUrl = rtrim($request->manager_url, '/');

            return redirect()->route('assets.server.create')->with('success_token', [
                'token' => $token,
                'hostname' => $server->name,
                'url' => $baseUrl,
                'install_args' => "--install --token \"{$token}\" --server \"{$baseUrl}\" --name \"{$server->name}\""
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to create server: " . $e->getMessage());
            return redirect()->back()->withErrors(['msg' => 'Server creation failed: ' . $e->getMessage()])->withInput();
        }
    }

    public function edit($id)
    {
        $server = Asset::findOrFail($id);
        return view('assets.server.edit', compact('server'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'hostname' => 'required|string|unique:assets,name,' . $id,
            'ip_address' => 'nullable|ip',
            'os' => 'nullable|string',
        ]);

        $server = Asset::findOrFail($id);
        $server->update([
            'name' => $request->hostname,
            'ip_address' => $request->ip_address,
            'os_name' => $request->os,
        ]);

        return redirect()->route('assets.server')->with('success', 'Server updated successfully.');
    }

    public function destroy($id)
    {
        $server = Asset::findOrFail($id);
        $server->delete();

        return redirect()->back()->with('success', 'Server deleted successfully.');
    }
}
