<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AiAnswerSheetEvaluation;
use App\Services\AnswerSheetEvaluatorService;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AiEvaluationController extends Controller
{
    public function __construct(
        protected AnswerSheetEvaluatorService $evaluationService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * List answer sheet evaluations.
     */
    public function index(Request $request): JsonResponse
    {
        $query = AiAnswerSheetEvaluation::with(['exam.subject', 'student.user', 'examiner:id,name']);

        if ($request->has('exam_id')) {
            $query->where('exam_id', $request->input('exam_id'));
        }

        if ($request->has('student_id')) {
            $query->where('student_id', $request->input('student_id'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $evaluations = $query->orderBy('id', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $evaluations->items(),
            'meta' => [
                'current_page' => $evaluations->currentPage(),
                'total' => $evaluations->total(),
            ],
        ]);
    }

    /**
     * Submit an answer sheet for AI OCR and pedagogical evaluation.
     */
    public function evaluate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'exam_id' => 'required|exists:exams,id',
            'student_id' => 'required|exists:students,id',
            'extracted_text' => 'required|string',
            'submission_url' => 'nullable|string',
            'marking_rubric' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $evaluation = $this->evaluationService->evaluate(
                examId: (int) $request->input('exam_id'),
                studentId: (int) $request->input('student_id'),
                extractedText: $request->input('extracted_text'),
                submissionUrl: $request->input('submission_url'),
                rubric: $request->input('marking_rubric'),
                userId: $request->user()->id
            );

            $this->auditLogService->log(
                event: 'ai.answer_sheet_evaluated',
                auditable: $evaluation,
                newValues: ['score' => $evaluation->total_score_awarded, 'exam_id' => $evaluation->exam_id],
                request: $request
            );

            return response()->json([
                'success' => true,
                'message' => 'Answer sheet evaluated by AI.',
                'data' => $evaluation->load(['exam.subject', 'student.user']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * View evaluation details.
     */
    public function show(int $id): JsonResponse
    {
        $evaluation = AiAnswerSheetEvaluation::with(['exam.subject', 'student.user', 'examiner'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $evaluation,
        ]);
    }

    /**
     * Approve AI evaluation, override marks if needed, and sync to exam marks ledger.
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        $evaluation = AiAnswerSheetEvaluation::with('exam')->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'override_score' => 'nullable|numeric|min:0|max:' . $evaluation->exam->total_marks,
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $override = $request->has('override_score') ? (float) $request->input('override_score') : null;
        $mark = $this->evaluationService->approveEvaluation($evaluation, $override, $request->user()->id);

        $this->auditLogService->log(
            event: 'ai.evaluation_approved',
            auditable: $mark,
            newValues: ['marks_obtained' => $mark->marks_obtained, 'grade' => $mark->grade],
            request: $request
        );

        return response()->json([
            'success' => true,
            'message' => 'Evaluation approved and recorded into official grade ledger.',
            'data' => [
                'evaluation' => $evaluation,
                'exam_mark' => $mark,
            ],
        ]);
    }
}
