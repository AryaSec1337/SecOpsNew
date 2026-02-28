<?php
// Test Payload without file bytes
$url = "http://127.0.0.1:8000/api/webhook/file-scan";

$payload = json_encode([
  "file_id" => "8f91d7c4-4b7e-4e3c-91f1-8d77a6d2b1e2",
  "sha256" => "44d88612fea8a8f36de82e1278abb02f",
  "size_bytes" => 834221,
  "uploaded_at" => "2026-02-27T14:22:10+07:00",
  "fullpath" => "/www/html/pins/uploads/invoice.pdf", 
  "original_filename" => "invoice.pdf",
  "endpoint" => "/api/upload/claim",
  "request_id" => "req-abc123"
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if(curl_errno($ch)){
    echo "Curl error: " . curl_error($ch) . "\n";
} else {
    echo "HTTP Status: " . $httpCode . "\n";
    $decoded = json_decode($response, true);
    if($decoded) {
        echo json_encode($decoded, JSON_PRETTY_PRINT)."\n";
    } else {
        echo $response."\n";
    }
}
curl_close($ch);
