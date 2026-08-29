<?php

namespace Tests\Feature;

use App\Models\AiGeneratedQuestionPaper;
use App\Models\AiWorksheet;
use App\Models\QuestionBank;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AIQuestionPaperAndWorksheetTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $schoolA;
    protected User $adminSchoolA;
    protected User $teacherSchoolA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->schoolA = Tenant::where('subdomain', 'greenfield')->firstOrFail();
        $this->adminSchoolA = User::where('email', 'admin@greenfield.edu')->firstOrFail();
        $this->teacherSchoolA = User::where('email', 'teacher@greenfield.edu')->firstOrFail();
    }

    public function test_teacher_can_generate_question_paper_and_sync_to_question_bank(): void
    {
        $class8 = SchoolClass::where('name', 'Class 8')->firstOrFail();
        $science = Subject::where('name', 'Science')->firstOrFail();

        // 1. Generate Question Paper
        $genRes = $this->actingAs($this->teacherSchoolA)
            ->postJson('/api/v1/ai/question-papers/generate', [
                'class_id' => $class8->id,
                'subject_id' => $science->id,
                'title' => 'Class 8 Science Mid-Term Comprehensive Paper',
                'duration_minutes' => 180,
                'total_marks' => 100,
                'blueprint' => [
                    'easy_percentage' => 40,
                    'medium_percentage' => 40,
                    'hard_percentage' => 20,
                ],
            ]);

        $genRes->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'title' => 'Class 8 Science Mid-Term Comprehensive Paper',
                    'total_marks' => 100,
                ]
            ]);

        $paperId = $genRes->json('data.id');
        $this->assertNotEmpty($genRes->json('data.sections'));
        $this->assertNotEmpty($genRes->json('data.answer_key'));

        // 2. Sync to Question Bank
        $syncRes = $this->actingAs($this->teacherSchoolA)
            ->postJson("/api/v1/ai/question-papers/{$paperId}/sync-question-bank");

        $syncRes->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertGreaterThan(0, $syncRes->json('data.imported_count'));

        // 3. Verify in question_banks table
        $this->assertDatabaseHas('question_banks', [
            'tenant_id' => $this->schoolA->id,
            'subject_id' => $science->id,
            'class_id' => $class8->id,
        ]);
    }

    public function test_teacher_can_generate_worksheet_and_publish(): void
    {
        $class8 = SchoolClass::where('name', 'Class 8')->firstOrFail();
        $science = Subject::where('name', 'Science')->firstOrFail();

        // 1. Generate Worksheet
        $genRes = $this->actingAs($this->teacherSchoolA)
            ->postJson('/api/v1/ai/worksheets/generate', [
                'class_id' => $class8->id,
                'subject_id' => $science->id,
                'title' => 'Cell Structure and Functions Practice Drill',
                'topic' => 'Cell organelles, plant vs animal cells, and cell membrane permeability',
                'difficulty' => 'medium',
                'instructions' => 'Time allowed: 30 minutes. Complete all 3 sections.',
            ]);

        $genRes->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'title' => 'Cell Structure and Functions Practice Drill',
                    'status' => 'draft',
                ]
            ]);

        $wsId = $genRes->json('data.id');
        $this->assertNotEmpty($genRes->json('data.content.sections'));
        $this->assertNotEmpty($genRes->json('data.solution_guide'));

        // 2. Publish Worksheet
        $publishRes = $this->actingAs($this->teacherSchoolA)
            ->postJson("/api/v1/ai/worksheets/{$wsId}/publish");

        $publishRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'published',
                ]
            ]);

        // 3. Verify AI usage log
        $this->assertDatabaseHas('ai_usage_logs', [
            'tenant_id' => $this->schoolA->id,
            'module' => 'worksheet_generator',
        ]);
    }
}
