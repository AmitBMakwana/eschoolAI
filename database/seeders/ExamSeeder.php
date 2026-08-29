<?php

namespace Database\Seeders;

use App\Models\Exam;
use App\Models\ExamTerm;
use App\Models\GradingScale;
use App\Models\QuestionBank;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Database\Seeder;

class ExamSeeder extends Seeder
{
    public function run(): void
    {
        $schoolA = Tenant::where('subdomain', 'greenfield')->firstOrFail();
        TenantContext::set($schoolA);

        $teacherUser = User::withoutGlobalScopes()->where('email', 'teacher@greenfield.edu')->firstOrFail();
        $class8 = SchoolClass::where('name', 'Class 8')->firstOrFail();
        $science = Subject::where('name', 'Science')->firstOrFail();
        $math = Subject::where('name', 'Mathematics')->firstOrFail();

        // 1. Grading Scales (Standard Letter & GPA)
        $grades = [
            ['grade' => 'A+', 'min_percentage' => 90.0, 'max_percentage' => 100.0, 'grade_point' => 4.0, 'description' => 'Outstanding'],
            ['grade' => 'A',  'min_percentage' => 80.0, 'max_percentage' => 89.99, 'grade_point' => 3.7, 'description' => 'Excellent'],
            ['grade' => 'B',  'min_percentage' => 70.0, 'max_percentage' => 79.99, 'grade_point' => 3.0, 'description' => 'Very Good'],
            ['grade' => 'C',  'min_percentage' => 60.0, 'max_percentage' => 69.99, 'grade_point' => 2.0, 'description' => 'Good'],
            ['grade' => 'D',  'min_percentage' => 50.0, 'max_percentage' => 59.99, 'grade_point' => 1.0, 'description' => 'Pass'],
            ['grade' => 'F',  'min_percentage' => 0.0,  'max_percentage' => 49.99, 'grade_point' => 0.0, 'description' => 'Fail'],
        ];

        foreach ($grades as $g) {
            GradingScale::updateOrCreate(['grade' => $g['grade'], 'tenant_id' => $schoolA->id], $g);
        }

        // 2. Exam Term
        $term1 = ExamTerm::updateOrCreate(['name' => 'Mid-Term Examination 2026', 'tenant_id' => $schoolA->id], [
            'academic_year' => '2026-2027',
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-25',
            'status' => 'upcoming',
        ]);

        // 3. Exams
        Exam::updateOrCreate(['title' => 'Science Mid-Term Assessment', 'tenant_id' => $schoolA->id], [
            'exam_term_id' => $term1->id,
            'class_id' => $class8->id,
            'subject_id' => $science->id,
            'exam_date' => '2026-10-12',
            'start_time' => '09:30:00',
            'end_time' => '12:30:00',
            'total_marks' => 100.0,
            'passing_marks' => 35.0,
            'room_number' => 'Hall A',
        ]);

        Exam::updateOrCreate(['title' => 'Mathematics Mid-Term Assessment', 'tenant_id' => $schoolA->id], [
            'exam_term_id' => $term1->id,
            'class_id' => $class8->id,
            'subject_id' => $math->id,
            'exam_date' => '2026-10-15',
            'start_time' => '09:30:00',
            'end_time' => '12:30:00',
            'total_marks' => 100.0,
            'passing_marks' => 35.0,
            'room_number' => 'Hall A',
        ]);

        // 4. Sample Question Bank
        $sampleQuestions = [
            [
                'subject_id' => $science->id,
                'class_id' => $class8->id,
                'topic' => 'Force and Pressure',
                'difficulty' => 'easy',
                'question_type' => 'mcq',
                'question_text' => 'What is the SI unit of force?',
                'options' => ['Pascal', 'Newton', 'Joule', 'Watt'],
                'correct_answer' => 'Newton',
                'explanation' => 'Force is measured in Newtons (N) in honor of Sir Isaac Newton.',
                'marks' => 1.0,
            ],
            [
                'subject_id' => $science->id,
                'class_id' => $class8->id,
                'topic' => 'Friction',
                'difficulty' => 'medium',
                'question_type' => 'short_answer',
                'question_text' => 'Explain why sliding friction is less than static friction.',
                'options' => null,
                'correct_answer' => 'Interlocking of surface irregularities has less time to form during sliding movement.',
                'explanation' => 'When surfaces are already in relative motion, microscopic ridges glide past each other without settling deeply.',
                'marks' => 3.0,
            ],
            [
                'subject_id' => $science->id,
                'class_id' => $class8->id,
                'topic' => 'Friction & Lubrication',
                'difficulty' => 'hard',
                'question_type' => 'essay',
                'question_text' => 'Discuss five engineering applications where friction is deliberately minimized using fluid lubrication and ball bearings.',
                'options' => null,
                'correct_answer' => 'Turbines, vehicle crankshafts, rolling stock wheelsets, electric motors, and computer hard drives.',
                'explanation' => 'Fluid thin films and rolling contact elements reduce shear resistance.',
                'marks' => 5.0,
            ],
        ];

        foreach ($sampleQuestions as $q) {
            QuestionBank::updateOrCreate(['question_text' => $q['question_text'], 'tenant_id' => $schoolA->id], array_merge($q, ['created_by_user_id' => $teacherUser->id]));
        }

        TenantContext::clear();
    }
}
