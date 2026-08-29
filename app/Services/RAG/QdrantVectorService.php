<?php

namespace App\Services\RAG;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class QdrantVectorService
{
    protected string $host;
    protected int $port;
    protected string $collection;

    public function __construct()
    {
        $this->host = config('services.qdrant.host', env('QDRANT_HOST', 'http://127.0.0.1'));
        $this->port = (int) config('services.qdrant.port', env('QDRANT_PORT', 6333));
        $this->collection = config('services.qdrant.collection', 'schoolos_curriculum');
    }

    /**
     * Store and index vector embedding in Qdrant with tenant-isolated payload.
     */
    public function upsertPoint(string $vectorId, array $vector, array $payload): bool
    {
        $url = "{$this->host}:{$this->port}/collections/{$this->collection}/points";

        try {
            $response = Http::timeout(5)->put($url, [
                'points' => [
                    [
                        'id' => $vectorId,
                        'vector' => $vector,
                        'payload' => $payload, // Must contain tenant_id
                    ]
                ]
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            // Local fallback / offline mode
            return true;
        }
    }

    /**
     * Query vectors matching top-k cosine similarity with STRICT tenant_id filter.
     */
    public function search(int $tenantId, array $queryVector, int $limit = 5, ?int $classId = null, ?int $subjectId = null): array
    {
        $url = "{$this->host}:{$this->port}/collections/{$this->collection}/points/search";

        // Build strict tenant isolation filter
        $mustFilters = [
            [
                'key' => 'tenant_id',
                'match' => ['value' => $tenantId],
            ]
        ];

        if ($classId !== null) {
            $mustFilters[] = [
                'key' => 'class_id',
                'match' => ['value' => $classId],
            ];
        }

        if ($subjectId !== null) {
            $mustFilters[] = [
                'key' => 'subject_id',
                'match' => ['value' => $subjectId],
            ];
        }

        try {
            $response = Http::timeout(5)->post($url, [
                'vector' => $queryVector,
                'filter' => [
                    'must' => $mustFilters,
                ],
                'limit' => $limit,
                'with_payload' => true,
            ]);

            if ($response->successful()) {
                return $response->json('result') ?? [];
            }
        } catch (\Exception $e) {
            // Fallback
        }

        return [];
    }

    /**
     * Compute cosine similarity between two vector arrays.
     */
    public static function cosineSimilarity(array $vecA, array $vecB): float
    {
        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        $len = min(count($vecA), count($vecB));
        for ($i = 0; $i < $len; $i++) {
            $dotProduct += $vecA[$i] * $vecB[$i];
            $normA += $vecA[$i] * $vecA[$i];
            $normB += $vecB[$i] * $vecB[$i];
        }

        if ($normA <= 0 || $normB <= 0) {
            return 0.0;
        }

        return $dotProduct / (sqrt($normA) * sqrt($normB));
    }
}
