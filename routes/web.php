<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [\App\Http\Controllers\AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);
Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

Route::get('/debug-stats', function() {
    $logs = \App\Models\FileIntegrityLog::get();
    $maliciousCount = $logs->filter(function($log) {
        return ($log->details['virustotal']['stats']['malicious'] ?? 0) > 0;
    })->count();
    
    return response()->json([
        'malicious_count' => $maliciousCount,
        'total_logs' => $logs->count(),
    ]);
});



Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');

Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index'])
    ->middleware('auth')
    ->name('logs');

Route::get('/dashboard/live', [\App\Http\Controllers\DashboardController::class, 'liveStats'])
    ->middleware('auth')
    ->name('dashboard.live');

// Navbar Notifications
Route::get('/notifications/pending', [\App\Http\Controllers\NotificationController::class, 'pending'])
    ->middleware('auth')
    ->name('notifications.pending');

// Blocked IPs
Route::get('blocked-ips/json', [\App\Http\Controllers\BlockedIpController::class, 'list'])
    ->middleware('auth')
    ->name('blocked-ips.list');

Route::resource('blocked-ips', \App\Http\Controllers\BlockedIpController::class)
    ->only(['index', 'store', 'destroy'])
    ->middleware('auth');

// Autocomplete Search Endpoints
Route::get('/mitigation-logs/search-emails', [\App\Http\Controllers\MitigationLogController::class, 'searchEmails'])->name('mitigation-logs.search-emails')->middleware('auth');
Route::get('/mitigation-logs/search-files', [\App\Http\Controllers\MitigationLogController::class, 'searchFiles'])->name('mitigation-logs.search-files')->middleware('auth');
Route::get('/mitigation-logs/search-urls', [\App\Http\Controllers\MitigationLogController::class, 'searchUrls'])->name('mitigation-logs.search-urls')->middleware('auth');

Route::resource('mitigation-logs', \App\Http\Controllers\MitigationLogController::class)
    ->middleware('auth');

Route::post('/mitigation-logs/{mitigation_log}/details', [\App\Http\Controllers\MitigationLogController::class, 'storeDetail'])
    ->name('mitigation-logs.details.store')
    ->middleware('auth');

Route::get('/mitigation-logs/{mitigation_log}/report', [\App\Http\Controllers\MitigationLogController::class, 'downloadReport'])
    ->name('mitigation-logs.report')
    ->middleware('auth');



Route::prefix('assets')->name('assets.')->middleware('auth')->group(function () {
    Route::get('/server/create', [\App\Http\Controllers\AssetServerController::class, 'create'])->name('server.create');
    Route::post('/server', [\App\Http\Controllers\AssetServerController::class, 'store'])->name('store');
    
    Route::get('/server/{id}/edit', [\App\Http\Controllers\AssetServerController::class, 'edit'])->name('server.edit');
    Route::put('/server/{id}', [\App\Http\Controllers\AssetServerController::class, 'update'])->name('server.update');
    Route::delete('/server/{id}', [\App\Http\Controllers\AssetServerController::class, 'destroy'])->name('server.destroy');

    Route::get('/server', [\App\Http\Controllers\AssetServerController::class, 'index'])->name('server');
    Route::get('/server/json', [\App\Http\Controllers\AssetServerController::class, 'list'])->name('server.json');
    
    Route::get('/application', [\App\Http\Controllers\AssetApplicationController::class, 'index'])->name('application.index');
    Route::get('/application/create', [\App\Http\Controllers\AssetApplicationController::class, 'create'])->name('application.create');
    Route::post('/application', [\App\Http\Controllers\AssetApplicationController::class, 'store'])->name('application.store');
    Route::get('/application/{id}/edit', [\App\Http\Controllers\AssetApplicationController::class, 'edit'])->name('application.edit');
    Route::put('/application/{id}', [\App\Http\Controllers\AssetApplicationController::class, 'update'])->name('application.update');
    Route::get('/application/json', [\App\Http\Controllers\AssetApplicationController::class, 'list'])->name('application.json');

    // Email Users
    Route::get('/email', [\App\Http\Controllers\AssetEmailController::class, 'index'])->name('email.index');
    Route::post('/email', [\App\Http\Controllers\AssetEmailController::class, 'store'])->name('email.store');
    Route::get('/email/json', [\App\Http\Controllers\AssetEmailController::class, 'list'])->name('email.json');
    Route::put('/email/{id}', [\App\Http\Controllers\AssetEmailController::class, 'update'])->name('email.update');
    Route::delete('/email/{id}', [\App\Http\Controllers\AssetEmailController::class, 'destroy'])->name('email.destroy');
    Route::post('/email/import', [\App\Http\Controllers\AssetEmailController::class, 'import'])->name('email.import');
});

