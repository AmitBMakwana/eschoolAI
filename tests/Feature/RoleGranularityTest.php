<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleGranularityTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $superAdmin;
    protected User $schoolAdmin;
    protected User $principal;
    protected User $teacher;
    protected User $student;
    protected User $parent;
    protected User $accountant;
    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->tenant = Tenant::where('subdomain', 'greenfield')->firstOrFail();

        $this->superAdmin = User::withoutGlobalScopes()->where('email', 'superadmin@schoolos.com')->firstOrFail();
        $this->schoolAdmin = User::withoutGlobalScopes()->where('email', 'admin@greenfield.edu')->firstOrFail();
        $this->teacher = User::withoutGlobalScopes()->where('email', 'teacher@greenfield.edu')->firstOrFail();
        $this->student = User::withoutGlobalScopes()->where('email', 'student@greenfield.edu')->firstOrFail();
        $this->parent = User::withoutGlobalScopes()->where('email', 'parent@greenfield.edu')->firstOrFail();

        $accountantRole = Role::firstOrCreate(['slug' => 'accountant'], ['name' => 'Accountant']);
        $this->accountant = User::withoutGlobalScopes()->firstOrCreate(
            ['email' => 'accountant@greenfield.edu'],
            [
                'name' => 'Robert Miller (Accountant)',
                'password' => bcrypt('password123'),
                'tenant_id' => $this->tenant->id,
                'role_id' => $accountantRole->id,
                'status' => 'active'
            ]
        );

        $principalRole = Role::firstOrCreate(['slug' => 'principal'], ['name' => 'Principal']);
        $this->principal = User::withoutGlobalScopes()->firstOrCreate(
            ['email' => 'principal@greenfield.edu'],
            [
                'name' => 'Dr. Eleanor Vance (Principal)',
                'password' => bcrypt('password123'),
                'tenant_id' => $this->tenant->id,
                'role_id' => $principalRole->id,
                'status' => 'active'
            ]
        );

        $staffRole = Role::firstOrCreate(['slug' => 'staff'], ['name' => 'Staff']);
        $this->staff = User::withoutGlobalScopes()->firstOrCreate(
            ['email' => 'librarian@greenfield.edu'],
            [
                'name' => 'Librarian Staff',
                'password' => bcrypt('password123'),
                'tenant_id' => $this->tenant->id,
                'role_id' => $staffRole->id,
                'status' => 'active'
            ]
        );
    }

    public function test_school_admin_can_manage_billing_but_cannot_access_platform_superadmin_telemetry(): void
    {
        // School Admin CAN access their own school subscription
        $subRes = $this->actingAs($this->schoolAdmin)
            ->getJson('/api/v1/billing/subscription');
        $subRes->assertStatus(200);

        // School Admin CANNOT access Platform Super Admin global MRR telemetry
        $mrrRes = $this->actingAs($this->schoolAdmin)
            ->getJson('/api/v1/billing/platform/mrr');
        $mrrRes->assertStatus(403);
    }

    public function test_principal_can_manage_academics_and_circulars_but_cannot_modify_billing(): void
    {
        // Principal CAN manage academic notices and circulars
        $noticeRes = $this->actingAs($this->principal)
            ->postJson('/api/v1/communication/notices', [
                'title' => 'Annual Academic Assembly 2026',
                'content' => 'All faculty and students are requested to assemble in auditorium.',
                'target_audience' => 'All'
            ]);
        $noticeRes->assertStatus(201);

        // Principal CANNOT modify SaaS billing subscription plans
        $upgradeRes = $this->actingAs($this->principal)
            ->postJson('/api/v1/billing/upgrade', [
                'plan_id' => 3
            ]);
        $upgradeRes->assertStatus(403);
    }

    public function test_accountant_can_access_finance_and_defaulters_but_cannot_edit_exam_marks(): void
    {
        // Accountant CAN view fee invoices and financial summaries
        $feeRes = $this->actingAs($this->accountant)
            ->getJson('/api/v1/finance/invoices');
        $feeRes->assertStatus(200);

        // Accountant CANNOT alter exam terms
        $examRes = $this->actingAs($this->accountant)
            ->postJson('/api/v1/exams/terms', [
                'name' => 'Unauthorized Accountant Exam Term',
                'start_date' => '2026-09-01',
                'end_date' => '2026-09-15'
            ]);
        $examRes->assertStatus(403);
    }

    public function test_staff_librarian_cannot_access_financial_ledgers(): void
    {
        // Staff CAN view public notices
        $noticesRes = $this->actingAs($this->staff)
            ->getJson('/api/v1/communication/notices');
        $noticesRes->assertStatus(200);

        // Staff CANNOT access fee invoices
        $feeRes = $this->actingAs($this->staff)
            ->getJson('/api/v1/finance/invoices');
        $feeRes->assertStatus(403);
    }

    public function test_student_and_parent_cannot_access_administrative_or_teacher_tools(): void
    {
        // Student CANNOT mark attendance
        $attRes = $this->actingAs($this->student)
            ->postJson('/api/v1/attendance/mark', [
                'class_id' => 1,
                'date' => now()->toDateString(),
                'records' => []
            ]);
        $attRes->assertStatus(403);

        // Parent CANNOT create homework assignments
        $hwRes = $this->actingAs($this->parent)
            ->postJson('/api/v1/homework', [
                'title' => 'Fake Parent Assignment',
                'description' => 'Test',
                'class_id' => 1,
                'due_date' => now()->addDays(2)->toDateString()
            ]);
        $hwRes->assertStatus(403);
    }
}
