<?php

use App\Http\Controllers\Api\V1\AcademicController;
use App\Http\Controllers\Api\V1\AIController;
use App\Http\Controllers\Api\V1\AiEvaluationController;
use App\Http\Controllers\Api\V1\AiQuestionPaperController;
use App\Http\Controllers\Api\V1\AiStudentAnalyticsController;
use App\Http\Controllers\Api\V1\AiWorksheetController;
use App\Http\Controllers\Api\V1\AttendanceController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BackupAndExportController;
use App\Http\Controllers\Api\V1\BillingController;
use App\Http\Controllers\Api\V1\CircularGeneratorController;
use App\Http\Controllers\Api\V1\CommunicationController;
use App\Http\Controllers\Api\V1\ComplianceController;
use App\Http\Controllers\Api\V1\ExamController;
use App\Http\Controllers\Api\V1\FeeController;
use App\Http\Controllers\Api\V1\HomeworkController;
use App\Http\Controllers\Api\V1\LessonPlannerController;
use App\Http\Controllers\Api\V1\MobileBridgeController;
use App\Http\Controllers\Api\V1\QuestionBankController;
use App\Http\Controllers\Api\V1\RagController;
use App\Http\Controllers\Api\V1\RealtimeHubController;
use App\Http\Controllers\Api\V1\SecurityAdminController;
use App\Http\Controllers\Api\V1\StudentController;
use App\Http\Controllers\Api\V1\TenantController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Middleware\SubscriptionFeatureMiddleware;
use App\Http\Middleware\TenantMiddleware;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - V1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Public Health Probe, OpenAPI Spec, Auth & Plans
    Route::get('/health', [HealthCheckController::class, 'check']);
    Route::get('/openapi.json', function () {
        $specPath = base_path('docs/openapi.yaml');
        if (File::exists($specPath)) {
            return response(file_get_contents($specPath), 200, ['Content-Type' => 'text/yaml']);
        }
        return response()->json(['openapi' => '3.0.0', 'info' => ['title' => 'AI SchoolOS API', 'version' => '1.0.0']]);
    });
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::get('/billing/plans', [BillingController::class, 'plans']);

    // Protected Routes (Sanctum + Tenant Context)
    Route::middleware(['auth:sanctum', TenantMiddleware::class])->group(function () {

        // Session & Identity
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Tenant Settings & Info
        Route::get('/tenant/current', [TenantController::class, 'current']);
        Route::put('/tenant/settings', [TenantController::class, 'updateSettings']);
        Route::get('/tenant/audit-logs', [TenantController::class, 'auditLogs']);

        // SaaS Billing & Subscriptions
        Route::get('/billing/subscription', [BillingController::class, 'subscription']);
        Route::post('/billing/subscribe', [BillingController::class, 'subscribe']);
        Route::get('/billing/invoices', [BillingController::class, 'invoices']);
        Route::post('/billing/coupons/validate', [BillingController::class, 'validateCoupon']);

        // Super Admin Platform Metrics & Controls
        Route::get('/platform/tenants', [TenantController::class, 'index']);
        Route::get('/platform/billing/metrics', [BillingController::class, 'platformMetrics']);
        Route::get('/platform/ai/global-metrics', [AIController::class, 'globalMetrics']);
        Route::post('/platform/tenants/{id}/archive', [SecurityAdminController::class, 'archiveTenant']);

        // 1. Classes & Sections & Subjects & Teachers
        Route::get('/classes', [AcademicController::class, 'classes']);
        Route::post('/classes', [AcademicController::class, 'storeClass']);
        Route::delete('/classes/{id}', [AcademicController::class, 'destroyClass']);
        Route::get('/sections', [AcademicController::class, 'sections']);
        Route::post('/sections', [AcademicController::class, 'storeSection']);
        Route::get('/teachers', [AcademicController::class, 'teachers']);
        Route::post('/teachers', [AcademicController::class, 'storeTeacher']);
        Route::delete('/teachers/{id}', [AcademicController::class, 'destroyTeacher']);
        Route::get('/subjects', [AcademicController::class, 'subjects']);
        Route::post('/subjects', [AcademicController::class, 'storeSubject']);
        Route::delete('/subjects/{id}', [AcademicController::class, 'destroySubject']);
        Route::get('/teacher-allocations', [AcademicController::class, 'teacherAllocations']);
        Route::post('/teacher-allocations', [AcademicController::class, 'storeTeacherAllocation']);
        Route::delete('/teacher-allocations/{id}', [AcademicController::class, 'destroyTeacherAllocation']);
        Route::get('/timetables', [AcademicController::class, 'timetables']);
        Route::post('/timetables', [AcademicController::class, 'storeTimetable']);
        Route::delete('/timetables/{id}', [AcademicController::class, 'destroyTimetable']);
        Route::get('/roles-permissions', [AcademicController::class, 'rolesAndPermissions']);

        // 2. Students & Enrollment
        Route::get('/students', [StudentController::class, 'index']);
        Route::post('/students', [StudentController::class, 'store']);
        Route::get('/students/{id}', [StudentController::class, 'show']);
        Route::put('/students/{id}', [StudentController::class, 'update']);
        Route::delete('/students/{id}', [StudentController::class, 'destroy']);

        // 3. Attendance
        Route::post('/attendance/mark', [AttendanceController::class, 'mark']);
        Route::get('/attendance/summary', [AttendanceController::class, 'summary']);

        // 4. Homework & Submissions
        Route::get('/homework', [HomeworkController::class, 'index']);
        Route::post('/homework', [HomeworkController::class, 'store']);
        Route::delete('/homework/{id}', [HomeworkController::class, 'destroy']);
        Route::post('/homework/{id}/submit', [HomeworkController::class, 'submit']);
        Route::post('/homework/submissions/{id}/review', [HomeworkController::class, 'review']);

        // 5. Notices & Communication
        Route::get('/notices', [CommunicationController::class, 'notices']);
        Route::post('/notices', [CommunicationController::class, 'storeNotice']);
        Route::delete('/notices/{id}', [CommunicationController::class, 'destroyNotice']);
        Route::get('/messages', [CommunicationController::class, 'messages']);
        Route::post('/messages/send', [CommunicationController::class, 'sendMessage']);

        // 6. Examinations, Grading & Results
        Route::get('/exams/terms', [ExamController::class, 'terms']);
        Route::post('/exams/terms', [ExamController::class, 'storeTerm']);
        Route::get('/exams', [ExamController::class, 'index']);
        Route::post('/exams', [ExamController::class, 'store']);
        Route::get('/exams/{id}/marks', [ExamController::class, 'marks']);
        Route::post('/exams/{id}/marks/bulk', [ExamController::class, 'storeBulkMarks']);
        Route::get('/exams/grading-scales', [ExamController::class, 'gradingScales']);
        Route::get('/exams/report-card/{student_id}', [ExamController::class, 'reportCard']);

        // 7. Question Bank & Exam Papers
        Route::get('/question-bank', [QuestionBankController::class, 'index']);
        Route::post('/question-bank', [QuestionBankController::class, 'store']);
        Route::post('/exam-papers/generate', [QuestionBankController::class, 'generatePaper']);

        // 8. Fees & Financial Management Engine
        Route::get('/finance/fee-heads', [FeeController::class, 'feeHeads']);
        Route::post('/finance/fee-heads', [FeeController::class, 'storeFeeHead']);
        Route::get('/finance/structures', [FeeController::class, 'structures']);
        Route::post('/finance/structures', [FeeController::class, 'storeStructure']);
        Route::get('/finance/concessions', [FeeController::class, 'concessions']);
        Route::post('/finance/concessions', [FeeController::class, 'storeConcession']);
        Route::put('/finance/concessions/{id}', [FeeController::class, 'updateConcession']);
        Route::delete('/finance/concessions/{id}', [FeeController::class, 'destroyConcession']);
        Route::get('/finance/invoices', [FeeController::class, 'invoices']);
        Route::post('/finance/invoices/generate-batch', [FeeController::class, 'generateBatchInvoices']);
        Route::post('/finance/payments/collect', [FeeController::class, 'collectPayment']);
        Route::get('/finance/defaulters', [FeeController::class, 'defaulters']);
        Route::get('/finance/analytics', [FeeController::class, 'analytics']);
        Route::get('/finance/expenses', [FeeController::class, 'expenses']);
        Route::post('/finance/expenses', [FeeController::class, 'storeExpense']);

        // 9. Provider-Abstracted AI Service Layer & Token Metering
        Route::post('/ai/prompt-preview', [AIController::class, 'promptPreview']);
        Route::get('/ai/usage-stats', [AIController::class, 'usageStats']);

        // 10. AI Education Modules: Lesson Planner & Circular Generator
        Route::get('/ai/lesson-plans', [LessonPlannerController::class, 'index']);
        Route::post('/ai/lesson-plans/generate', [LessonPlannerController::class, 'generate']);
        Route::get('/ai/lesson-plans/{id}', [LessonPlannerController::class, 'show']);
        Route::put('/ai/lesson-plans/{id}', [LessonPlannerController::class, 'update']);
        Route::post('/ai/lesson-plans/{id}/publish', [LessonPlannerController::class, 'publish']);

        Route::get('/ai/circulars', [CircularGeneratorController::class, 'index']);
        Route::post('/ai/circulars/generate', [CircularGeneratorController::class, 'generate']);
        Route::post('/ai/circulars/{id}/dispatch', [CircularGeneratorController::class, 'dispatch']);

        // 11. AI Education Modules: Question Paper & Worksheet Generator
        Route::get('/ai/question-papers', [AiQuestionPaperController::class, 'index']);
        Route::post('/ai/question-papers/generate', [AiQuestionPaperController::class, 'generate']);
        Route::get('/ai/question-papers/{id}', [AiQuestionPaperController::class, 'show']);
        Route::post('/ai/question-papers/{id}/sync-question-bank', [AiQuestionPaperController::class, 'syncQuestionBank']);

        Route::get('/ai/worksheets', [AiWorksheetController::class, 'index']);
        Route::post('/ai/worksheets/generate', [AiWorksheetController::class, 'generate']);
        Route::get('/ai/worksheets/{id}', [AiWorksheetController::class, 'show']);
        Route::post('/ai/worksheets/{id}/publish', [AiWorksheetController::class, 'publish']);

        // 12. AI Education Modules: Answer Sheet OCR Evaluation & Longitudinal Analytics
        Route::get('/ai/evaluations', [AiEvaluationController::class, 'index']);
        Route::post('/ai/evaluations/evaluate', [AiEvaluationController::class, 'evaluate']);
        Route::get('/ai/evaluations/{id}', [AiEvaluationController::class, 'show']);
        Route::post('/ai/evaluations/{id}/approve', [AiEvaluationController::class, 'approve']);

        Route::get('/ai/student-analytics/{student_id}', [AiStudentAnalyticsController::class, 'show']);

        // 13. Tenant-Isolated RAG Engine & Vector Search
        Route::get('/rag/documents', [RagController::class, 'index']);
        Route::post('/rag/documents/upload', [RagController::class, 'upload']);
        Route::get('/rag/documents/{id}/chunks', [RagController::class, 'chunks']);
        Route::post('/rag/query', [RagController::class, 'query']);

        // 14. Mobile-Ready Flutter REST API Bridge & Push Subsystem
        Route::get('/mobile/bootstrap', [MobileBridgeController::class, 'bootstrap']);
        Route::post('/mobile/devices/register', [MobileBridgeController::class, 'registerDevice']);
        Route::get('/mobile/notifications', [MobileBridgeController::class, 'notifications']);
        Route::post('/mobile/notifications/{id}/read', [MobileBridgeController::class, 'markNotificationRead']);
        Route::get('/mobile/sync/delta', [MobileBridgeController::class, 'syncDelta']);
        Route::get('/mobile/student-feed', [MobileBridgeController::class, 'studentFeed']);

        // 15. Realtime WebSocket Event Architecture & Instant Notification Hub
        Route::get('/realtime/channels', [RealtimeHubController::class, 'channels']);
        Route::post('/realtime/emergency-alert', [RealtimeHubController::class, 'emergencyAlert']);

        // 16. Security, Audit Trail & Enterprise Compliance
        Route::get('/security/audit-trail', [SecurityAdminController::class, 'auditTrail']);
        Route::get('/compliance/export/{student_id}', [ComplianceController::class, 'export']);
        Route::post('/compliance/anonymize/{student_id}', [ComplianceController::class, 'anonymize']);

        // 17. Automated Backups & System Exports Engine
        Route::get('/backups', [BackupAndExportController::class, 'backups']);
        Route::post('/backups/trigger', [BackupAndExportController::class, 'triggerBackup']);
        Route::get('/exports/students', [BackupAndExportController::class, 'exportStudents']);
        Route::get('/exports/attendance', [BackupAndExportController::class, 'exportAttendance']);
        Route::get('/exports/fees', [BackupAndExportController::class, 'exportFees']);
        Route::get('/exports/grades', [BackupAndExportController::class, 'exportGrades']);
    });
});
