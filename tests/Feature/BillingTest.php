<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingTest extends TestCase
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
        $this->superAdmin = User::where('email', 'superadmin@schoolos.com')->firstOrFail();
    }

    public function test_public_can_fetch_saas_plans(): void
    {
        $response = $this->getJson('/api/v1/billing/plans');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_school_admin_can_view_subscription_and_ai_quotas(): void
    {
        $token = $this->adminSchoolA->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/billing/subscription');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'tenant' => [
                        'subdomain' => 'greenfield',
                    ],
                    'subscription' => [
                        'status' => 'active',
                        'plan' => [
                            'slug' => 'professional',
                        ],
                    ],
                    'usage' => [
                        'ai_credits' => [
                            'limit' => 10000,
                        ],
                    ],
                ]
            ]);
    }

    public function test_coupon_validation_calculates_discount(): void
    {
        $token = $this->adminSchoolA->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/billing/coupons/validate', [
                'code' => 'WELCOME20',
                'plan_slug' => 'professional',
                'billing_cycle' => 'monthly',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'code' => 'WELCOME20',
                    'discount_value' => 20.0,
                    'discount_amount' => 49.8, // 20% of 249
                    'final_price' => 199.2,
                ]
            ]);
    }

    public function test_school_admin_can_upgrade_plan_and_generates_invoice(): void
    {
        $token = $this->adminSchoolA->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/billing/subscribe', [
                'plan_slug' => 'enterprise',
                'billing_cycle' => 'annual',
                'coupon_code' => 'LAUNCH50',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'plan' => 'Enterprise',
                    'status' => 'active',
                ]
            ]);

        // Verify generated invoice
        $this->assertDatabaseHas('invoices', [
            'tenant_id' => $this->schoolA->id,
            'status' => 'paid',
        ]);
    }

    public function test_super_admin_can_access_platform_mrr_metrics(): void
    {
        $token = $this->superAdmin->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/platform/billing/metrics');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'active_schools' => 2,
                ]
            ]);

        $this->assertGreaterThan(0, $response->json('data.mrr_usd'));
    }

    public function test_regular_teacher_cannot_manage_subscription(): void
    {
        $token = $this->teacherSchoolA->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/billing/subscribe', [
                'plan_slug' => 'enterprise',
                'billing_cycle' => 'monthly',
            ]);

        $response->assertStatus(403);
    }
}
