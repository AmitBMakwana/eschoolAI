<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AiLessonPlan;
use App\Services\AuditLogService;
use App\Services\LessonPlannerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LessonPlannerController extends Controller
{
    public function __construct(
        protected LessonPlannerService $lessonPlannerService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * List lesson plans with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = AiLessonPlan::with(['schoolClass', 'subject', 'author:id,name']);

        if ($request->has('class_id')) {
            $query->where('class_id', $request->input('class_id'));
        }

        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->input('subject_id'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $plans = $query->orderBy('id', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $plans->items(),
            'meta' => [
                'current_page' => $plans->currentPage(),
                'total' => $plans->total(),
            ],
        ]);
    }

    /**
     * Generate an AI lesson plan.
     */
    public function generate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'topic' => 'required|string|max:250',
            'duration_minutes' => 'nullable|integer|min:15|max:180',
            'context' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $plan = $this->lessonPlannerService->generate(
                classId: (int) $request->input('class_id'),
                subjectId: (int) $request->input('subject_id'),
                topic: $request->input('topic'),
                durationMinutes: (int) ($request->input('duration_minutes', 45)),
                context: $request->input('context'),
                userId: $request->user()->id
            );

            $this->auditLogService->log(
                event: 'ai.lesson_plan_generated',
                auditable: $plan,
                newValues: ['topic' => $plan->topic, 'class_id' => $plan->class_id],
                request: $request
            );

            return response()->json([
                'success' => true,
                'message' => 'AI Lesson Plan generated successfully.',
                'data' => $plan->load(['schoolClass', 'subject', 'author']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * View lesson plan.
     */
    public function show(int $id): JsonResponse
    {
        $plan = AiLessonPlan::with(['schoolClass', 'subject', 'author'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $plan,
        ]);
    }

    /**
     * Update/Refine lesson plan.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $plan = AiLessonPlan::findOrFail($id);

        $plan->update($request->only([
            'topic',
            'duration_minutes',
            'learning_outcomes',
            'prerequisites',
            'activities',
            'teaching_aids',
            'formative_assessments',
            'homework_recommendations',
            'full_plan_text',
            'status',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Lesson plan updated.',
            'data' => $plan->load(['schoolClass', 'subject']),
        ]);
    }

    /**
     * Publish lesson plan for school-wide access.
     */
    public function publish(int $id): JsonResponse
    {
        $plan = AiLessonPlan::findOrFail($id);
        $plan->update(['status' => 'published']);

        return response()->json([
            'success' => true,
            'message' => 'Lesson plan published to curriculum repository.',
            'data' => $plan,
        ]);
    }
}
