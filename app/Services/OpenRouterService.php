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
        $this->baseUrl = config('services.openrouter.base_url');
    }

    public function ask(string $systemPrompt, string $userPrompt, array $history = [])
    {
        $messages = collect($history)
            ->filter(fn($msg) => $msg['content'] !== $userPrompt)
            ->take(-8)
            ->map(fn($msg) => [
                'role' => $msg['role'] === 'assistant' ? 'assistant' : 'user',
                'content' => $msg['content']
            ])
            ->toArray();

        array_unshift($messages, ['role' => 'system', 'content' => $systemPrompt]);
        
        $messages[] = ['role' => 'user', 'content' => $userPrompt];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
                'HTTP-Referer'  => config('app.url'),
                'X-Title'       => config('app.name'),
            ])->timeout(30)
            ->post($this->baseUrl.'/v1/chat/completions', [
                'model'       => config('services.openrouter.model', 'google/gemini-2.0-flash-lite-001'),
                'messages'    => $messages,
                'max_tokens'  => 2000,
                'temperature' => 0.5,
            ]);

            if ($response->successful()) {
                return $response->json()['choices'][0]['message']['content'] ?? "Maaf, otak AI sedang blank.";
            }

            Log::error('OpenRouter Error: ' . $response->status() . ' - ' . $response->body());
            return "Lentera AI sedang beristirahat sebentar. Coba tanya lagi ya.";

        } catch (\Exception $e) {
            Log::error('OpenRouter Exception: ' . $e->getMessage());
            return "Koneksi ke otak AI terganggu. Periksa internetmu.";
        }
    }

    public function embed(string $text)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/v1/embeddings', [
                'model' => 'google/gemini-embedding-001', 
                'input' => $text,
            ]);

            if ($response->successful()) {
                return $response->json()['data'][0]['embedding'];
            }

            Log::error('OpenRouter Embedding Error: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('OpenRouter Embedding Exception: ' . $e->getMessage());
            return null;
        }
    }
}