<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\ExamMark;
use App\Models\FeePayment;
use App\Models\Student;
use App\Tenancy\TenantContext;

class ExportEngineService
{
    /**
     * Generate CSV content for student roster.
     */
    public function exportStudentsCsv(?int $classId = null): string
    {
        $query = Student::with(['user', 'schoolClass', 'section']);
        if ($classId !== null) {
            $query->where('class_id', $classId);
        }

        $students = $query->get();

        $output = fopen('php://temp', 'r+');
        fputcsv($output, ['ID', 'Admission Number', 'Roll Number', 'Student Name', 'Email', 'Class', 'Section', 'Gender', 'Status']);

        foreach ($students as $s) {
            fputcsv($output, [
                $s->id,
                $s->admission_number,
                $s->roll_number ?? 'N/A',
                $s->user?->name ?? 'N/A',
                $s->user?->email ?? 'N/A',
                $s->schoolClass?->name ?? 'N/A',
                $s->section?->name ?? 'N/A',
                ucfirst($s->gender ?? 'N/A'),
                ucfirst($s->status),
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Generate CSV content for attendance register.
     */
    public function exportAttendanceCsv(?int $classId = null, ?string $month = null): string
    {
        $query = Attendance::with(['student.user', 'schoolClass']);
        if ($classId !== null) {
            $query->where('class_id', $classId);
        }

        if ($month) {
            $query->where('date', 'like', "{$month}%");
        }

        $records = $query->orderBy('date', 'desc')->get();

        $output = fopen('php://temp', 'r+');
        fputcsv($output, ['Date', 'Class', 'Student Name', 'Admission No', 'Status', 'Remarks']);

        foreach ($records as $r) {
            fputcsv($output, [
                $r->date->toDateString(),
                $r->schoolClass?->name ?? 'N/A',
                $r->student?->user?->name ?? 'N/A',
                $r->student?->admission_number ?? 'N/A',
                ucfirst($r->status),
                $r->remarks ?? '',
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Generate CSV content for fee collection ledger.
     */
    public function exportFeesCsv(): string
    {
        $payments = FeePayment::with(['student.user', 'invoice.structure.feeHead'])->orderBy('paid_at', 'desc')->get();

        $output = fopen('php://temp', 'r+');
        fputcsv($output, ['Receipt No', 'Date', 'Student Name', 'Admission No', 'Fee Head', 'Payment Mode', 'Amount Paid', 'Reference/Txn']);

        foreach ($payments as $p) {
            fputcsv($output, [
                $p->receipt_number,
                $p->paid_at->toDateTimeString(),
                $p->student?->user?->name ?? 'N/A',
                $p->student?->admission_number ?? 'N/A',
                $p->invoice?->structure?->feeHead?->name ?? 'Academic Fee',
                ucfirst($p->payment_mode),
                number_format($p->amount_paid, 2),
                $p->transaction_reference ?? 'N/A',
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Generate CSV content for exam grades tabulation.
     */
    public function exportExamGradesCsv(?int $examId = null): string
    {
        $query = ExamMark::with(['student.user', 'exam.subject', 'exam.schoolClass']);
        if ($examId !== null) {
            $query->where('exam_id', $examId);
        }

        $marks = $query->get();

        $output = fopen('php://temp', 'r+');
        fputcsv($output, ['Exam Name', 'Class', 'Subject', 'Student Name', 'Admission No', 'Marks Obtained', 'Max Marks', 'Percentage', 'Grade', 'Remarks']);

        foreach ($marks as $m) {
            $maxMarks = $m->exam?->total_marks ?? 100;
            $pct = $maxMarks > 0 ? round(($m->marks_obtained / $maxMarks) * 100, 2) : 0;

            fputcsv($output, [
                $m->exam?->name ?? 'N/A',
                $m->exam?->schoolClass?->name ?? 'N/A',
                $m->exam?->subject?->name ?? 'N/A',
                $m->student?->user?->name ?? 'N/A',
                $m->student?->admission_number ?? 'N/A',
                $m->marks_obtained,
                $maxMarks,
                $pct . '%',
                $m->grade_letter ?? 'N/A',
                $m->remarks ?? '',
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
