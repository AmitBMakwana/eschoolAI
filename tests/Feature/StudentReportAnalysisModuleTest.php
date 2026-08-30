<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentReportAnalysisModuleTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $schoolA;
    protected Tenant $schoolB;
    protected User $teacherSchoolA;
    protected Student $studentSchoolA;
    protected Student $studentSchoolB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->schoolA = Tenant::where('subdomain', 'greenfield')->firstOrFail();
        $this->schoolB = Tenant::where('subdomain', 'oakridge')->firstOrFail();

        $this->teacherSchoolA = User::withoutGlobalScopes()->where('email', 'teacher@greenfield.edu')->firstOrFail();
        $studentUserA = User::withoutGlobalScopes()->where('email', 'student@greenfield.edu')->firstOrFail();
        $this->studentSchoolA = Student::where('user_id', $studentUserA->id)->firstOrFail();

        // Create a student in School B
        $studentRole = Role::where('slug', Role::STUDENT)->firstOrFail();
        $userB = User::withoutGlobalScopes()->create([
            'name' => 'John Oakridge',
            'email' => 'john@oakridge.edu',
            'password' => Hash::make('password123'),
            'role_id' => $studentRole->id,
            'tenant_id' => $this->schoolB->id,
            'status' => 'active',
        ]);

        $classB = SchoolClass::withoutGlobalScopes()->where('tenant_id', $this->schoolB->id)->first() ?? SchoolClass::withoutGlobalScopes()->create([
            'name' => 'Class 8-B',
            'code' => 'C8B',
            'order_index' => 8,
            'tenant_id' => $this->schoolB->id,
        ]);

        $this->studentSchoolB = Student::withoutGlobalScopes()->create([
            'user_id' => $userB->id,
            'class_id' => $classB->id,
            'admission_number' => 'ADM-OAK-001',
            'roll_number' => '01',
            'tenant_id' => $this->schoolB->id,
        ]);
    }

    public function test_teacher_can_generate_student_longitudinal_performance_report_with_narrative(): void
    {
        $response = $this->actingAs($this->teacherSchoolA)
            ->getJson("/api/v1/ai/student-analytics/{$this->studentSchoolA->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'student_id' => $this->studentSchoolA->id,
                ]
            ]);

        $data = $response->json('data');
        $this->assertNotEmpty($data['strength_topics']);
        $this->assertNotEmpty($data['personalized_recommendations']);
        $this->assertNotEmpty($data['attendance_correlation']);
    }

    public function test_student_report_analysis_strictly_prevents_cross_tenant_student_access(): void
    {
        // Teacher from School A attempts to analyze a student belonging to School B
        $response = $this->actingAs($this->teacherSchoolA)
            ->getJson("/api/v1/ai/student-analytics/{$this->studentSchoolB->id}");

        // Must reject with 404 or 403 due to tenant isolation
        $this->assertTrue(in_array($response->status(), [403, 404]));
    }
}
