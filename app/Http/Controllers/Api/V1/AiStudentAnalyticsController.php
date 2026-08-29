<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\StudentPerformanceAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiStudentAnalyticsController extends Controller
{
    public function __construct(
        protected StudentPerformanceAnalyticsService $analyticsService
    ) {}

    /**
     * Get or generate AI longitudinal diagnostic insights for a student.
     */
    public function show(Request $request, int $studentId): JsonResponse
    {
        $user = $request->user();

        // If user is a student or parent, enforce authorization
        if ($user->isStudent()) {
            $myStudent = Student::where('user_id', $user->id)->first();
            if (!$myStudent || $myStudent->id !== $studentId) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }
        }

        $insight = $this->analyticsService->analyzeStudent($studentId);

        return response()->json([
            'success' => true,
            'data' => $insight->load(['student.user', 'student.schoolClass', 'student.section']),
        ]);
    }
}
