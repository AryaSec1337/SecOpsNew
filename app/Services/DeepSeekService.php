<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class DeepSeekService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.deepseek.com/chat/completions';

    public function __construct()
    {
        $this->apiKey = config('services.deepseek.api_key');
    }

    /**
     * Generate content based on a prompt using DeepSeek API.
     *
     * @param string $prompt
     * @return string|null
     */
    public function generateAnalysis($prompt)
    {
        if (!$this->apiKey) {
            Log::warning('DeepSeek API Key is missing.');
            return json_encode([
                'error' => "AI Analysis Unavailable: DeepSeek API Key missing."
            ]);
        }

        // DeepSeek is OpenAI compatible
        $payload = [
            'model' => 'deepseek-chat',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful cybersecurity assistant.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.5,
            'max_tokens' => 4096,
            'top_p' => 1,
            'frequency_penalty' => 0,
            'presence_penalty' => 0,
            'stream' => false
        ];

        return $this->queryApi($payload);
    }

    protected function queryApi($payload)
    {
        $ch = curl_init($this->baseUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        
        // Timeout customization
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            Log::error("DeepSeek CURL Error: $error");
            return "AI Analysis Error: Connection failed ($error)";
        }
        
        curl_close($ch);

        $decoded = json_decode($response, true);

        if ($httpCode !== 200) {
            $msg = $decoded['error']['message'] ?? "HTTP $httpCode";
            Log::error("DeepSeek API Error: $msg");
            Log::debug("DeepSeek Response: $response");
            return "AI Analysis Failed: $msg";
        }

        if (isset($decoded['choices'][0]['message']['content'])) {
            return $decoded['choices'][0]['message']['content'];
        }

        Log::error("DeepSeek Invalid Format: $response");
        return "Error parsing DeepSeek response.";
    }
}
