<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HeartbeatController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Agent API Routes (Protected by Bearer Token inside Controller or Middleware)
// For simplicity, we'll handle token check in Controller or use a custom middleware if available.
Route::prefix('agent')->group(function () {
    Route::post('/heartbeat', [\App\Http\Controllers\Api\AgentController::class, 'heartbeat']);
    Route::post('/ack', [\App\Http\Controllers\Api\AgentController::class, 'acknowledge']);
});

// Legacy Logs Routes
Route::middleware([\App\Http\Middleware\CheckAgentToken::class])->group(function () {
    Route::post('/logs', [\App\Http\Controllers\Api\LogIngestionController::class, 'store']);
    // ... other Log routes
});

// SecOps Webhook Routes
Route::post('/webhook/file-scan', [\App\Http\Controllers\Api\WebhookScanController::class, 'handle']);
Route::post('/webhook/wazuh-alert', [\App\Http\Controllers\Api\WazuhWebhookController::class, 'handle']);
Route::post('/webhook/bad-ip', [\App\Http\Controllers\Api\BadIpWebhookController::class, 'handle']);
