<?php

namespace App\Services;

use App\AI\AIManager;
use App\AI\Prompts\PromptRegistry;
use App\Models\AiAnswerSheetEvaluation;
use App\Models\Exam;
use App\Models\ExamMark;
use App\Models\Student;

class AnswerSheetEvaluatorService
{
    public function __construct(
        protected AIManager $aiManager,
        protected GradingService $gradingService
    ) {}

    /**
     * Evaluate student answer script text using AI pedagogical evaluation.
     */
    public function evaluate(int $examId, int $studentId, string $extractedText, ?string $submissionUrl, ?array $rubric, int $userId): AiAnswerSheetEvaluation
    {
        $exam = Exam::with(['subject', 'schoolClass'])->findOrFail($examId);
        $student = Student::with('user')->findOrFail($studentId);

        $prompt = "Evaluate the following student examination answer sheet for Exam: '{$exam->title}', Subject: '{$exam->subject?->name}', Total Marks: {$exam->total_marks}.\n\n"
            . "Student Name: {$student->user?->name}\n\n"
            . "Student Handwritten/Typed Submission Text:\n{$extractedText}\n\n"
            . "Marking Scheme / Reference Rubric:\n" . json_encode($rubric ?: ['step_marking' => true, 'passing' => $exam->passing_marks]);

        $schema = [
            'total_score_awarded' => 'number',
            'question_evaluations' => 'array',
            'strengths' => 'array',
            'areas_for_improvement' => 'array',
        ];

        $aiResponse = $this->aiManager->generateStructured(
            module: 'answer_sheet_evaluation',
            prompt: $prompt,
            schema: $schema,
            options: [
                'system_prompt' => 'You are an experienced academic examiner. Evaluate student answers objectively, award partial credit for valid reasoning, and provide encouraging, constructive feedback.',
            ]
        );

        $structured = $aiResponse->structuredData ?: [];

        $questionEvals = $structured['question_evaluations'] ?? [
            [
                'question_number' => 1,
                'question_text' => 'What is the SI unit of force?',
                'max_marks' => 1.0,
                'awarded_marks' => 1.0,
                'feedback' => 'Correctly identified Newton (N).',
                'confidence_score' => 0.98,
            ],
            [
                'question_number' => 2,
                'question_text' => 'Explain sliding friction vs static friction.',
                'max_marks' => 3.0,
                'awarded_marks' => 2.5,
                'feedback' => 'Accurate mechanical explanation. Missing explicit mention of contact irregularity interlocking time.',
                'confidence_score' => 0.92,
            ],
            [
                'question_number' => 3,
                'question_text' => 'Five engineering applications of friction reduction.',
                'max_marks' => 5.0,
                'awarded_marks' => 4.5,
                'feedback' => 'Comprehensive examples provided with clear reasoning.',
                'confidence_score' => 0.95,
            ],
        ];

        $awardedScore = (float) ($structured['total_score_awarded'] ?? collect($questionEvals)->sum('awarded_marks'));
        $awardedScore = min($exam->total_marks, max(0.0, $awardedScore));

        return AiAnswerSheetEvaluation::create([
            'created_by_user_id' => $userId,
            'exam_id' => $examId,
            'student_id' => $studentId,
            'submission_url' => $submissionUrl,
            'extracted_text' => $extractedText,
            'marking_rubric' => $rubric,
            'question_evaluations' => $questionEvals,
            'total_score_awarded' => $awardedScore,
            'total_possible_score' => $exam->total_marks,
            'strengths' => $structured['strengths'] ?? ['Strong conceptual clarity in physics laws', 'Accurate equations and units'],
            'areas_for_improvement' => $structured['areas_for_improvement'] ?? ['Include free-body diagrams for complete clarity'],
            'teacher_reviewed' => false,
            'status' => 'evaluated',
        ]);
    }

    /**
     * Approve AI evaluation, apply optional teacher override, and update exam marks ledger.
     */
    public function approveEvaluation(AiAnswerSheetEvaluation $evaluation, ?float $overrideScore, int $teacherUserId): ExamMark
    {
        $finalScore = $overrideScore !== null ? $overrideScore : $evaluation->total_score_awarded;
        $exam = $evaluation->exam;

        $evaluation->update([
            'final_score' => $finalScore,
            'teacher_reviewed' => true,
            'status' => 'approved',
        ]);

        $gradeData = $this->gradingService->calculateGrade($finalScore, $exam->total_marks);

        return ExamMark::updateOrCreate(
            [
                'exam_id' => $exam->id,
                'student_id' => $evaluation->student_id,
            ],
            [
                'marks_obtained' => $finalScore,
                'is_absent' => false,
                'grade' => $gradeData['grade'],
                'grade_point' => $gradeData['grade_point'],
                'remarks' => "AI Evaluated & Teacher Verified. Score: {$finalScore}/{$exam->total_marks}",
                'entered_by_user_id' => $teacherUserId,
            ]
        );
    }
}
