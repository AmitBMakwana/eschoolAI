<?php

namespace App\AI\Providers;

use App\AI\Contracts\AIProviderInterface;
use App\AI\DTOs\AIResponse;
use Illuminate\Support\Facades\Http;

class OpenAIProvider implements AIProviderInterface
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct(?string $apiKey = null, string $baseUrl = 'https://api.openai.com/v1')
    {
        $this->apiKey = $apiKey ?? config('services.openai.api_key', env('OPENAI_API_KEY', ''));
        $this->baseUrl = $baseUrl;
    }

    public function getIdentifier(): string
    {
        return 'openai';
    }

    public function generateText(string $prompt, array $options = []): AIResponse
    {
        if (empty($this->apiKey)) {
            // Fallback gracefully to mock in local dev environments if no API key is supplied
            return (new MockAIProvider())->generateText($prompt, $options);
        }

        $model = $options['model'] ?? 'gpt-4o-mini';
        $startTime = microtime(true);

        $response = Http::withToken($this->apiKey)
            ->timeout(60)
            ->post("{$this->baseUrl}/chat/completions", [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $options['system_prompt'] ?? 'You are an educational AI assistant for teachers and school administrators.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => $options['temperature'] ?? 0.3,
            ]);

        $latency = (int) ((microtime(true) - $startTime) * 1000);

        if (!$response->successful()) {
            throw new \RuntimeException("OpenAI API error: " . $response->body());
        }

        $data = $response->json();
        $content = $data['choices'][0]['message']['content'] ?? '';
        $usage = $data['usage'] ?? [];
        $promptTokens = $usage['prompt_tokens'] ?? 0;
        $completionTokens = $usage['completion_tokens'] ?? 0;
        $totalTokens = $usage['total_tokens'] ?? 0;

        // Pricing: $0.15/1M in, $0.60/1M out
        $cost = ($promptTokens * 0.00000015) + ($completionTokens * 0.0000006);

        return new AIResponse(
            content: $content,
            structuredData: null,
            promptTokens: $promptTokens,
            completionTokens: $completionTokens,
            totalTokens: $totalTokens,
            costUsd: round($cost, 6),
            provider: 'openai',
            model: $model,
            latencyMs: $latency
        );
    }

    public function generateStructured(string $prompt, array $schema, array $options = []): AIResponse
    {
        if (empty($this->apiKey)) {
            return (new MockAIProvider())->generateStructured($prompt, $schema, $options);
        }

        $model = $options['model'] ?? 'gpt-4o-mini';
        $startTime = microtime(true);

        $response = Http::withToken($this->apiKey)
            ->timeout(60)
            ->post("{$this->baseUrl}/chat/completions", [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => ($options['system_prompt'] ?? 'You are an educational AI assistant.') . "\nRespond ONLY in valid JSON strictly matching the required schema."],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature' => $options['temperature'] ?? 0.2,
            ]);

        $latency = (int) ((microtime(true) - $startTime) * 1000);

        if (!$response->successful()) {
            throw new \RuntimeException("OpenAI API error: " . $response->body());
        }

        $data = $response->json();
        $content = $data['choices'][0]['message']['content'] ?? '{}';
        $structured = json_decode($content, true) ?: [];
        $usage = $data['usage'] ?? [];
        $promptTokens = $usage['prompt_tokens'] ?? 0;
        $completionTokens = $usage['completion_tokens'] ?? 0;
        $totalTokens = $usage['total_tokens'] ?? 0;
        $cost = ($promptTokens * 0.00000015) + ($completionTokens * 0.0000006);

        return new AIResponse(
            content: $content,
            structuredData: $structured,
            promptTokens: $promptTokens,
            completionTokens: $completionTokens,
            totalTokens: $totalTokens,
            costUsd: round($cost, 6),
            provider: 'openai',
            model: $model,
            latencyMs: $latency
        );
    }

    public function generateEmbedding(string $text): array
    {
        if (empty($this->apiKey)) {
            return (new MockAIProvider())->generateEmbedding($text);
        }

        $response = Http::withToken($this->apiKey)
            ->post("{$this->baseUrl}/embeddings", [
                'model' => 'text-embedding-3-small',
                'input' => $text,
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException("OpenAI Embedding error: " . $response->body());
        }

        return $response->json('data.0.embedding') ?? [];
    }
}
