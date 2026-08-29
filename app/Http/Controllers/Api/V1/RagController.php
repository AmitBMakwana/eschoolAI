<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\RagChunk;
use App\Models\RagDocument;
use App\Services\AuditLogService;
use App\Services\RAG\RagPipelineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RagController extends Controller
{
    public function __construct(
        protected RagPipelineService $ragService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * List uploaded RAG documents.
     */
    public function index(Request $request): JsonResponse
    {
        $query = RagDocument::with(['schoolClass', 'subject', 'uploader:id,name']);

        if ($request->has('class_id')) {
            $query->where('class_id', $request->input('class_id'));
        }

        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->input('subject_id'));
        }

        if ($request->has('document_type')) {
            $query->where('document_type', $request->input('document_type'));
        }

        $docs = $query->orderBy('id', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $docs->items(),
            'meta' => [
                'current_page' => $docs->currentPage(),
                'total' => $docs->total(),
            ],
        ]);
    }

    /**
     * Ingest/Upload document content into tenant vector repository.
     */
    public function upload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:250',
            'content_text' => 'required|string',
            'class_id' => 'nullable|exists:school_classes,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'document_type' => 'required|in:textbook,syllabus,study_material,exam_paper,notes',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $doc = $this->ragService->ingestDocument(
                title: $request->input('title'),
                contentText: $request->input('content_text'),
                classId: $request->input('class_id') ? (int) $request->input('class_id') : null,
                subjectId: $request->input('subject_id') ? (int) $request->input('subject_id') : null,
                documentType: $request->input('document_type'),
                userId: $request->user()->id
            );

            $this->auditLogService->log(
                event: 'rag.document_indexed',
                auditable: $doc,
                newValues: ['title' => $doc->title, 'total_chunks' => $doc->total_chunks],
                request: $request
            );

            return response()->json([
                'success' => true,
                'message' => "Document ingested and {$doc->total_chunks} chunks indexed into vector database.",
                'data' => $doc->load(['schoolClass', 'subject']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * View chunks of a document.
     */
    public function chunks(int $id): JsonResponse
    {
        $doc = RagDocument::findOrFail($id);
        $chunks = RagChunk::where('rag_document_id', $doc->id)->orderBy('chunk_index')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'document' => $doc,
                'chunks' => $chunks,
            ],
        ]);
    }

    /**
     * Perform tenant-isolated semantic vector retrieval.
     */
    public function query(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string',
            'class_id' => 'nullable|exists:school_classes,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'top_k' => 'nullable|integer|min:1|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $results = $this->ragService->queryContext(
            query: $request->input('query'),
            classId: $request->input('class_id') ? (int) $request->input('class_id') : null,
            subjectId: $request->input('subject_id') ? (int) $request->input('subject_id') : null,
            topK: (int) ($request->input('top_k', 3))
        );

        return response()->json([
            'success' => true,
            'data' => [
                'query' => $request->input('query'),
                'matched_chunks' => $results,
            ],
        ]);
    }
}