// CTI Routes
Route::prefix('cti')->name('cti.')->middleware('auth')->group(function () {
    Route::get('/domains', [\App\Http\Controllers\CtiController::class, 'domainMonitoring'])->name('domain.index');
    Route::post('/domains', [\App\Http\Controllers\CtiController::class, 'storeDomain'])->name('domain.store');
    Route::get('/domains/{id}', [\App\Http\Controllers\CtiController::class, 'show'])->name('domain.show');
    Route::post('/domains/{id}/scan', [\App\Http\Controllers\CtiController::class, 'scan'])->name('domain.scan');
    Route::delete('/domains/{id}', [\App\Http\Controllers\CtiController::class, 'destroy'])->name('domain.destroy');
    
    // External CTI
    Route::get('/external', [\App\Http\Controllers\ExternalCtiController::class, 'index'])->name('external.index');
    Route::get('/external/group/{name}', [\App\Http\Controllers\ExternalCtiController::class, 'showGroup'])->name('external.group');
    Route::get('/external/negotiation/{name}/{chatId}', [\App\Http\Controllers\ExternalCtiController::class, 'showNegotiation'])->name('external.negotiation');

    // MITRE ATT&CK
    Route::get('/mitre', [\App\Http\Controllers\MitreController::class, 'index'])->name('mitre.index');
});

// Investigation Tools
Route::prefix('investigation')->name('investigation.')->middleware('auth')->group(function () {
    Route::get('/ip-analyzer', [\App\Http\Controllers\IpAnalyzerController::class, 'index'])->name('ip-analyzer.index');
    Route::get('/ip-analyzer/history', [\App\Http\Controllers\IpAnalyzerController::class, 'history'])->name('ip-analyzer.history');
    Route::post('/ip-analyzer/analyze', [\App\Http\Controllers\IpAnalyzerController::class, 'analyze'])->name('ip-analyzer.analyze');
    Route::post('/ip-analyzer/export', [\App\Http\Controllers\IpAnalyzerController::class, 'export'])->name('ip-analyzer.export');

    // Email Analyzer
    Route::get('/email-analyzer', [\App\Http\Controllers\EmailAnalyzerController::class, 'index'])->name('email-analyzer.index');
    Route::post('/email-analyzer/analyze', [\App\Http\Controllers\EmailAnalyzerController::class, 'analyze'])->name('email-analyzer.analyze');
    // Email Analyzer Rules
    Route::get('/email-analyzer/rules', [\App\Http\Controllers\EmailAnalyzerController::class, 'getRules'])->name('email-analyzer.rules.index');
    Route::post('/email-analyzer/rules', [\App\Http\Controllers\EmailAnalyzerController::class, 'storeRule'])->name('email-analyzer.rules.store');
    Route::delete('/email-analyzer/rules/{id}', [\App\Http\Controllers\EmailAnalyzerController::class, 'destroyRule'])->name('email-analyzer.rules.destroy');
    
    // Email Analyzer History
    Route::get('/email-analyzer/history', [\App\Http\Controllers\EmailAnalyzerController::class, 'history'])->name('email-analyzer.history.index');
    Route::get('/email-analyzer/history/{id}', [\App\Http\Controllers\EmailAnalyzerController::class, 'showHistory'])->name('email-analyzer.history.show');
    Route::delete('/email-analyzer/history/{id}', [\App\Http\Controllers\EmailAnalyzerController::class, 'destroyHistory'])->name('email-analyzer.history.destroy');

    // YARA Generator
    Route::get('/yara-generator', [\App\Http\Controllers\YarGenController::class, 'index'])->name('yargen.index');
    Route::post('/yara-generator/generate', [\App\Http\Controllers\YarGenController::class, 'generate'])->name('yargen.generate');
});

