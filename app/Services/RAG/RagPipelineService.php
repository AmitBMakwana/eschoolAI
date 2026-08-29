<?php

namespace App\Services\RAG;

use App\AI\AIManager;
use App\Models\RagChunk;
use App\Models\RagDocument;
use App\Tenancy\TenantContext;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RagPipelineService
{
    public function __construct(
        protected TextChunker $chunker,
        protected QdrantVectorService $vectorService,
        protected AIManager $aiManager
    ) {}

    /**
     * Ingest, chunk, embed, and index an educational document.
     */
    public function ingestDocument(string $title, string $contentText, ?int $classId, ?int $subjectId, string $documentType, int $userId): RagDocument
    {
        $tenantId = TenantContext::id();

        $document = RagDocument::create([
            'uploaded_by_user_id' => $userId,
            'class_id' => $classId,
            'subject_id' => $subjectId,
            'title' => $title,
            'document_type' => $documentType,
            'file_size_bytes' => strlen($contentText),
            'mime_type' => 'text/plain',
            'status' => 'processing',
        ]);

        $chunkTexts = $this->chunker->chunk($contentText, chunkSize: 350, overlap: 40);
        $totalChunks = count($chunkTexts);

        DB::transaction(function () use ($document, $chunkTexts, $tenantId, $classId, $subjectId) {
            foreach ($chunkTexts as $idx => $text) {
                $vectorId = (string) Str::uuid();
                $tokenEstimate = (int) (strlen($text) / 4);

                // Generate embedding via AI Provider
                $embedding = $this->aiManager->generateEmbedding($text);

                // Store in database
                $chunk = RagChunk::create([
                    'rag_document_id' => $document->id,
                    'chunk_index' => $idx + 1,
                    'content' => $text,
                    'token_count' => $tokenEstimate,
                    'vector_id' => $vectorId,
                    'metadata' => [
                        'chunk_number' => $idx + 1,
                        'total_chunks' => count($chunkTexts),
                        'document_title' => $document->title,
                    ],
                ]);

                // Index in Qdrant with tenant payload
                $this->vectorService->upsertPoint($vectorId, $embedding, [
                    'tenant_id' => $tenantId,
                    'document_id' => $document->id,
                    'chunk_id' => $chunk->id,
                    'class_id' => $classId,
                    'subject_id' => $subjectId,
                    'content' => $text,
                ]);
            }

            $document->update([
                'total_chunks' => count($chunkTexts),
                'status' => 'indexed',
            ]);
        });

        return $document;
    }

    /**
     * Retrieve relevant context chunks matching a query prompt under strict tenant isolation.
     */
    public function queryContext(string $query, ?int $classId = null, ?int $subjectId = null, int $topK = 3): array
    {
        $tenantId = TenantContext::id();
        $queryEmbedding = $this->aiManager->generateEmbedding($query);

        // Fetch candidate chunks for this tenant from database
        $chunkQuery = RagChunk::with('document')
            ->whereHas('document', function ($q) use ($classId, $subjectId) {
                if ($classId !== null) {
                    $q->where('class_id', $classId);
                }
                if ($subjectId !== null) {
                    $q->where('subject_id', $subjectId);
                }
            });

        $chunks = $chunkQuery->limit(20)->get();

        if ($chunks->isEmpty()) {
            return [];
        }

        // Rank by cosine similarity
        $scoredChunks = $chunks->map(function ($chunk) use ($queryEmbedding) {
            $chunkVec = $this->aiManager->generateEmbedding($chunk->content);
            $similarity = QdrantVectorService::cosineSimilarity($queryEmbedding, $chunkVec);

            return [
                'chunk_id' => $chunk->id,
                'document_id' => $chunk->rag_document_id,
                'document_title' => $chunk->document?->title,
                'content' => $chunk->content,
                'similarity_score' => round($similarity, 4),
                'metadata' => $chunk->metadata,
            ];
        })->sortByDesc('similarity_score')->take($topK)->values()->toArray();

        return $scoredChunks;
    }
}
