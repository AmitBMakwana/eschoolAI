<?php

namespace Tests\Feature;

use App\AI\AIManager;
use App\AI\Prompts\PromptRegistry;
use App\Models\AiUsageLog;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AIServiceLayerTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $schoolA;
    protected User $adminSchoolA;
    protected User $teacherSchoolA;
    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->schoolA = Tenant::where('subdomain', 'greenfield')->firstOrFail();
        $this->adminSchoolA = User::where('email', 'admin@greenfield.edu')->firstOrFail();
        $this->teacherSchoolA = User::where('email', 'teacher@greenfield.edu')->firstOrFail();
        $this->superAdmin = User::withoutGlobalScopes()->where('email', 'superadmin@schoolos.com')->firstOrFail();
    }

    public function test_ai_manager_resolves_all_provider_drivers(): void
    {
        $manager = new AIManager();

        $this->assertEquals('mock', $manager->provider('mock')->getIdentifier());
        $this->assertEquals('openai', $manager->provider('openai')->getIdentifier());
        $this->assertEquals('gemini', $manager->provider('gemini')->getIdentifier());
        $this->assertEquals('claude', $manager->provider('claude')->getIdentifier());
        $this->assertEquals('ollama', $manager->provider('ollama')->getIdentifier());
    }

    public function test_prompt_registry_renders_versioned_templates(): void
    {
        $rendered = PromptRegistry::get('lesson_planner.v1', [
            'class_name' => 'Class 8',
            'subject_name' => 'Physics',
            'topic' => 'Gravitation and Planetary Motion',
            'context' => 'NCERT Chapter 7 excerpt on universal gravitation',
        ]);

        $this->assertEquals('lesson_planner', $rendered['module']);
        $this->assertEquals('1.0', $rendered['version']);
        $this->assertStringContainsString('Gravitation and Planetary Motion', $rendered['prompt']);
        $this->assertNotEmpty($rendered['schema']);
    }

    public function test_ai_generation_automatically_records_token_usage_and_costs(): void
    {
        TenantContext::set($this->schoolA);

        $response = $this->actingAs($this->teacherSchoolA)
            ->postJson('/api/v1/ai/prompt-preview', [
                'template_key' => 'lesson_planner.v1',
                'variables' => [
                    'class_name' => 'Class 8',
                    'subject_name' => 'Science',
                    'topic' => 'Force & Laws of Motion',
                    'context' => 'Basic definitions of inertia and momentum',
                ],
                'provider' => 'mock',
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertNotNull($response->json('data.ai_response.structured_data'));
        $this->assertGreaterThan(0, $response->json('data.ai_response.usage.total_tokens'));

        // Verify record in ai_usage_logs
        $this->assertDatabaseHas('ai_usage_logs', [
            'tenant_id' => $this->schoolA->id,
            'user_id' => $this->teacherSchoolA->id,
            'module' => 'lesson_planner',
            'provider' => 'mock',
        ]);
    }

    public function test_tenant_ai_usage_stats_and_super_admin_global_metrics(): void
    {
        // 1. Trigger an AI request
        $this->actingAs($this->teacherSchoolA)
            ->postJson('/api/v1/ai/prompt-preview', [
                'template_key' => 'circular_generator.v1',
                'variables' => [
                    'school_name' => 'Greenfield International',
                    'audience' => 'Parents of Grade 8',
                    'event_topic' => 'Field Trip to Science Observatory',
                    'details' => 'Bus leaves at 8 AM, return by 4 PM',
                ],
                'provider' => 'mock',
            ]);

        // 2. School Admin queries usage stats
        $statsRes = $this->actingAs($this->adminSchoolA)
            ->getJson('/api/v1/ai/usage-stats');

        $statsRes->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertGreaterThanOrEqual(1, $statsRes->json('data.summary.total_generations'));
        $this->assertGreaterThanOrEqual(1, $statsRes->json('data.summary.total_tokens'));

        // 3. Super Admin queries platform global AI metrics
        $globalRes = $this->actingAs($this->superAdmin)
            ->getJson('/api/v1/platform/ai/global-metrics');

        $globalRes->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertGreaterThanOrEqual(1, $globalRes->json('data.total_calls'));
    }
}
