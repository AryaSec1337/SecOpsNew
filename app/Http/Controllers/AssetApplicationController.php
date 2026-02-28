<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;

class AssetApplicationController extends Controller
{
    public function index()
    {
        $applications = Asset::where('type', 'application')->paginate(10);
        return view('assets.application.index', ['applications' => $applications]);
    }

    public function create()
    {
        return view('assets.application.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'vendor' => 'nullable|string|max:255',
            'version' => 'nullable|string|max:50',
            'type' => 'nullable|string|max:50',
            'criticality' => 'required|in:Low,Medium,High,Critical',
            'owner' => 'nullable|string|max:255',
            'status' => 'required|in:Active,Development,Warning,Offline',
            'cti_monitoring_enabled' => 'boolean'
        ]);

        Asset::create([
            'name' => $validated['name'],
            'type' => 'application',
            'vendor' => $validated['vendor'] ?? null,
            'app_version' => $validated['version'] ?? null,
            'app_type' => $validated['type'] ?? null,
            'criticality' => $validated['criticality'],
            'owner' => $validated['owner'] ?? null,
            'status' => $validated['status'],
            'cti_monitoring_enabled' => $request->has('cti_monitoring_enabled'),
            'last_seen_at' => now(),
        ]);

        return redirect()->route('assets.application.index')->with('success', 'Application asset created successfully.');
    }

    public function edit($id)
    {
        $application = Asset::findOrFail($id);
        return view('assets.application.edit', compact('application'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'vendor' => 'nullable|string|max:255',
            'version' => 'nullable|string|max:50',
            'type' => 'nullable|string|max:50',
            'criticality' => 'required|in:Low,Medium,High,Critical',
            'owner' => 'nullable|string|max:255',
            'status' => 'required|in:Active,Development,Warning,Offline',
            'cti_monitoring_enabled' => 'boolean'
        ]);

        $asset = Asset::findOrFail($id);
        $asset->update([
            'name' => $validated['name'],
            'vendor' => $validated['vendor'] ?? null,
            'app_version' => $validated['version'] ?? null,
            'app_type' => $validated['type'] ?? null,
            'criticality' => $validated['criticality'],
            'owner' => $validated['owner'] ?? null,
            'status' => $validated['status'],
            'cti_monitoring_enabled' => $request->has('cti_monitoring_enabled'),
        ]);

        return redirect()->route('assets.application.index')->with('success', 'Application asset updated successfully.');
    }

    public function list()
    {
        $applications = Asset::where('type', 'application')
            ->orderBy('last_seen_at', 'desc')
            ->get()
            ->map(function ($app) {
                return [
                    'id' => $app->id,
                    'name' => $app->name,
                    'version' => $app->app_version ?? 'N/A',
                    'vendor' => $app->vendor ?? 'Unknown',
                    'type' => $app->app_type ?? 'Generic',
                    'criticality' => $app->criticality ?? 'Low',
                    'owner' => $app->owner ?? 'Unassigned',
                    'status' => $app->status,
                    'cti_enabled' => $app->cti_monitoring_enabled,
                    'last_seen' => $app->last_seen_at ? $app->last_seen_at->diffForHumans() : 'Never',
                ];
            });

        return response()->json($applications);
    }
}
