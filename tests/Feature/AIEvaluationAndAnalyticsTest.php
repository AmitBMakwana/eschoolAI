<?php

namespace Tests\Feature;

use App\Models\AiAnswerSheetEvaluation;
use App\Models\AiStudentInsight;
use App\Models\Exam;
use App\Models\ExamMark;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AIEvaluationAndAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $schoolA;
    protected User $adminSchoolA;
    protected User $teacherSchoolA;
    protected User $studentSchoolA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->schoolA = Tenant::where('subdomain', 'greenfield')->firstOrFail();
        $this->adminSchoolA = User::where('email', 'admin@greenfield.edu')->firstOrFail();
        $this->teacherSchoolA = User::where('email', 'teacher@greenfield.edu')->firstOrFail();
        $this->studentSchoolA = User::where('email', 'student@greenfield.edu')->firstOrFail();
    }

    public function test_teacher_evaluates_answer_sheet_and_approves_into_gradebook(): void
    {
        $exam = Exam::where('title', 'Science Mid-Term Assessment')->firstOrFail();
        $alexStudent = Student::where('user_id', $this->studentSchoolA->id)->firstOrFail();

        $submissionText = "Q1: The SI unit of force is Newton (N).\n"
            . "Q2: Sliding friction is less than static friction because surface irregularities do not get enough time to interlock when bodies are in motion.\n"
            . "Q3: Five applications to minimize friction: 1) Ball bearings in bicycle wheels, 2) Engine oil lubrication, 3) Aerodynamic streamlined car shape, 4) Polishing contact gears, 5) Air cushions in hovercrafts.";

        // 1. Submit for AI evaluation
        $evalRes = $this->actingAs($this->teacherSchoolA)
            ->postJson('/api/v1/ai/evaluations/evaluate', [
                'exam_id' => $exam->id,
                'student_id' => $alexStudent->id,
                'extracted_text' => $submissionText,
                'submission_url' => 'https://storage.schoolos.com/tenants/1/submissions/science_q1.jpg',
            ]);

        $evalRes->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'exam_id' => $exam->id,
                    'student_id' => $alexStudent->id,
                    'status' => 'evaluated',
                ]
            ]);

        $evalId = $evalRes->json('data.id');
        $this->assertNotEmpty($evalRes->json('data.question_evaluations'));
        $this->assertNotEmpty($evalRes->json('data.strengths'));

        // 2. Teacher reviews and approves evaluation with final score of 94.0
        $approveRes = $this->actingAs($this->teacherSchoolA)
            ->postJson("/api/v1/ai/evaluations/{$evalId}/approve", [
                'override_score' => 94.0,
            ]);

        $approveRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'evaluation' => [
                        'status' => 'approved',
                        'final_score' => 94.0,
                    ],
                    'exam_mark' => [
                        'marks_obtained' => 94.0,
                        'grade' => 'A+',
                    ]
                ]
            ]);

        // 3. Verify in exam_marks table
        $this->assertDatabaseHas('exam_marks', [
            'tenant_id' => $this->schoolA->id,
            'exam_id' => $exam->id,
            'student_id' => $alexStudent->id,
            'marks_obtained' => 94.0,
        ]);
    }

    public function test_student_longitudinal_analytics_generation(): void
    {
        $alexStudent = Student::where('user_id', $this->studentSchoolA->id)->firstOrFail();

        $analyticsRes = $this->actingAs($this->teacherSchoolA)
            ->getJson("/api/v1/ai/student-analytics/{$alexStudent->id}");

        $analyticsRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'student_id' => $alexStudent->id,
                    'academic_year' => '2026-2027',
                ]
            ]);

        $this->assertNotEmpty($analyticsRes->json('data.strength_topics'));
        $this->assertNotEmpty($analyticsRes->json('data.personalized_recommendations'));
        $this->assertNotEmpty($analyticsRes->json('data.attendance_correlation'));

        // Verify AI usage log
        $this->assertDatabaseHas('ai_usage_logs', [
            'tenant_id' => $this->schoolA->id,
            'module' => 'student_report_analysis',
        ]);
    }
}
