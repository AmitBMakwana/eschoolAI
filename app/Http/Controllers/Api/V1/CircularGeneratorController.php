<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AiCircular;
use App\Services\AuditLogService;
use App\Services\CircularGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CircularGeneratorController extends Controller
{
    public function __construct(
        protected CircularGeneratorService $circularService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * List AI circulars.
     */
    public function index(Request $request): JsonResponse
    {
        $query = AiCircular::with(['author:id,name', 'notice']);

        if ($request->has('audience')) {
            $query->where('audience', $request->input('audience'));
        }

        $circulars = $query->orderBy('id', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $circulars->items(),
            'meta' => [
                'current_page' => $circulars->currentPage(),
                'total' => $circulars->total(),
            ],
        ]);
    }

    /**
     * Generate an AI institutional circular.
     */
    public function generate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:250',
            'audience' => 'required|in:all,teachers,parents,students,staff',
            'event_topic' => 'required|string|max:250',
            'tone' => 'required|in:formal,celebratory,urgent,welcoming',
            'details' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $circular = $this->circularService->generate(
                title: $request->input('title'),
                audience: $request->input('audience'),
                eventTopic: $request->input('event_topic'),
                tone: $request->input('tone'),
                details: $request->input('details'),
                userId: $request->user()->id
            );

            $this->auditLogService->log(
                event: 'ai.circular_generated',
                auditable: $circular,
                newValues: ['title' => $circular->title, 'audience' => $circular->audience],
                request: $request
            );

            return response()->json([
                'success' => true,
                'message' => 'AI circular draft generated successfully.',
                'data' => $circular->load('author'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Dispatch circular to school Notice Board.
     */
    public function dispatch(Request $request, int $id): JsonResponse
    {
        $circular = AiCircular::findOrFail($id);

        $notice = $this->circularService->dispatch($circular, $request->user()->id);

        $this->auditLogService->log(
            event: 'ai.circular_dispatched',
            auditable: $notice,
            newValues: ['notice_id' => $notice->id, 'title' => $notice->title],
            request: $request
        );

        return response()->json([
            'success' => true,
            'message' => 'Circular approved and published to Notice Board.',
            'data' => [
                'circular' => $circular,
                'notice' => $notice,
            ],
        ]);
    }
}
