<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

echo "=== WAZUH CONNECTION DEBUG ===\n";

$url = config('services.wazuh.url');
$username = config('services.wazuh.username');
$password = config('services.wazuh.password');

echo "URL Configured: " . $url . "\n";
echo "Username: " . $username . "\n";

// Test 1: Direct Authentication
echo "\nTesting Authentication Endpoint...\n";
try {
    // Manually constructing URL to verify logic
    if ($url && !str_ends_with($url, '/api/v1')) {
         $url = rtrim($url, '/') . '/api/v1'; 
    }
    
    $fullUrl = "{$url}/security/user/authenticate";
    echo "Endpoint: $fullUrl\n";

    $response = Http::withOptions(['verify' => false])
        ->timeout(5)
        ->withBasicAuth($username, $password)
        ->post($fullUrl);

    echo "Status Code: " . $response->status() . "\n";
    if ($response->successful()) {
        echo "SUCCESS! Token received.\n";
    } else {
        echo "FAILED. Response:\n";
        echo substr($response->body(), 0, 500) . "\n";
    }
} catch (\Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}

echo "\n=== LAST 20 LOG ENTRIES ===\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $lines = file($logFile);
    $lastLines = array_slice($lines, -20);
    foreach ($lastLines as $line) {
        echo $line;
    }
} else {
    echo "Log file not found.\n";
}
