<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Exam;
use App\Models\ExamMark;
use App\Models\ExamTerm;
use App\Models\GradingScale;
use App\Models\ReportCard;
use App\Models\Student;
use App\Tenancy\TenantContext;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GradingService
{
    /**
     * Compute grade and grade point for a mark obtained out of max marks.
     */
    public function calculateGrade(float $marksObtained, float $maxMarks): array
    {
        if ($maxMarks <= 0) {
            return ['grade' => 'N/A', 'grade_point' => 0.0, 'percentage' => 0.0];
        }

        $percentage = round(($marksObtained / $maxMarks) * 100, 2);
        $scale = GradingScale::gradeForPercentage($percentage);

        if (!$scale) {
            // Default grading fallback if table is empty
            if ($percentage >= 90) return ['grade' => 'A+', 'grade_point' => 4.0, 'percentage' => $percentage];
            if ($percentage >= 80) return ['grade' => 'A', 'grade_point' => 3.7, 'percentage' => $percentage];
            if ($percentage >= 70) return ['grade' => 'B', 'grade_point' => 3.0, 'percentage' => $percentage];
            if ($percentage >= 60) return ['grade' => 'C', 'grade_point' => 2.0, 'percentage' => $percentage];
            if ($percentage >= 50) return ['grade' => 'D', 'grade_point' => 1.0, 'percentage' => $percentage];
            return ['grade' => 'F', 'grade_point' => 0.0, 'percentage' => $percentage];
        }

        return [
            'grade' => $scale->grade,
            'grade_point' => $scale->grade_point,
            'percentage' => $percentage,
        ];
    }

    /**
     * Process and record bulk exam marks for an exam.
     */
    public function recordBulkMarks(Exam $exam, array $records, int $enteredByUserId): Collection
    {
        $updated = collect();

        DB::transaction(function () use ($exam, $records, $enteredByUserId, &$updated) {
            foreach ($records as $item) {
                $isAbsent = !empty($item['is_absent']);
                $marksObtained = $isAbsent ? 0.0 : (float) ($item['marks_obtained'] ?? 0);
                
                $gradeData = $isAbsent 
                    ? ['grade' => 'AB', 'grade_point' => 0.0, 'percentage' => 0.0] 
                    : $this->calculateGrade($marksObtained, $exam->total_marks);

                $mark = ExamMark::updateOrCreate(
                    [
                        'exam_id' => $exam->id,
                        'student_id' => $item['student_id'],
                    ],
                    [
                        'marks_obtained' => $marksObtained,
                        'is_absent' => $isAbsent,
                        'grade' => $gradeData['grade'],
                        'grade_point' => $gradeData['grade_point'],
                        'remarks' => $item['remarks'] ?? null,
                        'entered_by_user_id' => $enteredByUserId,
                    ]
                );

                $updated->push($mark);
            }
        });

        return $updated;
    }

    /**
     * Generate or regenerate a consolidated Report Card for a student in an exam term.
     */
    public function generateReportCard(int $studentId, int $examTermId): ReportCard
    {
        $student = Student::with(['schoolClass', 'section', 'user'])->findOrFail($studentId);
        $term = ExamTerm::findOrFail($examTermId);

        // Get all exams for the student's class in this term
        $exams = Exam::with('subject')
            ->where('exam_term_id', $term->id)
            ->where('class_id', $student->class_id)
            ->get();

        $examIds = $exams->pluck('id');
        $marks = ExamMark::whereIn('exam_id', $examIds)
            ->where('student_id', $student->id)
            ->get()
            ->keyBy('exam_id');

        $totalMaxMarks = 0;
        $totalObtainedMarks = 0;
        $totalGradePoints = 0;
        $subjectBreakdown = [];

        foreach ($exams as $exam) {
            $markRecord = $marks->get($exam->id);
            $obtained = $markRecord ? (float) $markRecord->marks_obtained : 0.0;
            $isAbsent = $markRecord ? (bool) $markRecord->is_absent : false;
            $grade = $markRecord ? $markRecord->grade : 'N/A';
            $gradePoint = $markRecord ? (float) $markRecord->grade_point : 0.0;

            $totalMaxMarks += $exam->total_marks;
            $totalObtainedMarks += $obtained;
            $totalGradePoints += $gradePoint;

            $subjectBreakdown[] = [
                'subject_id' => $exam->subject_id,
                'subject_name' => $exam->subject?->name ?? 'General',
                'exam_title' => $exam->title,
                'max_marks' => $exam->total_marks,
                'marks_obtained' => $obtained,
                'is_absent' => $isAbsent,
                'grade' => $grade,
                'grade_point' => $gradePoint,
                'remarks' => $markRecord?->remarks,
            ];
        }

        $overallPercentage = $totalMaxMarks > 0 ? round(($totalObtainedMarks / $totalMaxMarks) * 100, 2) : 0.0;
        $overallGradeData = $this->calculateGrade($totalObtainedMarks, $totalMaxMarks);
        $gpa = count($exams) > 0 ? round($totalGradePoints / count($exams), 2) : 0.0;

        // Calculate attendance rate
        $totalAttendance = Attendance::where('student_id', $student->id)->count();
        $presentAttendance = Attendance::where('student_id', $student->id)->where('status', 'present')->count();
        $attRate = $totalAttendance > 0 ? round(($presentAttendance / $totalAttendance) * 100, 1) : 100.0;

        $reportCard = ReportCard::updateOrCreate(
            [
                'student_id' => $student->id,
                'exam_term_id' => $term->id,
            ],
            [
                'total_max_marks' => $totalMaxMarks,
                'total_marks_obtained' => $totalObtainedMarks,
                'percentage' => $overallPercentage,
                'overall_grade' => $overallGradeData['grade'],
                'gpa' => $gpa,
                'attendance_percentage' => $attRate,
                'subject_breakdown' => $subjectBreakdown,
                'teacher_remarks' => $overallPercentage >= 80 ? 'Exceptional academic performance and participation.' : 'Good effort, can improve with consistent study.',
                'status' => 'published',
                'generated_at' => now(),
            ]
        );

        return $reportCard;
    }
}
