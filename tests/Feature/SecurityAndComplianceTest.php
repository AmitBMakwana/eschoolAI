<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use App\Services\AuditLogService;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityAndComplianceTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $schoolA;
    protected User $adminSchoolA;
    protected User $studentSchoolA;
    protected User $teacherSchoolA;
    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->schoolA = Tenant::where('subdomain', 'greenfield')->firstOrFail();
        $this->adminSchoolA = User::where('email', 'admin@greenfield.edu')->firstOrFail();
        $this->studentSchoolA = User::where('email', 'student@greenfield.edu')->firstOrFail();
        $this->teacherSchoolA = User::where('email', 'teacher@greenfield.edu')->firstOrFail();
        $this->superAdmin = User::withoutGlobalScopes()->where('email', 'superadmin@schoolos.com')->firstOrFail();
    }

    public function test_ferpa_gdpr_student_data_export_and_access_control(): void
    {
        $alexStudent = Student::where('user_id', $this->studentSchoolA->id)->firstOrFail();

        // 1. Student exports own data -> 200 OK
        $studentExport = $this->actingAs($this->studentSchoolA)
            ->getJson("/api/v1/compliance/export/{$alexStudent->id}");

        $studentExport->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'compliance_standard' => 'FERPA / GDPR Article 20 Compliant',
                    'student_profile' => [
                        'admission_number' => 'ADM-2026-084',
                    ]
                ]
            ]);

        // 2. Regular teacher tries to export complete GDPR archive -> 403 Forbidden
        $teacherExport = $this->actingAs($this->teacherSchoolA)
            ->getJson("/api/v1/compliance/export/{$alexStudent->id}");

        $teacherExport->assertStatus(403);
    }

    public function test_gdpr_right_to_be_forgotten_anonymization(): void
    {
        $alexStudent = Student::where('user_id', $this->studentSchoolA->id)->firstOrFail();

        $anonymizeRes = $this->actingAs($this->adminSchoolA)
            ->postJson("/api/v1/compliance/anonymize/{$alexStudent->id}");

        $anonymizeRes->assertStatus(200)
            ->assertJson(['success' => true]);

        // Verify PII masked
        $this->studentSchoolA->refresh();
        $this->assertStringContainsString('Anonymized Student', $this->studentSchoolA->name);
        $this->assertStringContainsString('@privacy.schoolos.internal', $this->studentSchoolA->email);

        // Verify audit log
        $this->assertDatabaseHas('audit_logs', [
            'tenant_id' => $this->schoolA->id,
            'event' => 'compliance.student_anonymized',
        ]);
    }

    public function test_searchable_audit_trail_and_super_admin_tenant_archive(): void
    {
        // 1. Generate an audit log entry first
        TenantContext::set($this->schoolA);
        app(AuditLogService::class)->log(
            event: 'security.permission_reviewed',
            userId: $this->adminSchoolA->id,
            newValues: ['status' => 'verified']
        );

        // 2. Query audit trail
        $auditRes = $this->actingAs($this->adminSchoolA)
            ->getJson('/api/v1/security/audit-trail');

        $auditRes->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertNotEmpty($auditRes->json('data'));

        // 3. Super Admin archives tenant
        $archiveRes = $this->actingAs($this->superAdmin)
            ->postJson("/api/v1/platform/tenants/{$this->schoolA->id}/archive");

        $archiveRes->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->schoolA->refresh();
        $this->assertEquals('suspended', $this->schoolA->status);
    }
}
