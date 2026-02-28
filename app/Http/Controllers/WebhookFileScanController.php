<?php

namespace App\Http\Controllers;

use App\Models\WebhookFileScan;
use Illuminate\Http\Request;

class WebhookFileScanController extends Controller
{
    public function index()
    {
        $scans = WebhookFileScan::with('mitigationLog')->latest()->paginate(20);
        return view('webhook-scans.index', compact('scans'));
    }

    /**
     * JSON endpoint for AJAX polling — returns latest scans.
     */
    public function apiLatest()
    {
        $scans = WebhookFileScan::latest()->take(20)->get();

        return response()->json([
            'scans' => $scans->map(function ($scan) {
                return [
                    'id' => $scan->id,
                    'original_filename' => $scan->original_filename,
                    'sha256' => $scan->sha256,
                    'file_id' => $scan->file_id,
                    'server_hostname' => $scan->server_hostname,
                    'size_bytes' => $scan->size_bytes,
                    'verdict' => $scan->verdict,
                    'yara_result' => $scan->yara_result,
                    'clamav_result' => $scan->clamav_result,
                    'vt_result' => $scan->vt_result,
                    'created_at' => $scan->created_at->toIso8601String(),
                    'created_at_date' => $scan->created_at->format('M d, Y'),
                    'created_at_time' => $scan->created_at->format('H:i:s'),
                    'show_url' => route('webhook-scans.show', $scan->id),
                ];
            }),
            'total' => WebhookFileScan::count(),
            'clean_count' => WebhookFileScan::where('verdict', 'CLEAN')->count(),
            'suspicious_count' => WebhookFileScan::where('verdict', 'SUSPICIOUS')->count(),
            'malicious_count' => WebhookFileScan::where('verdict', 'MALICIOUS')->count(),
        ]);
    }

    public function show($id)
    {
        $scan = WebhookFileScan::with('mitigationLog')->findOrFail($id);
        return view('webhook-scans.show', compact('scan'));
    }
}
