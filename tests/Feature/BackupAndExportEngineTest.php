<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackupAndExportEngineTest extends TestCase
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

    public function test_admin_can_trigger_database_backup_and_view_history(): void
    {
        // 1. Trigger database backup snapshot
        $backupRes = $this->actingAs($this->adminSchoolA)
            ->postJson('/api/v1/backups/trigger');

        $backupRes->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'tenant_id' => $this->schoolA->id,
                    'backup_type' => 'database_only',
                    'status' => 'completed',
                ]
            ]);

        $this->assertNotEmpty($backupRes->json('data.checksum_sha256'));
        $this->assertGreaterThan(0, $backupRes->json('data.file_size_bytes'));

        // 2. Query backup logs
        $listRes = $this->actingAs($this->adminSchoolA)
            ->getJson('/api/v1/backups');

        $listRes->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertCount(1, $listRes->json('data'));
    }

    public function test_csv_data_exports(): void
    {
        // 1. Students Roster Export
        $studentsRes = $this->actingAs($this->adminSchoolA)
            ->get('/api/v1/exports/students');

        $studentsRes->assertStatus(200);
        $this->assertStringContainsString('text/csv', $studentsRes->headers->get('Content-Type'));
        $this->assertStringContainsString('Admission Number', $studentsRes->getContent());

        // 2. Attendance Register Export
        $attendanceRes = $this->actingAs($this->adminSchoolA)
            ->get('/api/v1/exports/attendance');

        $attendanceRes->assertStatus(200);
        $this->assertStringContainsString('text/csv', $attendanceRes->headers->get('Content-Type'));
        $this->assertStringContainsString('Student Name', $attendanceRes->getContent());

        // 3. Fee Collection Export
        $feeRes = $this->actingAs($this->adminSchoolA)
            ->get('/api/v1/exports/fees');

        $feeRes->assertStatus(200);
        $this->assertStringContainsString('text/csv', $feeRes->headers->get('Content-Type'));
        $this->assertStringContainsString('Receipt No', $feeRes->getContent());
    }
}
