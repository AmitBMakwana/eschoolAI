<?php

namespace App\AI\Providers;

use App\AI\Contracts\AIProviderInterface;
use App\AI\DTOs\AIResponse;
use Illuminate\Support\Facades\Http;

class ClaudeProvider implements AIProviderInterface
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct(?string $apiKey = null, string $baseUrl = 'https://api.anthropic.com/v1')
    {
        $this->apiKey = $apiKey ?? config('services.anthropic.api_key', env('ANTHROPIC_API_KEY', ''));
        $this->baseUrl = $baseUrl;
    }

    public function getIdentifier(): string
    {
        return 'claude';
    }

    public function generateText(string $prompt, array $options = []): AIResponse
    {
        if (empty($this->apiKey)) {
            return (new MockAIProvider())->generateText($prompt, $options);
        }

        $model = $options['model'] ?? 'claude-3-5-haiku-20241022';
        $startTime = microtime(true);

        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post("{$this->baseUrl}/messages", [
            'model' => $model,
            'max_tokens' => $options['max_tokens'] ?? 2048,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'system' => $options['system_prompt'] ?? 'You are an educational AI assistant for teachers and school administrators.',
            'temperature' => $options['temperature'] ?? 0.3,
        ]);

        $latency = (int) ((microtime(true) - $startTime) * 1000);

        if (!$response->successful()) {
            throw new \RuntimeException("Claude API error: " . $response->body());
        }

        $data = $response->json();
        $content = $data['content'][0]['text'] ?? '';
        $usage = $data['usage'] ?? [];
        $promptTokens = $usage['input_tokens'] ?? 0;
        $completionTokens = $usage['output_tokens'] ?? 0;
        $totalTokens = $promptTokens + $completionTokens;
        $cost = ($promptTokens * 0.0000008) + ($completionTokens * 0.000004);

        return new AIResponse(
            content: $content,
            structuredData: null,
            promptTokens: $promptTokens,
            completionTokens: $completionTokens,
            totalTokens: $totalTokens,
            costUsd: round($cost, 6),
            provider: 'claude',
            model: $model,
            latencyMs: $latency
        );
    }

    public function generateStructured(string $prompt, array $schema, array $options = []): AIResponse
    {
        if (empty($this->apiKey)) {
            return (new MockAIProvider())->generateStructured($prompt, $schema, $options);
        }

        $res = $this->generateText($prompt . "\nOutput valid JSON strictly matching the specified JSON schema.", $options);
        $structured = json_decode($res->content, true) ?: [];

        return new AIResponse(
            content: $res->content,
            structuredData: $structured,
            promptTokens: $res->promptTokens,
            completionTokens: $res->completionTokens,
            totalTokens: $res->totalTokens,
            costUsd: $res->costUsd,
            provider: 'claude',
            model: $res->model,
            latencyMs: $res->latencyMs
        );
    }

    public function generateEmbedding(string $text): array
    {
        // Anthropic delegates embeddings to VoyagAI or Fallback
        return (new MockAIProvider())->generateEmbedding($text);
    }
}
