<?php

namespace App\Services\RAG;

class TextChunker
{
    /**
     * Chunk text into overlapping segments with sentence boundary preservation.
     *
     * @param string $text Raw input text
     * @param int $chunkSize Target token/word size per chunk (default 400)
     * @param int $overlap Sliding window overlap (default 50)
     * @return array Array of chunk strings
     */
    public function chunk(string $text, int $chunkSize = 400, int $overlap = 50): array
    {
        // 1. Clean & normalize whitespace
        $cleanText = preg_replace('/\s+/', ' ', trim($text));
        if (empty($cleanText)) {
            return [];
        }

        // 2. Split into sentences
        $sentences = preg_split('/(?<=[.?!])\s+/', $cleanText, -1, PREG_SPLIT_NO_EMPTY);
        if (empty($sentences)) {
            $sentences = [$cleanText];
        }

        $chunks = [];
        $currentChunkWords = [];

        foreach ($sentences as $sentence) {
            $words = explode(' ', trim($sentence));
            
            if (count($currentChunkWords) + count($words) > $chunkSize && !empty($currentChunkWords)) {
                // Save current chunk
                $chunks[] = implode(' ', $currentChunkWords);
                
                // Sliding window: keep trailing overlap words for context preservation
                $overlapWords = array_slice($currentChunkWords, max(0, count($currentChunkWords) - $overlap));
                $currentChunkWords = $overlapWords;
            }

            $currentChunkWords = array_merge($currentChunkWords, $words);
        }

        if (!empty($currentChunkWords)) {
            $chunks[] = implode(' ', $currentChunkWords);
        }

        return $chunks;
    }
}
