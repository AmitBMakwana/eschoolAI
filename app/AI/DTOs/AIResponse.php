<?php

namespace App\AI\DTOs;

class AIResponse
{
    public function __construct(
        public string $content,
        public array|object|null $structuredData = null,
        public int $promptTokens = 0,
        public int $completionTokens = 0,
        public int $totalTokens = 0,
        public float $costUsd = 0.0,
        public string $provider = 'mock',
        public string $model = 'mock-model',
        public int $latencyMs = 0,
    ) {}

    public function toArray(): array
    {
        return [
            'content' => $this->content,
            'structured_data' => $this->structuredData,
            'usage' => [
                'prompt_tokens' => $this->promptTokens,
                'completion_tokens' => $this->completionTokens,
                'total_tokens' => $this->totalTokens,
                'cost_usd' => $this->costUsd,
                'latency_ms' => $this->latencyMs,
            ],
            'provider' => $this->provider,
            'model' => $this->model,
        ];
    }
}
