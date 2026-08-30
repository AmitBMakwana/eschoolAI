<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeAndBrandingCustomizationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $schoolA;
    protected Tenant $schoolB;
    protected User $adminSchoolA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->schoolA = Tenant::where('subdomain', 'greenfield')->firstOrFail();
        $this->schoolB = Tenant::where('subdomain', 'oakridge')->firstOrFail();
        $this->adminSchoolA = User::withoutGlobalScopes()->where('email', 'admin@greenfield.edu')->firstOrFail();
    }

    public function test_school_admin_can_update_institutional_theme_branding_settings(): void
    {
        $response = $this->actingAs($this->adminSchoolA)
            ->putJson('/api/v1/tenant/settings', [
                'settings' => [
                    'theme' => [
                        'primary_color' => '#10B981',
                        'palette_name' => 'Emerald Academy',
                        'crest_initials' => 'GF',
                        'school_motto' => 'Excellence in Education'
                    ]
                ]
            ]);

        $response->assertStatus(200);

        // Verify persisted in School A settings
        $this->schoolA->refresh();
        $this->assertEquals('#10B981', $this->schoolA->settings['theme']['primary_color'] ?? null);

        // Assert School B settings are untouched (Tenant Isolation)
        $this->schoolB->refresh();
        $this->assertNotEquals('#10B981', $this->schoolB->settings['theme']['primary_color'] ?? null);
    }

    public function test_tenant_context_resolves_correctly_for_authenticated_user(): void
    {
        $response = $this->actingAs($this->adminSchoolA)
            ->getJson('/api/v1/tenant/current');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'subdomain' => 'greenfield'
                ]
            ]);
    }
}
