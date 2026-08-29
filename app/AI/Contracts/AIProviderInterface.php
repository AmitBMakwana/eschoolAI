<?php

namespace App\AI\Contracts;

use App\AI\DTOs\AIResponse;

interface AIProviderInterface
{
    /**
     * Get the unique provider identifier (openai, claude, gemini, ollama, mock).
     */
    public function getIdentifier(): string;

    /**
     * Generate freeform text from prompt.
     */
    public function generateText(string $prompt, array $options = []): AIResponse;

    /**
     * Generate strictly validated JSON structured output matching a JSON schema.
     */
    public function generateStructured(string $prompt, array $schema, array $options = []): AIResponse;

    /**
     * Generate vector embeddings for a given text for Qdrant/RAG retrieval.
     */
    public function generateEmbedding(string $text): array;
}
