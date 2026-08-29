<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\ExamMark;
use App\Models\HomeworkSubmission;
use App\Models\Student;
use App\Models\StudentFeeInvoice;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;

class ComplianceService
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Export complete student educational and financial record (GDPR / FERPA Article 20 Data Portability).
     */
    public function exportStudentData(int $studentId): array
    {
        $student = Student::with(['user', 'schoolClass', 'section', 'parent.user'])->findOrFail($studentId);

        $attendance = Attendance::where('student_id', $student->id)->orderBy('date')->get();
        $marks = ExamMark::with('exam.subject')->where('student_id', $student->id)->get();
        $invoices = StudentFeeInvoice::where('student_id', $student->id)->get();
        $homework = HomeworkSubmission::with('homework')->where('student_id', $student->id)->get();

        return [
            'export_timestamp' => now()->toIso8601String(),
            'compliance_standard' => 'FERPA / GDPR Article 20 Compliant',
            'student_profile' => [
                'admission_number' => $student->admission_number,
                'name' => $student->user?->name,
                'email' => $student->user?->email,
                'phone' => $student->user?->phone,
                'dob' => $student->dob?->toDateString(),
                'gender' => $student->gender,
                'blood_group' => $student->blood_group,
                'class' => $student->schoolClass?->name,
                'section' => $student->section?->name,
                'enrollment_date' => $student->enrollment_date?->toDateString(),
            ],
            'academic_records' => [
                'exam_marks' => $marks,
                'homework_submissions' => $homework,
            ],
            'attendance_records' => [
                'total_recorded_days' => $attendance->count(),
                'present_days' => $attendance->where('status', 'present')->count(),
                'records' => $attendance,
            ],
            'financial_records' => [
                'invoices' => $invoices,
            ],
        ];
    }

    /**
     * Anonymize student PII upon right-to-be-forgotten request while preserving anonymized academic ledger integrity.
     */
    public function anonymizeStudent(Student $student, int $requestedByUserId): Student
    {
        DB::transaction(function () use ($student, $requestedByUserId) {
            $user = $student->user;
            if ($user) {
                $user->update([
                    'name' => 'Anonymized Student #' . $student->id,
                    'email' => "anonymized_{$student->id}@privacy.schoolos.internal",
                    'phone' => null,
                    'avatar_url' => null,
                    'status' => 'inactive',
                ]);
            }

            $student->update([
                'address' => null,
                'blood_group' => null,
                'status' => 'suspended',
            ]);
        });

        return $student->fresh(['user']);
    }
}
