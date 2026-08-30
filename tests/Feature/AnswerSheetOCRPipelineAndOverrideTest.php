<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\ExamMark;
use App\Models\ExamTerm;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnswerSheetOCRPipelineAndOverrideTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $schoolA;
    protected User $teacher;
    protected Student $student;
    protected Exam $exam;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->schoolA = Tenant::where('subdomain', 'greenfield')->firstOrFail();
        TenantContext::set($this->schoolA);

        $this->teacher = User::where('email', 'teacher@greenfield.edu')->firstOrFail();
        $this->student = Student::first();
        $this->exam = Exam::first();

        TenantContext::clear();
    }

    public function test_ocr_pipeline_evaluates_answer_sheet_and_returns_confidence_score(): void
    {
        $response = $this->actingAs($this->teacher)
            ->postJson('/api/v1/ai/evaluations/evaluate', [
                'student_id' => $this->student->id,
                'exam_id' => $this->exam->id,
                'extracted_text' => "Q1: The SI unit of force is Newton (N).\nQ2: Sliding friction is less than static friction because interlocking of surface irregularities is incomplete during motion.",
                'submission_url' => 'https://storage.schoolos.com/tenants/1/submissions/answer_sheet_p1.jpg'
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'exam_id' => $this->exam->id,
                    'student_id' => $this->student->id,
                    'status' => 'evaluated',
                ]
            ]);

        $data = $response->json('data');
        $this->assertNotEmpty($data['question_evaluations']);
        $this->assertArrayHasKey('obtained_marks', $data);
        $this->assertArrayHasKey('confidence_score', $data);
        $this->assertGreaterThanOrEqual(80, $data['confidence_score']);
    }

    public function test_teacher_can_manually_override_ai_marks_before_gradebook_commit(): void
    {
        // 1. Submit for initial evaluation
        $evalRes = $this->actingAs($this->teacher)
            ->postJson('/api/v1/ai/evaluations/evaluate', [
                'student_id' => $this->student->id,
                'exam_id' => $this->exam->id,
                'extracted_text' => "Q1: Force is mass times acceleration (F = m*a).\nQ2: Friction opposes relative motion.",
                'submission_url' => 'https://storage.schoolos.com/tenants/1/submissions/answer_sheet_p2.jpg'
            ]);

        $evalId = $evalRes->json('data.id');

        // 2. Teacher manually overrides score to 48.5 before approving into official gradebook
        $approveRes = $this->actingAs($this->teacher)
            ->postJson("/api/v1/ai/evaluations/{$evalId}/approve", [
                'override_score' => 48.5,
            ]);

        $approveRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'evaluation' => [
                        'status' => 'approved',
                        'final_score' => 48.5,
                    ],
                    'exam_mark' => [
                        'marks_obtained' => 48.5,
                    ]
                ]
            ]);

        // 3. Confirm mark exists in MySQL exam_marks table
        $this->assertDatabaseHas('exam_marks', [
            'tenant_id' => $this->schoolA->id,
            'exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'marks_obtained' => 48.5,
        ]);
    }
}
