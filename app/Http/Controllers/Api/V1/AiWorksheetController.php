<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AiWorksheet;
use App\Services\AiWorksheetService;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AiWorksheetController extends Controller
{
    public function __construct(
        protected AiWorksheetService $worksheetService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * List worksheets.
     */
    public function index(Request $request): JsonResponse
    {
        $query = AiWorksheet::with(['schoolClass', 'subject', 'author:id,name']);

        if ($request->has('class_id')) {
            $query->where('class_id', $request->input('class_id'));
        }

        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->input('subject_id'));
        }

        $worksheets = $query->orderBy('id', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $worksheets->items(),
            'meta' => [
                'current_page' => $worksheets->currentPage(),
                'total' => $worksheets->total(),
            ],
        ]);
    }

    /**
     * Generate an AI worksheet.
     */
    public function generate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:250',
            'topic' => 'required|string|max:250',
            'difficulty' => 'required|in:easy,medium,hard,adaptive',
            'instructions' => 'nullable|string',
            'context' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $worksheet = $this->worksheetService->generate(
                classId: (int) $request->input('class_id'),
                subjectId: (int) $request->input('subject_id'),
                title: $request->input('title'),
                topic: $request->input('topic'),
                difficulty: $request->input('difficulty'),
                instructions: $request->input('instructions'),
                context: $request->input('context'),
                userId: $request->user()->id
            );

            $this->auditLogService->log(
                event: 'ai.worksheet_generated',
                auditable: $worksheet,
                newValues: ['title' => $worksheet->title, 'topic' => $worksheet->topic],
                request: $request
            );

            return response()->json([
                'success' => true,
                'message' => 'AI Worksheet generated with complete solution guide.',
                'data' => $worksheet->load(['schoolClass', 'subject', 'author']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * View worksheet.
     */
    public function show(int $id): JsonResponse
    {
        $worksheet = AiWorksheet::with(['schoolClass', 'subject', 'author'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $worksheet,
        ]);
    }

    /**
     * Publish worksheet.
     */
    public function publish(int $id): JsonResponse
    {
        $worksheet = AiWorksheet::findOrFail($id);
        $worksheet->update(['status' => 'published']);

        return response()->json([
            'success' => true,
            'message' => 'Worksheet published for student download.',
            'data' => $worksheet,
        ]);
    }
}
