<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected $apiKey;
    protected $models = [
        'gemini-2.0-flash',       // Primary (Fastest/Newest)
        'gemini-1.5-flash',       // Fallback 1 (Stable)
        'gemini-pro'              // Fallback 2 (Legacy)
    ];

    public function __construct()
    {
        $this->apiKey = config('services.google.api_key');
    }

    /**
     * Generate content based on a prompt.
     *
     * @param string $prompt
     * @return string|null
     */
    public function generateAnalysis($prompt)
    {
        if (!$this->apiKey) {
            Log::warning('Gemini API Key is missing.');
            return json_encode([
                'data' => [],
                'error' => "AI Analysis Unavailable: API Key missing."
            ]);
        }

        $safetySettings = [
            ["category" => "HARM_CATEGORY_HARASSMENT", "threshold" => "BLOCK_NONE"],
            ["category" => "HARM_CATEGORY_HATE_SPEECH", "threshold" => "BLOCK_NONE"],
            ["category" => "HARM_CATEGORY_SEXUALLY_EXPLICIT", "threshold" => "BLOCK_NONE"],
            ["category" => "HARM_CATEGORY_DANGEROUS_CONTENT", "threshold" => "BLOCK_NONE"],
        ];

        foreach ($this->models as $model) {
            try {
                $response = $this->queryApi($model, $prompt, $safetySettings);
                return $response;
            } catch (\Exception $e) {
                // If it's the last model, throw the error
                if ($model === end($this->models)) {
                    // Log the failure
                    Log::error("All Gemini models failed. Last error: " . $e->getMessage());
                    return json_encode([
                        'data' => [],
                        'error' => "All AI models failed. Please check your API Key or Quota."
                    ]);
                }
                // Otherwise continue to next model
                Log::warning("Gemini model $model failed: " . $e->getMessage() . ". Switching to next...");
                continue;
            }
        }
    }

    protected function queryApi($model, $prompt, $safetySettings)
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$this->apiKey}";

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'safetySettings' => $safetySettings,
            'generationConfig' => [
                'temperature' => 0.4,
                'topK' => 32,
                'topP' => 1,
                'maxOutputTokens' => 4096,
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        // Timeout customization
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new \Exception('Curl error: ' . curl_error($ch));
        }
        
        curl_close($ch);

        $decoded = json_decode($response, true);

        if ($httpCode !== 200) {
            $msg = $decoded['error']['message'] ?? "HTTP $httpCode";
            throw new \Exception("API Error: $msg");
        }

        if (isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
            return $decoded['candidates'][0]['content']['parts'][0]['text'];
        }

        throw new \Exception("Invalid API response format");
    }
}
