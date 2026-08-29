<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AiGeneratedQuestionPaper;
use App\Services\AiQuestionPaperService;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AiQuestionPaperController extends Controller
{
    public function __construct(
        protected AiQuestionPaperService $paperService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * List generated question papers.
     */
    public function index(Request $request): JsonResponse
    {
        $query = AiGeneratedQuestionPaper::with(['schoolClass', 'subject', 'author:id,name']);

        if ($request->has('class_id')) {
            $query->where('class_id', $request->input('class_id'));
        }

        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->input('subject_id'));
        }

        $papers = $query->orderBy('id', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $papers->items(),
            'meta' => [
                'current_page' => $papers->currentPage(),
                'total' => $papers->total(),
            ],
        ]);
    }

    /**
     * Generate an AI Question Paper.
     */
    public function generate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:250',
            'duration_minutes' => 'nullable|integer|min:15|max:240',
            'total_marks' => 'nullable|numeric|min:10|max:200',
            'blueprint' => 'nullable|array',
            'context' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $paper = $this->paperService->generate(
                classId: (int) $request->input('class_id'),
                subjectId: (int) $request->input('subject_id'),
                title: $request->input('title'),
                durationMinutes: (int) ($request->input('duration_minutes', 180)),
                totalMarks: (float) ($request->input('total_marks', 100.0)),
                blueprint: $request->input('blueprint', ['easy' => 40, 'medium' => 40, 'hard' => 20]),
                context: $request->input('context'),
                userId: $request->user()->id
            );

            $this->auditLogService->log(
                event: 'ai.question_paper_generated',
                auditable: $paper,
                newValues: ['title' => $paper->title, 'total_marks' => $paper->total_marks],
                request: $request
            );

            return response()->json([
                'success' => true,
                'message' => 'AI Question Paper generated successfully with answer key and blueprint.',
                'data' => $paper->load(['schoolClass', 'subject', 'examPaper']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * View generated paper.
     */
    public function show(int $id): JsonResponse
    {
        $paper = AiGeneratedQuestionPaper::with(['schoolClass', 'subject', 'author', 'examPaper'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $paper,
        ]);
    }

    /**
     * Sync questions to Question Bank.
     */
    public function syncQuestionBank(Request $request, int $id): JsonResponse
    {
        $paper = AiGeneratedQuestionPaper::findOrFail($id);
        $importedCount = $this->paperService->syncToQuestionBank($paper, $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => "Successfully imported {$importedCount} questions into Question Bank.",
            'data' => [
                'imported_count' => $importedCount,
            ],
        ]);
    }
}
