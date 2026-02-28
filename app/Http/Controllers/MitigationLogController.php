<?php

namespace App\Http\Controllers;

use App\Models\MitigationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Mail\InvestigationCreated;
use App\Mail\InvestigationStatusUpdated;
use Illuminate\Support\Facades\Mail;

class MitigationLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stats = [
            'pending' => MitigationLog::where('status', 'Pending')->count(),
            'in_progress' => MitigationLog::where('status', 'In Progress')->count(),
            'resolved' => MitigationLog::where('status', 'Resolved')->count(),
            'email_phishing' => MitigationLog::where('type', 'Email Phishing')->count(),
            'file_check' => MitigationLog::where('type', 'File Check')->count(),
            'domain_check' => MitigationLog::where('type', 'Domain Check')->count(),
            'siem_incident' => MitigationLog::where(function($q) { $q->whereNull('type')->orWhere('type', 'General'); })->count(),
        ];

        $query = MitigationLog::with('user')->latest();

    if (request('type')) {
        if (request('type') === 'General') {
            $query->where(function($q) {
                $q->whereNull('type')->orWhere('type', 'General');
            });
        } else {
            $query->where('type', request('type'));
        }
    }

    $mitigations = $query->paginate(10)->withQueryString();

    if (request()->ajax()) {
        return view('mitigation.partials.logs-table', compact('mitigations'));
    }

    return view('mitigation.index', compact('mitigations', 'stats'));
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $fileAnalysisLogs = \App\Models\FileAnalysisLog::where('status', 'completed')
            ->latest()->get(['id', 'file_name', 'file_hash_sha256', 'result', 'created_at']);
        $urlAnalysisLogs = \App\Models\UrlAnalysisLog::where('status', 'completed')
            ->latest()->get(['id', 'url', 'result', 'created_at']);

        $fileAnalysisData = $this->transformFileAnalysisData($fileAnalysisLogs);
        $urlAnalysisData = $this->transformUrlAnalysisData($urlAnalysisLogs);

        return view('mitigation.create', compact('fileAnalysisData', 'urlAnalysisData'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Sanitize inputs: Convert empty strings to null for optional fields
        // This ensures validation rules like 'integer' and 'date' don't fail on empty strings
        if ($request->input('file_analysis_log_id') === '') $request->merge(['file_analysis_log_id' => null]);
        if ($request->input('url_analysis_log_id') === '') $request->merge(['url_analysis_log_id' => null]);
        if ($request->input('incident_time') === '') $request->merge(['incident_time' => null]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'incident_time' => 'nullable|date',
            'description' => 'nullable|string',
            'event_log' => 'nullable|string',
            'evidence_before' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'evidence_after' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'system_affected' => 'nullable|string|max:255',
            'reporter_email' => 'nullable|string|max:255',
        'reporter_department' => 'nullable|string|max:255',
            'type' => 'nullable|string|in:General,Email Phishing,File Check,Domain Check',
            'attack_classification' => 'nullable|string|in:True Attack,False Attack',
            'email_subject' => 'nullable|string|max:255',
            'email_sender' => 'nullable|string|max:255',
            'email_recipient' => 'nullable|string|max:255',
            'email_headers' => 'nullable|string',
            'file_analysis_log_id' => 'nullable|integer|exists:file_analysis_logs,id',
            'url_analysis_log_id' => 'nullable|integer|exists:url_analysis_logs,id',
            'analysis_summary' => 'nullable|string',
            'evidence_files.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,eml,msg,txt|max:10240',
            'priority' => 'nullable|string|in:Low,Medium,High,Critical',
            'severity' => 'nullable|string|in:Low,Medium,High,Critical',
            'hostname' => 'nullable|string|max:255',
            'internal_ip' => 'nullable|string|max:255',
            'os' => 'nullable|string|max:255',
            'network_zone' => 'nullable|string|max:255',
        ]);

        $validated['status'] = 'Pending'; // Force status to Pending on create
        $validated['user_id'] = Auth::id();
        $validated['mitigated_at'] = now();

        // Handle File Uploads (Legacy Single Files)
        if ($request->hasFile('evidence_before')) {
            $validated['evidence_before'] = $request->file('evidence_before')->store('evidence', 'public');
        }

        if ($request->hasFile('evidence_after')) {
            $validated['evidence_after'] = $request->file('evidence_after')->store('evidence', 'public');
        }

        $mitigationLog = MitigationLog::create($validated);

        // Handle Multi-File Uploads
        if ($request->hasFile('evidence_files')) {
            foreach ($request->file('evidence_files') as $file) {
                $path = $file->store('evidence', 'public');
                $mitigationLog->files()->create([
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        // Auto-create timeline entry
        $mitigationLog->details()->create([
            'action' => 'Investigation Created',
            'description' => 'Initial report created. Status automatically set to "Pending".',
            'log_date' => now(),
            'user_id' => Auth::id(),
        ]);

        // Send Email Notification
        $recipient = env('MAIL_NOTIFICATION_TO');
        if ($recipient) {
            \Illuminate\Support\Facades\Mail::to($recipient)->send(new InvestigationCreated($mitigationLog));
        }

        return redirect()->route('mitigation-logs.index')->with('success', 'Investigation Log recorded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(MitigationLog $mitigationLog)
    {
        $mitigationLog->load(['user', 'details.user', 'fileAnalysis', 'urlAnalysis', 'ipAnalysis', 'files']);
        return view('mitigation.show', compact('mitigationLog'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MitigationLog $mitigationLog)
    {
        $mitigationLog->load(['fileAnalysis', 'urlAnalysis', 'ipAnalysis', 'files']);

        $fileAnalysisLogs = \App\Models\FileAnalysisLog::where('status', 'completed')
            ->latest()->get(['id', 'file_name', 'file_hash_sha256', 'result', 'created_at']);
        $urlAnalysisLogs = \App\Models\UrlAnalysisLog::where('status', 'completed')
            ->latest()->get(['id', 'url', 'result', 'created_at']);
        $ipAnalyses = \App\Models\IpAnalysis::latest()->get(['id', 'ip_address', 'risk_score', 'geo_data', 'virustotal_data', 'abuseipdb_data', 'greynoise_data', 'alienvault_data', 'created_at']);

        $fileAnalysisData = $this->transformFileAnalysisData($fileAnalysisLogs);
        $urlAnalysisData = $this->transformUrlAnalysisData($urlAnalysisLogs);

        return view('mitigation.edit', compact('mitigationLog', 'fileAnalysisData', 'urlAnalysisData', 'ipAnalyses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MitigationLog $mitigationLog)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'incident_time' => 'nullable|date',
            'description' => 'nullable|string',
            'event_log' => 'nullable|string',
            'evidence_before' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'evidence_after' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'system_affected' => 'nullable|string|max:255',
            'reporter_email' => 'nullable|string|max:255',
            'reporter_department' => 'nullable|string|max:255',
            'status' => 'required|in:Pending,In Progress,Resolved,Monitor',
            'type' => 'nullable|string|in:General,Email Phishing,File Check,Domain Check',
            'attack_classification' => 'nullable|string|in:True Attack,False Attack',
            'email_subject' => 'nullable|string|max:255',
            'email_sender' => 'nullable|string|max:255',
            'email_recipient' => 'nullable|string|max:255',
            'email_headers' => 'nullable|string',
            'file_analysis_log_id' => 'nullable|integer|exists:file_analysis_logs,id',
            'url_analysis_log_id' => 'nullable|integer|exists:url_analysis_logs,id',
            'analysis_summary' => 'nullable|string',
            'evidence_files.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,eml,msg,txt|max:10240',
            'priority' => 'nullable|string|in:Low,Medium,High,Critical',
            'severity' => 'nullable|string|in:Low,Medium,High,Critical',
            'hostname' => 'nullable|string|max:255',
            'internal_ip' => 'nullable|string|max:255',
            'os' => 'nullable|string|max:255',
            'network_zone' => 'nullable|string|max:255',
            'analyst_decision' => 'nullable|string',
            'ip_analysis_id' => 'nullable|integer|exists:ip_analyses,id',
        ]);

        $oldStatus = $mitigationLog->status;

        // Handle File Uploads (Legacy Single Files)
        if ($request->hasFile('evidence_before')) {
            // Delete old file
            if ($mitigationLog->evidence_before) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($mitigationLog->evidence_before);
            }
            $validated['evidence_before'] = $request->file('evidence_before')->store('evidence', 'public');
        }

        if ($request->hasFile('evidence_after')) {
            // Delete old file
            if ($mitigationLog->evidence_after) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($mitigationLog->evidence_after);
            }
            $validated['evidence_after'] = $request->file('evidence_after')->store('evidence', 'public');
        }

        $mitigationLog->update($validated);

        // Handle Multi-File Uploads
        if ($request->hasFile('evidence_files')) {
            foreach ($request->file('evidence_files') as $file) {
                $path = $file->store('evidence', 'public');
                $mitigationLog->files()->create([
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        // Check for status change and log it
        if ($oldStatus !== $validated['status']) {
            $mitigationLog->details()->create([
                'action' => 'Status Updated',
                'description' => "Status changed from '{$oldStatus}' to '{$validated['status']}'.",
                'log_date' => now(),
                'user_id' => Auth::id(),
            ]);

            // Send Email Notification
            $recipient = env('MAIL_NOTIFICATION_TO');
            if ($recipient) {
                \Illuminate\Support\Facades\Mail::to($recipient)->send(new InvestigationStatusUpdated($mitigationLog, $oldStatus, $validated['status']));
            }
        }

        return redirect()->route('mitigation-logs.index')->with('success', 'Investigation Log updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MitigationLog $mitigationLog)
    {
        if ($mitigationLog->evidence_before) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($mitigationLog->evidence_before);
        }
        if ($mitigationLog->evidence_after) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($mitigationLog->evidence_after);
        }
        foreach ($mitigationLog->files as $file) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($file->file_path);
        }

        $mitigationLog->delete();

        return redirect()->route('mitigation-logs.index')->with('success', 'Investigation Log deleted successfully.');
    }

    public function storeDetail(Request $request, MitigationLog $mitigationLog)
    {
        $validated = $request->validate([
            'action' => 'required|string|max:255',
            'description' => 'required|string',
            'log_date' => 'required|date',
        ]);

        $validated['user_id'] = Auth::id();

        $mitigationLog->details()->create($validated);

        return redirect()->route('mitigation-logs.show', $mitigationLog)->with('success', 'Detail log added successfully.');
    }

    /**
     * Search emails for autocomplete.
     */
    public function searchEmails(Request $request)
    {
        $query = $request->get('query');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $emails = \App\Models\EmailsUser::where('email_address', 'LIKE', "%{$query}%")
                    ->orWhere('display_name', 'LIKE', "%{$query}%")
                    ->limit(10)
                    ->get(['email_address', 'display_name', 'department']);

        return response()->json($emails);
    }

    /**
     * Download PDF report for a resolved investigation.
     */
    public function downloadReport(MitigationLog $mitigationLog)
    {
        $mitigationLog->load(['user', 'details.user', 'ipAnalysis', 'files']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('mitigation.investigation_report', [
            'log' => $mitigationLog,
        ]);

        $pdf->setPaper('a4', 'portrait');

        $filename = 'Investigation_Report_' . $mitigationLog->id . '_' . now()->format('Ymd') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Search file analysis logs for autocomplete.
     */
    public function searchFiles(Request $request)
    {
        $query = $request->get('query');

        $logs = \App\Models\FileAnalysisLog::where('status', 'completed')
            ->where('file_name', 'LIKE', "%{$query}%")
            ->latest()
            ->limit(15)
            ->get(['id', 'file_name', 'file_hash_sha256', 'result', 'created_at']);

        $results = $logs->map(function ($log) {
            $attr = $log->result['attributes'] ?? $log->result ?? [];
            $stats = $attr['last_analysis_stats'] ?? $attr['stats'] ?? [];
            $malicious = ($stats['malicious'] ?? 0);
            $total = array_sum($stats);

            return [
                'id' => $log->id,
                'file_name' => $log->file_name,
                'hash' => substr($log->file_hash_sha256, 0, 16) . '...',
                'date' => $log->created_at->format('M d, Y H:i'),
                'malicious' => $malicious,
                'total' => $total,
                'verdict' => $malicious > 0 ? 'Malicious' : 'Clean',
            ];
        });

        return response()->json($results);
    }

    /**
     * Search URL analysis logs for autocomplete.
     */
    public function searchUrls(Request $request)
    {
        $query = $request->get('query');

        $logs = \App\Models\UrlAnalysisLog::where('status', 'completed')
            ->where('url', 'LIKE', "%{$query}%")
            ->latest()
            ->limit(15)
            ->get(['id', 'url', 'result', 'created_at']);

        $results = $logs->map(function ($log) {
            $attr = $log->result['attributes'] ?? $log->result ?? [];
            $stats = $attr['last_analysis_stats'] ?? $attr['stats'] ?? [];
            $malicious = ($stats['malicious'] ?? 0);
            $total = array_sum($stats);

            return [
                'id' => $log->id,
                'url' => $log->url,
                'date' => $log->created_at->format('M d, Y H:i'),
                'malicious' => $malicious,
                'total' => $total,
                'verdict' => $malicious > 0 ? 'Malicious' : 'Clean',
            ];
        });

        return response()->json($results);
    }

    /**
     * Transform file analysis logs into a flat array for JSON serialization.
     */
    private function transformFileAnalysisData($logs)
    {
        return $logs->map(function ($log) {
            $attr = $log->result['attributes'] ?? $log->result ?? [];
            $stats = $attr['last_analysis_stats'] ?? $attr['stats'] ?? [];
            $malicious = $stats['malicious'] ?? 0;
            $suspicious = $stats['suspicious'] ?? 0;
            $harmless = $stats['harmless'] ?? 0;
            $undetected = $stats['undetected'] ?? 0;
            $total = array_sum($stats);
            return [
                'id' => $log->id,
                'file_name' => $log->file_name,
                'hash' => $log->file_hash_sha256,
                'date' => $log->created_at->format('M d, Y H:i'),
                'malicious' => $malicious,
                'suspicious' => $suspicious,
                'harmless' => $harmless,
                'undetected' => $undetected,
                'total' => $total,
                'verdict' => $malicious > 0 ? 'Malicious' : 'Clean',
            ];
        })->values()->toArray();
    }

    /**
     * Transform URL analysis logs into a flat array for JSON serialization.
     */
    private function transformUrlAnalysisData($logs)
    {
        return $logs->map(function ($log) {
            $attr = $log->result['attributes'] ?? $log->result ?? [];
            $stats = $attr['last_analysis_stats'] ?? $attr['stats'] ?? [];
            $malicious = $stats['malicious'] ?? 0;
            $suspicious = $stats['suspicious'] ?? 0;
            $harmless = $stats['harmless'] ?? 0;
            $undetected = $stats['undetected'] ?? 0;
            $total = array_sum($stats);
            return [
                'id' => $log->id,
                'url' => $log->url,
                'date' => $log->created_at->format('M d, Y H:i'),
                'malicious' => $malicious,
                'suspicious' => $suspicious,
                'harmless' => $harmless,
                'undetected' => $undetected,
                'total' => $total,
                'verdict' => $malicious > 0 ? 'Malicious' : 'Clean',
            ];
        })->values()->toArray();
    }
}
