<?php

namespace App\AI\Providers;

use App\AI\Contracts\AIProviderInterface;
use App\AI\DTOs\AIResponse;
use Illuminate\Support\Facades\Http;

class OllamaProvider implements AIProviderInterface
{
    protected string $baseUrl;

    public function __construct(string $baseUrl = 'http://127.0.0.1:11434')
    {
        $this->baseUrl = config('services.ollama.base_url', env('OLLAMA_BASE_URL', $baseUrl));
    }

    public function getIdentifier(): string
    {
        return 'ollama';
    }

    public function generateText(string $prompt, array $options = []): AIResponse
    {
        $model = $options['model'] ?? 'llama3.2';
        $startTime = microtime(true);

        try {
            $response = Http::timeout(60)->post("{$this->baseUrl}/api/generate", [
                'model' => $model,
                'prompt' => $prompt,
                'system' => $options['system_prompt'] ?? 'You are an educational AI assistant.',
                'stream' => false,
            ]);

            $latency = (int) ((microtime(true) - $startTime) * 1000);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['response'] ?? '';
                $promptTokens = $data['prompt_eval_count'] ?? (int) (strlen($prompt) / 4);
                $completionTokens = $data['eval_count'] ?? (int) (strlen($content) / 4);

                return new AIResponse(
                    content: $content,
                    structuredData: null,
                    promptTokens: $promptTokens,
                    completionTokens: $completionTokens,
                    totalTokens: $promptTokens + $completionTokens,
                    costUsd: 0.0, // On-premise self-hosted free execution
                    provider: 'ollama',
                    model: $model,
                    latencyMs: $latency
                );
            }
        } catch (\Exception $e) {
            // Local server unreachable, fallback to Mock driver
        }

        return (new MockAIProvider())->generateText($prompt, $options);
    }

    public function generateStructured(string $prompt, array $schema, array $options = []): AIResponse
    {
        $model = $options['model'] ?? 'llama3.2';
        $startTime = microtime(true);

        try {
            $response = Http::timeout(60)->post("{$this->baseUrl}/api/generate", [
                'model' => $model,
                'prompt' => $prompt . "\nRespond with a valid JSON object matching the schema.",
                'format' => 'json',
                'stream' => false,
            ]);

            $latency = (int) ((microtime(true) - $startTime) * 1000);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['response'] ?? '{}';
                $structured = json_decode($content, true) ?: [];
                $promptTokens = $data['prompt_eval_count'] ?? (int) (strlen($prompt) / 4);
                $completionTokens = $data['eval_count'] ?? (int) (strlen($content) / 4);

                return new AIResponse(
                    content: $content,
                    structuredData: $structured,
                    promptTokens: $promptTokens,
                    completionTokens: $completionTokens,
                    totalTokens: $promptTokens + $completionTokens,
                    costUsd: 0.0,
                    provider: 'ollama',
                    model: $model,
                    latencyMs: $latency
                );
            }
        } catch (\Exception $e) {
            // Fallback
        }

        return (new MockAIProvider())->generateStructured($prompt, $schema, $options);
    }

    public function generateEmbedding(string $text): array
    {
        try {
            $response = Http::post("{$this->baseUrl}/api/embeddings", [
                'model' => 'nomic-embed-text',
                'prompt' => $text,
            ]);

            if ($response->successful()) {
                return $response->json('embedding') ?? [];
            }
        } catch (\Exception $e) {
            // Fallback
        }

        return (new MockAIProvider())->generateEmbedding($text);
    }
}
