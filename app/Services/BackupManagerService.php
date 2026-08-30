<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\BackupLog;
use App\Models\Exam;
use App\Models\ExamMark;
use App\Models\FeePayment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentFeeInvoice;
use App\Models\Subject;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\Storage;

class BackupManagerService
{
    /**
     * Create database snapshot backup for current tenant.
     */
    public function createTenantBackup(?int $tenantId = null): BackupLog
    {
        $tenantId = $tenantId ?? TenantContext::id();

        $snapshot = [
            'tenant_id' => $tenantId,
            'generated_at' => now()->toIso8601String(),
            'platform' => 'eschoolAI Multi-Tenant SaaS',
            'version' => '1.0.0-PROD',
            'data' => [
                'users' => User::where('tenant_id', $tenantId)->get(),
                'classes' => SchoolClass::where('tenant_id', $tenantId)->get(),
                'subjects' => Subject::where('tenant_id', $tenantId)->get(),
                'students' => Student::where('tenant_id', $tenantId)->get(),
                'attendance_count' => Attendance::where('tenant_id', $tenantId)->count(),
                'exams' => Exam::where('tenant_id', $tenantId)->get(),
                'invoices_count' => StudentFeeInvoice::where('tenant_id', $tenantId)->count(),
                'payments_count' => FeePayment::where('tenant_id', $tenantId)->count(),
            ],
        ];

        $json = json_encode($snapshot, JSON_PRETTY_PRINT);
        $fileName = "backups/tenant_{$tenantId}_" . date('Y_m_d_His') . ".json";
        $checksum = hash('sha256', $json);
        $fileSize = strlen($json);

        Storage::disk('local')->put($fileName, $json);

        $log = BackupLog::create([
            'tenant_id' => $tenantId,
            'backup_type' => 'database_only',
            'file_path' => $fileName,
            'file_size_bytes' => $fileSize,
            'checksum_sha256' => $checksum,
            'status' => 'completed',
        ]);

        return $log;
    }
}