// Settings & Retention
Route::prefix('settings')->name('settings.')->middleware('auth')->group(function () {
    Route::get('/', [\App\Http\Controllers\SettingsController::class, 'index'])->name('index');
    Route::put('/', [\App\Http\Controllers\SettingsController::class, 'update'])->name('update');
    Route::post('/cleanup', [\App\Http\Controllers\SettingsController::class, 'cleanup'])->name('cleanup');
    
    // Backups
    Route::get('/backup/{id}/download', [\App\Http\Controllers\BackupController::class, 'download'])->name('backup.download');
    Route::delete('/backup/{id}', [\App\Http\Controllers\BackupController::class, 'destroy'])->name('backup.destroy');
});

// File Analyst
Route::prefix('file-analyst')->name('file-analyst.')->middleware('auth')->group(function () {
    Route::get('/', [\App\Http\Controllers\FileAnalystController::class, 'index'])->name('index');
    Route::post('/analyze', [\App\Http\Controllers\FileAnalystController::class, 'analyze'])->name('analyze');
    Route::get('/status/{id}', [\App\Http\Controllers\FileAnalystController::class, 'checkStatus'])->name('checkStatus');
});

// Webhook File Monitoring
Route::prefix('webhook-scans')->name('webhook-scans.')->middleware('auth')->group(function () {
    Route::get('/', [\App\Http\Controllers\WebhookFileScanController::class, 'index'])->name('index');
    Route::get('/api/latest', [\App\Http\Controllers\WebhookFileScanController::class, 'apiLatest'])->name('api.latest');
    Route::get('/{id}', [\App\Http\Controllers\WebhookFileScanController::class, 'show'])->name('show');
});

// Webhook Alerts (FUM Ticketing)
Route::prefix('webhook-alerts')->name('webhook-alerts.')->middleware('auth')->group(function () {
    Route::get('/', [\App\Http\Controllers\WebhookAlertController::class, 'index'])->name('index');
    Route::get('/{webhookAlert}', [\App\Http\Controllers\WebhookAlertController::class, 'show'])->name('show');
    Route::patch('/{webhookAlert}/status', [\App\Http\Controllers\WebhookAlertController::class, 'updateStatus'])->name('update-status');
});

// Wazuh Alerts (SIEM)
Route::prefix('wazuh-alerts')->name('wazuh-alerts.')->middleware('auth')->group(function () {
    Route::get('/', [\App\Http\Controllers\WazuhAlertController::class, 'index'])->name('index');
    Route::post('/bulk-resolve', [\App\Http\Controllers\WazuhAlertController::class, 'bulkResolve'])->name('bulk-resolve');
    Route::get('/{wazuhAlert}', [\App\Http\Controllers\WazuhAlertController::class, 'show'])->name('show');
    Route::patch('/{wazuhAlert}/status', [\App\Http\Controllers\WazuhAlertController::class, 'updateStatus'])->name('update-status');
});

// Phishing Link Scanner
Route::prefix('url-scanner')->name('url-scanner.')->middleware('auth')->group(function () {
    Route::get('/', [\App\Http\Controllers\UrlScannerController::class, 'index'])->name('index');
    Route::post('/scan', [\App\Http\Controllers\UrlScannerController::class, 'scan'])->name('scan');
    Route::get('/status/{id}', [\App\Http\Controllers\UrlScannerController::class, 'checkStatus'])->name('checkStatus');
});
// Security Reports (Core Feature)
Route::prefix('reports')->name('reports.')->middleware('auth')->group(function () {
    Route::get('/', [\App\Http\Controllers\SecurityReportController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\SecurityReportController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\SecurityReportController::class, 'store'])->name('store');
    Route::post('/analyze', [\App\Http\Controllers\SecurityReportController::class, 'analyze'])->name('analyze');
    Route::get('/{id}', [\App\Http\Controllers\SecurityReportController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [\App\Http\Controllers\SecurityReportController::class, 'edit'])->name('edit');
    Route::put('/{id}', [\App\Http\Controllers\SecurityReportController::class, 'update'])->name('update');
    Route::get('/{id}/pdf', [\App\Http\Controllers\SecurityReportController::class, 'downloadPdf'])->name('exportPdf');
    Route::delete('/{id}', [\App\Http\Controllers\SecurityReportController::class, 'destroy'])->name('destroy');
});
// Serve Agent Script
Route::get('/agent.py', function () {
    $path = base_path('tools/secops_agent/secops_agent.py');
    if (!file_exists($path)) {
        abort(404);
    }
    return response()->file($path, ['Content-Type' => 'text/x-python']);
});
