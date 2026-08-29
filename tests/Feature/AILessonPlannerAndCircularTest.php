<?php

namespace Tests\Feature;

use App\Models\AiCircular;
use App\Models\AiLessonPlan;
use App\Models\Notice;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AILessonPlannerAndCircularTest extends TestCase
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

    public function test_teacher_can_generate_and_publish_lesson_plan(): void
    {
        $class8 = SchoolClass::where('name', 'Class 8')->firstOrFail();
        $science = Subject::where('name', 'Science')->firstOrFail();

        // 1. Generate AI Lesson Plan
        $genRes = $this->actingAs($this->teacherSchoolA)
            ->postJson('/api/v1/ai/lesson-plans/generate', [
                'class_id' => $class8->id,
                'subject_id' => $science->id,
                'topic' => 'Photosynthesis & Cellular Respiration',
                'duration_minutes' => 45,
                'context' => 'NCERT Chapter 4 on nutrition in plants and chloroplast function',
            ]);

        $genRes->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'topic' => 'Photosynthesis & Cellular Respiration',
                    'status' => 'draft',
                ]
            ]);

        $planId = $genRes->json('data.id');
        $this->assertNotEmpty($genRes->json('data.learning_outcomes'));
        $this->assertNotEmpty($genRes->json('data.activities'));

        // 2. Refine lesson plan
        $updateRes = $this->actingAs($this->teacherSchoolA)
            ->putJson("/api/v1/ai/lesson-plans/{$planId}", [
                'topic' => 'Photosynthesis & Cellular Respiration (Advanced)',
                'teaching_aids' => ['Microscope slides of stomata', 'Chlorophyll extract beaker'],
            ]);

        $updateRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'topic' => 'Photosynthesis & Cellular Respiration (Advanced)',
                ]
            ]);

        // 3. Publish lesson plan
        $publishRes = $this->actingAs($this->teacherSchoolA)
            ->postJson("/api/v1/ai/lesson-plans/{$planId}/publish");

        $publishRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'published',
                ]
            ]);

        // 4. Verify AI usage log
        $this->assertDatabaseHas('ai_usage_logs', [
            'tenant_id' => $this->schoolA->id,
            'module' => 'lesson_planner',
        ]);
    }

    public function test_can_generate_circular_and_dispatch_to_notice_board(): void
    {
        // 1. Generate AI Circular
        $genRes = $this->actingAs($this->adminSchoolA)
            ->postJson('/api/v1/ai/circulars/generate', [
                'title' => 'Annual Sports Day 2026 Participation & Schedule',
                'audience' => 'all',
                'event_topic' => 'Track and field events, house marches, and prize distribution',
                'tone' => 'celebratory',
                'details' => 'Friday November 14th from 8:30 AM to 3:30 PM. Parents are warmly invited.',
            ]);

        $genRes->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'title' => 'Annual Sports Day 2026 Participation & Schedule',
                    'is_dispatched' => false,
                ]
            ]);

        $circularId = $genRes->json('data.id');

        // 2. Dispatch circular to Notice Board
        $dispatchRes = $this->actingAs($this->adminSchoolA)
            ->postJson("/api/v1/ai/circulars/{$circularId}/dispatch");

        $dispatchRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'circular' => [
                        'is_dispatched' => true,
                    ],
                    'notice' => [
                        'title' => 'Annual Sports Day 2026 Participation & Schedule',
                        'is_published' => true,
                    ]
                ]
            ]);

        // 3. Verify notice exists in general notice board
        $this->assertDatabaseHas('notices', [
            'tenant_id' => $this->schoolA->id,
            'title' => 'Annual Sports Day 2026 Participation & Schedule',
            'audience_type' => 'all',
            'is_published' => true,
        ]);
    }
}
