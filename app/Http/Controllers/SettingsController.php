<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    public function index()
    {
        // Define default settings
        $defaults = [
            'retention_activity_logs' => ['value' => '30', 'type' => 'integer', 'description' => 'Days to keep activity logs'],
            'retention_fim_logs' => ['value' => '30', 'type' => 'integer', 'description' => 'Days to keep file integrity logs'],
            'retention_blocked_ips' => ['value' => '90', 'type' => 'integer', 'description' => 'Days to keep blocked IP history'],
            'retention_incidents' => ['value' => '365', 'type' => 'integer', 'description' => 'Days to keep incident reports'],
        ];

        // Seed if missing
        foreach ($defaults as $key => $data) {
            Setting::firstOrCreate(
                ['key' => $key],
                ['value' => $data['value'], 'type' => $data['type'], 'description' => $data['description']]
            );
        }

        $settings = Setting::all()->pluck('value', 'key');

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'retention_activity_logs' => 'required|integer|min:1',
            'retention_fim_logs' => 'required|integer|min:1',
            'retention_blocked_ips' => 'required|integer|min:1',
            'retention_incidents' => 'required|integer|min:1',
        ]);

        foreach ($data as $key => $value) {
            Setting::where('key', $key)->update(['value' => $value]);
        }

        return redirect()->back()->with('success', 'Retention settings updated successfully.');
    }

    public function cleanup()
    {
        try {
            Artisan::call('siem:cleanup');
            return redirect()->back()->with('success', 'Database cleanup triggered successfully. Old records have been pruned.');
        } catch (\Exception $e) {
            Log::error("Manual Cleanup Failed: " . $e->getMessage());
            return redirect()->back()->with('error', 'Cleanup failed: ' . $e->getMessage());
        }
    }
}
