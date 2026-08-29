<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Services\AuditLogService;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $schoolA;
    protected Tenant $schoolB;
    protected User $adminSchoolA;
    protected User $adminSchoolB;
    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->schoolA = Tenant::where('subdomain', 'greenfield')->firstOrFail();
        $this->schoolB = Tenant::where('subdomain', 'oakridge')->firstOrFail();

        $this->adminSchoolA = User::where('email', 'admin@greenfield.edu')->firstOrFail();
        $this->adminSchoolB = User::where('email', 'admin@oakridge.edu')->firstOrFail();
        $this->superAdmin = User::where('email', 'superadmin@schoolos.com')->firstOrFail();
    }

    public function test_school_a_admin_cannot_see_school_b_users_or_logs(): void
    {
        // Generate an audit log in School B
        TenantContext::set($this->schoolB);
        $auditService = app(AuditLogService::class);
        $auditService->log(
            event: 'secret.school_b_action',
            userId: $this->adminSchoolB->id,
            tenantId: $this->schoolB->id
        );
        TenantContext::clear();

        // Authenticate as School A Admin
        $token = $this->adminSchoolA->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/tenant/audit-logs');

        $response->assertStatus(200);

        $events = collect($response->json('data'))->pluck('event');

        // Assert School B event is NEVER returned to School A
        $this->assertFalse($events->contains('secret.school_b_action'));
    }

    public function test_school_a_user_only_retrieves_school_a_tenant_context(): void
    {
        $token = $this->adminSchoolA->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/tenant/current');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $this->schoolA->id,
                    'subdomain' => 'greenfield',
                ]
            ]);

        // Explicit check: ID must not be School B
        $this->assertNotEquals($this->schoolB->id, $response->json('data.id'));
    }

    public function test_suspended_school_users_are_denied_access(): void
    {
        $this->schoolA->update(['status' => 'suspended']);

        $token = $this->adminSchoolA->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/tenant/current');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'error_code' => 'TENANT_SUSPENDED',
            ]);
    }

    public function test_tenant_scope_automatically_applies_to_eloquent_queries(): void
    {
        // When TenantContext is set to School A
        TenantContext::set($this->schoolA);

        $users = User::all();

        // Every user returned MUST belong to School A
        foreach ($users as $user) {
            $this->assertEquals($this->schoolA->id, $user->tenant_id);
        }

        // Must not contain School B Admin
        $this->assertFalse($users->contains('id', $this->adminSchoolB->id));

        TenantContext::clear();
    }
}
