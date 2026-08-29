<?php

namespace App\AI\Providers;

use App\AI\Contracts\AIProviderInterface;
use App\AI\DTOs\AIResponse;

class MockAIProvider implements AIProviderInterface
{
    public function getIdentifier(): string
    {
        return 'mock';
    }

    public function generateText(string $prompt, array $options = []): AIResponse
    {
        $content = "Mock AI generated response for prompt: " . substr($prompt, 0, 80) . "...";
        $promptTokens = (int) (strlen($prompt) / 4);
        $completionTokens = (int) (strlen($content) / 4);
        $totalTokens = $promptTokens + $completionTokens;
        $cost = ($promptTokens * 0.00000015) + ($completionTokens * 0.0000006);

        return new AIResponse(
            content: $content,
            structuredData: null,
            promptTokens: $promptTokens,
            completionTokens: $completionTokens,
            totalTokens: $totalTokens,
            costUsd: round($cost, 6),
            provider: 'mock',
            model: $options['model'] ?? 'mock-gpt-4o',
            latencyMs: 150
        );
    }

    public function generateStructured(string $prompt, array $schema, array $options = []): AIResponse
    {
        // Build mock structured data matching expected educational formats
        $structured = [
            'title' => 'Pedagogy & Lesson Plan: Forces & Friction',
            'grade_level' => 'Class 8',
            'learning_objectives' => [
                'Understand difference between static and kinetic friction',
                'Identify five everyday applications of friction reduction',
            ],
            'activities' => [
                ['name' => 'Spring Scale Experiment', 'duration_minutes' => 15],
                ['name' => 'Interactive Class Discussion', 'duration_minutes' => 20],
            ],
            'assessment_questions' => [
                ['question' => 'Why are soles of shoes grooved?', 'marks' => 2],
            ],
        ];

        $jsonStr = json_encode($structured, JSON_PRETTY_PRINT);
        $promptTokens = (int) (strlen($prompt) / 4);
        $completionTokens = (int) (strlen($jsonStr) / 4);
        $totalTokens = $promptTokens + $completionTokens;
        $cost = ($promptTokens * 0.00000015) + ($completionTokens * 0.0000006);

        return new AIResponse(
            content: $jsonStr,
            structuredData: $structured,
            promptTokens: $promptTokens,
            completionTokens: $completionTokens,
            totalTokens: $totalTokens,
            costUsd: round($cost, 6),
            provider: 'mock',
            model: $options['model'] ?? 'mock-gpt-4o',
            latencyMs: 220
        );
    }

    public function generateEmbedding(string $text): array
    {
        // 768-dimensional mock normalized vector
        $vector = [];
        $seed = crc32($text);
        mt_srand($seed);

        for ($i = 0; $i < 768; $i++) {
            $vector[] = round((mt_rand(-1000, 1000) / 1000), 6);
        }

        return $vector;
    }
}
