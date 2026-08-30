<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BackupLog;
use App\Services\AuditLogService;
use App\Services\BackupManagerService;
use App\Services\ExportEngineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BackupAndExportController extends Controller
{
    public function __construct(
        protected BackupManagerService $backupService,
        protected ExportEngineService $exportService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * List backup history.
     */
    public function backups(Request $request): JsonResponse
    {
        $logs = BackupLog::orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $logs->items(),
            'meta' => [
                'current_page' => $logs->currentPage(),
                'total' => $logs->total(),
            ],
        ]);
    }

    /**
     * Trigger instant database snapshot backup.
     */
    public function triggerBackup(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $log = $this->backupService->createTenantBackup();

        $this->auditLogService->log(
            event: 'system.backup_generated',
            auditable: $log,
            newValues: ['file_path' => $log->file_path, 'file_size' => $log->file_size_bytes],
            request: $request
        );

        return response()->json([
            'success' => true,
            'message' => 'Database snapshot backup created successfully.',
            'data' => $log,
        ], 201);
    }

    /**
     * Export Students CSV.
     */
    public function exportStudents(Request $request): Response
    {
        $classId = $request->input('class_id') ? (int) $request->input('class_id') : null;
        $csv = $this->exportService->exportStudentsCsv($classId);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="students_roster_' . date('Y_m_d') . '.csv"',
        ]);
    }

    /**
     * Export Attendance CSV.
     */
    public function exportAttendance(Request $request): Response
    {
        $classId = $request->input('class_id') ? (int) $request->input('class_id') : null;
        $month = $request->input('month');
        $csv = $this->exportService->exportAttendanceCsv($classId, $month);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="attendance_register_' . date('Y_m_d') . '.csv"',
        ]);
    }

    /**
     * Export Fees Collection CSV.
     */
    public function exportFees(Request $request): Response
    {
        $csv = $this->exportService->exportFeesCsv();

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="fee_collection_' . date('Y_m_d') . '.csv"',
        ]);
    }

    /**
     * Export Grades Tabulation CSV.
     */
    public function exportGrades(Request $request): Response
    {
        $examId = $request->input('exam_id') ? (int) $request->input('exam_id') : null;
        $csv = $this->exportService->exportExamGradesCsv($examId);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="grades_tabulation_' . date('Y_m_d') . '.csv"',
        ]);
    }
}
