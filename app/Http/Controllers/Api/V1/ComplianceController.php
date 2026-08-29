<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\AuditLogService;
use App\Services\ComplianceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ComplianceController extends Controller
{
    public function __construct(
        protected ComplianceService $complianceService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Export complete student educational and financial history (FERPA / GDPR).
     */
    public function export(Request $request, int $studentId): JsonResponse
    {
        $user = $request->user();

        // Enforce access control
        if ($user->isStudent()) {
            $student = Student::where('user_id', $user->id)->first();
            if (!$student || $student->id !== $studentId) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }
        } elseif (!$user->isAdmin() && !$user->isPrincipal()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $bundle = $this->complianceService->exportStudentData($studentId);

        $this->auditLogService->log(
            event: 'compliance.student_data_exported',
            newValues: ['student_id' => $studentId],
            request: $request
        );

        return response()->json([
            'success' => true,
            'data' => $bundle,
        ]);
    }

    /**
     * Anonymize student PII (GDPR right to be forgotten).
     */
    public function anonymize(Request $request, int $studentId): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Only administrators can anonymize records.'], 403);
        }

        $student = Student::with('user')->findOrFail($studentId);
        $anonymized = $this->complianceService->anonymizeStudent($student, $user->id);

        $this->auditLogService->log(
            event: 'compliance.student_anonymized',
            auditable: $student,
            newValues: ['student_id' => $studentId],
            request: $request
        );

        return response()->json([
            'success' => true,
            'message' => 'Student personally identifiable information has been securely anonymized.',
            'data' => $anonymized,
        ]);
    }
}
