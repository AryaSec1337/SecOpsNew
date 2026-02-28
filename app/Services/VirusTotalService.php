<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VirusTotalService
{
    protected $baseUrl = 'https://www.virustotal.com/api/v3';
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.virustotal.api_key');
    }

    /**
     * Upload a file to VirusTotal for scanning.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return array|null
     */
    public function scanFile($file)
    {
        try {
            $response = Http::withHeaders([
                'x-apikey' => $this->apiKey,
            ])->attach(
                'file', file_get_contents($file->getRealPath()), $file->getClientOriginalName()
            )->post("{$this->baseUrl}/files");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('VirusTotal Upload Failed: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('VirusTotal Service Error (Upload): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get analysis results by analysis ID (returned from scan).
     *
     * @param string $analysisId
     * @return array|null
     */
    public function getAnalysis($analysisId)
    {
        try {
            $response = Http::withHeaders([
                'x-apikey' => $this->apiKey,
            ])->get("{$this->baseUrl}/analyses/{$analysisId}");

            if ($response->successful()) {
                return $response->json();
            }
            
            Log::error('VirusTotal Analysis Fetch Failed: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('VirusTotal Service Error (Get Analysis): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Check file report by Hash (SHA-256).
     * Useful if file was already scanned previously.
     *
     * @param string $hash
     * @return array|null
     */
    public function getFileReport($hash)
    {
        try {
            $response = Http::withHeaders([
                'x-apikey' => $this->apiKey,
            ])->get("{$this->baseUrl}/files/{$hash}");

            if ($response->successful()) {
                return $response->json();
            }
            
            return null; // 404 means not found/not scanned yet
        } catch (\Exception $e) {
            Log::error('VirusTotal Service Error (Get Report): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Scan a URL.
     *
     * @param string $url
     * @return array|null
     */
    public function scanUrl($url)
    {
        try {
            $response = Http::withHeaders([
                'x-apikey' => $this->apiKey,
            ])->asForm()->post("{$this->baseUrl}/urls", [
                'url' => $url
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('VirusTotal URL Scan Failed: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('VirusTotal Service Error (Scan URL): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get URL Report by "URL Identifier".
     * VirusTotal URL IDs are base64 encoded URLs (without padding).
     *
     * @param string $url
     * @return array|null
     */
    public function getUrlReport($url)
    {
        try {
            // Generate VT URL Identifier: base64 without padding
            $id = rtrim(base64_encode($url), '=');
            
            $response = Http::withHeaders([
                'x-apikey' => $this->apiKey,
            ])->get("{$this->baseUrl}/urls/{$id}");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('VirusTotal Service Error (Get URL Report): ' . $e->getMessage());
            return null;
        }
    }
}
