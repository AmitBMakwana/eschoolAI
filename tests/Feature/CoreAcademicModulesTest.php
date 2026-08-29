<?php

namespace Tests\Feature;

use App\Models\Homework;
use App\Models\Message;
use App\Models\Notice;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoreAcademicModulesTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $schoolA;
    protected User $adminSchoolA;
    protected User $teacherSchoolA;
    protected User $studentSchoolA;
    protected User $parentSchoolA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->schoolA = Tenant::where('subdomain', 'greenfield')->firstOrFail();
        $this->adminSchoolA = User::where('email', 'admin@greenfield.edu')->firstOrFail();
        $this->teacherSchoolA = User::where('email', 'teacher@greenfield.edu')->firstOrFail();
        $this->studentSchoolA = User::where('email', 'student@greenfield.edu')->firstOrFail();
        $this->parentSchoolA = User::where('email', 'parent@greenfield.edu')->firstOrFail();
    }

    public function test_can_list_and_create_classes(): void
    {
        $token = $this->adminSchoolA->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/classes');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertNotEmpty($response->json('data'));

        // Create new Class 10
        $createRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/classes', [
                'name' => 'Class 10',
                'code' => 'C10',
                'sections' => ['A', 'B'],
            ]);

        $createRes->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Class 10',
                ]
            ]);

        $this->assertDatabaseHas('school_classes', [
            'tenant_id' => $this->schoolA->id,
            'name' => 'Class 10',
        ]);
    }

    public function test_student_directory_and_enrollment(): void
    {
        $token = $this->adminSchoolA->createToken('test_token')->plainTextToken;

        // List students
        $listRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/students?search=Alex');

        $listRes->assertStatus(200);
        $this->assertEquals('Alex Miller', $listRes->json('data.0.user.name'));

        // Enroll new student
        $class8 = SchoolClass::where('name', 'Class 8')->firstOrFail();
        $sectionA = Section::where('class_id', $class8->id)->firstOrFail();

        $enrollRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/students', [
                'name' => 'Lucas Vance',
                'email' => 'lucas.vance@greenfield.edu',
                'class_id' => $class8->id,
                'section_id' => $sectionA->id,
                'parent_name' => 'Eleanor Vance',
                'parent_email' => 'eleanor.vance@greenfield.edu',
                'parent_phone' => '+1555234567',
                'roll_number' => '108',
            ]);

        $enrollRes->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'roll_number' => '108',
                ]
            ]);

        $this->assertDatabaseHas('users', ['email' => 'lucas.vance@greenfield.edu']);
        $this->assertDatabaseHas('students', ['roll_number' => '108']);
    }

    public function test_bulk_attendance_marking_and_summary(): void
    {
        $token = $this->teacherSchoolA->createToken('test_token')->plainTextToken;
        $class8 = SchoolClass::where('name', 'Class 8')->firstOrFail();
        $sectionA = Section::where('class_id', $class8->id)->firstOrFail();
        $students = Student::where('class_id', $class8->id)->where('section_id', $sectionA->id)->get();

        $records = $students->map(function ($s, $idx) {
            return [
                'student_id' => $s->id,
                'status' => $idx === 0 ? 'absent' : 'present',
                'remarks' => $idx === 0 ? 'Sick leave' : null,
            ];
        })->toArray();

        $date = '2026-09-01';

        $markRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/attendance/mark', [
                'class_id' => $class8->id,
                'section_id' => $sectionA->id,
                'date' => $date,
                'records' => $records,
            ]);

        $markRes->assertStatus(200)
            ->assertJson(['success' => true]);

        // Get Summary
        $sumRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/attendance/summary?class_id={$class8->id}&section_id={$sectionA->id}&date={$date}");

        $sumRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'date' => $date,
                    'absent' => 1,
                ]
            ]);
    }

    public function test_homework_assignment_submission_and_grading(): void
    {
        $class8 = SchoolClass::where('name', 'Class 8')->firstOrFail();
        $sectionA = Section::where('class_id', $class8->id)->firstOrFail();
        $subject = Subject::where('name', 'Science')->firstOrFail();

        // 1. Teacher assigns homework
        $assignRes = $this->actingAs($this->teacherSchoolA)
            ->postJson('/api/v1/homework', [
                'class_id' => $class8->id,
                'section_id' => $sectionA->id,
                'subject_id' => $subject->id,
                'title' => 'Thermodynamics Problem Set',
                'description' => 'Solve exercises 1-5',
                'due_date' => date('Y-m-d', strtotime('+5 days')),
            ]);

        $assignRes->assertStatus(201);
        $hwId = $assignRes->json('data.id');

        // 2. Student submits homework
        $submitRes = $this->actingAs($this->studentSchoolA)
            ->postJson("/api/v1/homework/{$hwId}/submit", [
                'submission_text' => 'Here are my solutions to problems 1-5.',
            ]);

        $submitRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'submitted',
                ]
            ]);
        $subId = $submitRes->json('data.id');

        // 3. Teacher reviews and grades submission
        $gradeRes = $this->actingAs($this->teacherSchoolA)
            ->postJson("/api/v1/homework/submissions/{$subId}/review", [
                'marks' => 95.0,
                'teacher_feedback' => 'Excellent work and clear derivations.',
            ]);

        $gradeRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'marks' => 95.0,
                    'status' => 'reviewed',
                ]
            ]);
    }

    public function test_notices_and_direct_messaging(): void
    {
        // 1. Admin publishes notice
        $noticeRes = $this->actingAs($this->adminSchoolA)
            ->postJson('/api/v1/notices', [
                'title' => 'Parent Teacher Conference Next Friday',
                'content' => 'Please join us from 3 PM onwards.',
                'audience_type' => 'parents',
            ]);

        $noticeRes->assertStatus(201);

        // 2. Parent sends message to Teacher
        $sendRes = $this->actingAs($this->parentSchoolA)
            ->postJson('/api/v1/messages/send', [
                'receiver_user_id' => $this->teacherSchoolA->id,
                'message_body' => 'Hello Sarah, could we schedule a 15-minute sync regarding Alex math score?',
            ]);

        $sendRes->assertStatus(201);

        // 3. Teacher fetches message thread
        $threadRes = $this->actingAs($this->teacherSchoolA)
            ->getJson("/api/v1/messages?peer_user_id={$this->parentSchoolA->id}");

        $threadRes->assertStatus(200);
        $this->assertCount(1, $threadRes->json('data'));
        $this->assertEquals('Hello Sarah, could we schedule a 15-minute sync regarding Alex math score?', $threadRes->json('data.0.message_body'));
    }
}
