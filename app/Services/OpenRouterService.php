<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenRouterService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.openrouter.key');
        $this->baseUrl = config('services.openrouter.base_url') . '/v1/chat/completions';
    }

    public function ask(string $systemPrompt, string $userPrompt, array $history = [])
    {
        $formattedHistory = array_map(function($msg) {
            return [
                'role' => $msg['role'] === 'ai' ? 'assistant' : 'user',
                'content' => $msg['content']
            ];
        }, $history);

        $messages = array_merge(
            [['role' => 'system', 'content' => $systemPrompt]],
            $formattedHistory,
            [['role' => 'user', 'content' => $userPrompt]]
        );

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => config('app.url'),
                'X-Title' => config('app.name'),
            ])->post($this->baseUrl, [
                'model' => 'google/gemini-2.0-flash-lite-001',
                'messages' => $messages,
                'max_tokens' => 1000,
                'temperature' => 0.7,
            ]);

            if ($response->successful()) {
                return $response->json()['choices'][0]['message']['content'];
            }

            Log::error('OpenRouter Error: ' . $response->status() . ' - ' . $response->body());
            return "Maaf, Lentera AI sedang sibuk. Coba lagi nanti ya.";

        } catch (\Exception $e) {
            Log::error('OpenRouter Exception: ' . $e->getMessage());
            return "Koneksi ke otak AI terputus. Pastikan internetmu lancar.";
        }
    }
}