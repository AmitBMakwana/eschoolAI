<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\ExamTerm;
use App\Models\QuestionBank;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExaminationAndResultsTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $schoolA;
    protected User $adminSchoolA;
    protected User $teacherSchoolA;
    protected User $studentSchoolA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->schoolA = Tenant::where('subdomain', 'greenfield')->firstOrFail();
        $this->adminSchoolA = User::where('email', 'admin@greenfield.edu')->firstOrFail();
        $this->teacherSchoolA = User::where('email', 'teacher@greenfield.edu')->firstOrFail();
        $this->studentSchoolA = User::where('email', 'student@greenfield.edu')->firstOrFail();
    }

    public function test_can_list_and_create_exam_terms(): void
    {
        $response = $this->actingAs($this->adminSchoolA)
            ->getJson('/api/v1/exams/terms');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertNotEmpty($response->json('data'));

        // Create Final Exam Term
        $createRes = $this->actingAs($this->adminSchoolA)
            ->postJson('/api/v1/exams/terms', [
                'name' => 'Annual Final Examination 2027',
                'academic_year' => '2026-2027',
                'start_date' => '2027-03-01',
                'end_date' => '2027-03-20',
            ]);

        $createRes->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Annual Final Examination 2027',
                ]
            ]);
    }

    public function test_can_schedule_exam_and_query_timetable(): void
    {
        $term = ExamTerm::where('name', 'Mid-Term Examination 2026')->firstOrFail();
        $class8 = SchoolClass::where('name', 'Class 8')->firstOrFail();
        $subject = Subject::where('name', 'English Literature')->firstOrFail();

        $scheduleRes = $this->actingAs($this->adminSchoolA)
            ->postJson('/api/v1/exams', [
                'exam_term_id' => $term->id,
                'class_id' => $class8->id,
                'subject_id' => $subject->id,
                'title' => 'English Literature Mid-Term',
                'exam_date' => '2026-10-18',
                'start_time' => '09:00',
                'end_time' => '12:00',
                'total_marks' => 100,
                'passing_marks' => 35,
                'room_number' => 'Hall B',
            ]);

        $scheduleRes->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'title' => 'English Literature Mid-Term',
                ]
            ]);
    }

    public function test_question_bank_and_paper_generation(): void
    {
        $science = Subject::where('name', 'Science')->firstOrFail();
        $class8 = SchoolClass::where('name', 'Class 8')->firstOrFail();

        // 1. Add question to Question Bank
        $qRes = $this->actingAs($this->teacherSchoolA)
            ->postJson('/api/v1/question-bank', [
                'subject_id' => $science->id,
                'class_id' => $class8->id,
                'topic' => 'Sound and Waves',
                'difficulty' => 'easy',
                'question_type' => 'mcq',
                'question_text' => 'Sound cannot travel through which of the following?',
                'options' => ['Air', 'Water', 'Vacuum', 'Steel'],
                'correct_answer' => 'Vacuum',
                'explanation' => 'Sound requires a material medium for propagation.',
                'marks' => 1.0,
            ]);

        $qRes->assertStatus(201);

        // 2. Query Question Bank
        $listRes = $this->actingAs($this->teacherSchoolA)
            ->getJson("/api/v1/question-bank?subject_id={$science->id}&difficulty=easy");

        $listRes->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, count($listRes->json('data')));

        // 3. Compose Question Paper
        $paperRes = $this->actingAs($this->teacherSchoolA)
            ->postJson('/api/v1/exam-papers/generate', [
                'subject_id' => $science->id,
                'class_id' => $class8->id,
                'title' => 'Class 8 Science Term 1 Question Paper',
                'instructions' => 'Time allowed: 3 hours. Maximum Marks: 100.',
                'total_marks' => 100,
                'duration_minutes' => 180,
                'easy_count' => 2,
                'medium_count' => 1,
                'hard_count' => 1,
            ]);

        $paperRes->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'title' => 'Class 8 Science Term 1 Question Paper',
                ]
            ]);
    }

    public function test_bulk_mark_entry_and_report_card_generation(): void
    {
        $scienceExam = Exam::where('title', 'Science Mid-Term Assessment')->firstOrFail();
        $mathExam = Exam::where('title', 'Mathematics Mid-Term Assessment')->firstOrFail();
        $class8 = SchoolClass::where('name', 'Class 8')->firstOrFail();
        $students = Student::where('class_id', $class8->id)->get();

        // 1. Teacher records marks for Science
        $scienceRecords = $students->map(function ($s, $idx) {
            return [
                'student_id' => $s->id,
                'marks_obtained' => $idx === 0 ? 92.5 : 78.0,
                'is_absent' => false,
                'remarks' => $idx === 0 ? 'Outstanding grasp of physics concepts' : 'Good work',
            ];
        })->toArray();

        $scienceMarksRes = $this->actingAs($this->teacherSchoolA)
            ->postJson("/api/v1/exams/{$scienceExam->id}/marks/bulk", [
                'records' => $scienceRecords,
            ]);

        $scienceMarksRes->assertStatus(200)
            ->assertJson(['success' => true]);

        // 2. Teacher records marks for Mathematics
        $mathRecords = $students->map(function ($s, $idx) {
            return [
                'student_id' => $s->id,
                'marks_obtained' => $idx === 0 ? 95.0 : 82.0,
                'is_absent' => false,
                'remarks' => $idx === 0 ? 'Flawless algebra derivations' : 'Good work',
            ];
        })->toArray();

        $mathMarksRes = $this->actingAs($this->teacherSchoolA)
            ->postJson("/api/v1/exams/{$mathExam->id}/marks/bulk", [
                'records' => $mathRecords,
            ]);

        $mathMarksRes->assertStatus(200)
            ->assertJson(['success' => true]);

        // 3. Fetch marks ledger for Science
        $ledgerRes = $this->actingAs($this->teacherSchoolA)
            ->getJson("/api/v1/exams/{$scienceExam->id}/marks");

        $ledgerRes->assertStatus(200);
        $this->assertEquals(92.5, $ledgerRes->json('data.ledger.0.marks_obtained'));
        $this->assertEquals('A+', $ledgerRes->json('data.ledger.0.grade'));

        // 4. Generate Report Card for Student Alex (Alex scored 92.5 in Science + 95.0 in Math = 187.5 / 200 = 93.75% -> A+)
        $alexStudent = Student::where('user_id', $this->studentSchoolA->id)->firstOrFail();
        $reportRes = $this->actingAs($this->adminSchoolA)
            ->getJson("/api/v1/exams/report-card/{$alexStudent->id}?exam_term_id={$scienceExam->exam_term_id}");

        $reportRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'percentage' => 93.75,
                    'overall_grade' => 'A+',
                ]
            ]);

        $this->assertGreaterThan(0, $reportRes->json('data.gpa'));
        $this->assertCount(2, $reportRes->json('data.subject_breakdown'));
    }
}
