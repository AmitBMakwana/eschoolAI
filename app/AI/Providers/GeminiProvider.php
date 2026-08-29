<?php

namespace App\AI\Providers;

use App\AI\Contracts\AIProviderInterface;
use App\AI\DTOs\AIResponse;
use Illuminate\Support\Facades\Http;

class GeminiProvider implements AIProviderInterface
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct(?string $apiKey = null, string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta')
    {
        $this->apiKey = $apiKey ?? config('services.gemini.api_key', env('GEMINI_API_KEY', ''));
        $this->baseUrl = $baseUrl;
    }

    public function getIdentifier(): string
    {
        return 'gemini';
    }

    public function generateText(string $prompt, array $options = []): AIResponse
    {
        if (empty($this->apiKey)) {
            return (new MockAIProvider())->generateText($prompt, $options);
        }

        $model = $options['model'] ?? 'gemini-1.5-flash';
        $startTime = microtime(true);

        $response = Http::post("{$this->baseUrl}/models/{$model}:generateContent?key={$this->apiKey}", [
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ],
            'generationConfig' => [
                'temperature' => $options['temperature'] ?? 0.3,
            ]
        ]);

        $latency = (int) ((microtime(true) - $startTime) * 1000);

        if (!$response->successful()) {
            throw new \RuntimeException("Gemini API error: " . $response->body());
        }

        $data = $response->json();
        $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        $usage = $data['usageMetadata'] ?? [];
        $promptTokens = $usage['promptTokenCount'] ?? (int) (strlen($prompt) / 4);
        $completionTokens = $usage['candidatesTokenCount'] ?? (int) (strlen($content) / 4);
        $totalTokens = $promptTokens + $completionTokens;
        $cost = ($promptTokens * 0.000000075) + ($completionTokens * 0.0000003);

        return new AIResponse(
            content: $content,
            structuredData: null,
            promptTokens: $promptTokens,
            completionTokens: $completionTokens,
            totalTokens: $totalTokens,
            costUsd: round($cost, 6),
            provider: 'gemini',
            model: $model,
            latencyMs: $latency
        );
    }

    public function generateStructured(string $prompt, array $schema, array $options = []): AIResponse
    {
        if (empty($this->apiKey)) {
            return (new MockAIProvider())->generateStructured($prompt, $schema, $options);
        }

        $model = $options['model'] ?? 'gemini-1.5-flash';
        $startTime = microtime(true);

        $response = Http::post("{$this->baseUrl}/models/{$model}:generateContent?key={$this->apiKey}", [
            'contents' => [
                ['parts' => [['text' => $prompt . "\nOutput valid JSON strictly matching the schema."]]]
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'temperature' => $options['temperature'] ?? 0.2,
            ]
        ]);

        $latency = (int) ((microtime(true) - $startTime) * 1000);

        if (!$response->successful()) {
            throw new \RuntimeException("Gemini API error: " . $response->body());
        }

        $data = $response->json();
        $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
        $structured = json_decode($content, true) ?: [];
        $usage = $data['usageMetadata'] ?? [];
        $promptTokens = $usage['promptTokenCount'] ?? (int) (strlen($prompt) / 4);
        $completionTokens = $usage['candidatesTokenCount'] ?? (int) (strlen($content) / 4);
        $totalTokens = $promptTokens + $completionTokens;
        $cost = ($promptTokens * 0.000000075) + ($completionTokens * 0.0000003);

        return new AIResponse(
            content: $content,
            structuredData: $structured,
            promptTokens: $promptTokens,
            completionTokens: $completionTokens,
            totalTokens: $totalTokens,
            costUsd: round($cost, 6),
            provider: 'gemini',
            model: $model,
            latencyMs: $latency
        );
    }

    public function generateEmbedding(string $text): array
    {
        if (empty($this->apiKey)) {
            return (new MockAIProvider())->generateEmbedding($text);
        }

        $response = Http::post("{$this->baseUrl}/models/text-embedding-004:embedContent?key={$this->apiKey}", [
            'content' => ['parts' => [['text' => $text]]]
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException("Gemini Embedding error: " . $response->body());
        }

        return $response->json('embedding.values') ?? [];
    }
}
