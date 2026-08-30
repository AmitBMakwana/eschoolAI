<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Homework;
use App\Models\Notice;
use App\Models\ParentProfile;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TeacherAllocation;
use App\Models\Tenant;
use App\Models\Timetable;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AcademicSeeder extends Seeder
{
    public function run(): void
    {
        $schoolA = Tenant::where('subdomain', 'greenfield')->firstOrFail();
        TenantContext::set($schoolA);

        $teacherUser = User::withoutGlobalScopes()->where('email', 'teacher@greenfield.edu')->firstOrFail();
        $studentUser = User::withoutGlobalScopes()->where('email', 'student@greenfield.edu')->firstOrFail();
        $parentUser = User::withoutGlobalScopes()->where('email', 'parent@greenfield.edu')->firstOrFail();
        $adminUser = User::withoutGlobalScopes()->where('email', 'admin@greenfield.edu')->firstOrFail();

        // 1. Parent Profile
        $parentProfile = ParentProfile::updateOrCreate(['user_id' => $parentUser->id, 'tenant_id' => $schoolA->id], [
            'occupation' => 'Architect',
            'relationship' => 'Father',
            'alternate_phone' => '+1234567899',
        ]);

        // 2. Classes (Class 1 to 10)
        $classes = [];
        for ($i = 1; $i <= 10; $i++) {
            $classes[$i] = SchoolClass::updateOrCreate(['name' => "Class {$i}", 'tenant_id' => $schoolA->id], [
                'code' => "C{$i}",
                'order_index' => $i,
            ]);

            Section::updateOrCreate(['class_id' => $classes[$i]->id, 'name' => 'A', 'tenant_id' => $schoolA->id], [
                'capacity' => 40,
                'class_teacher_id' => $teacherUser->id,
            ]);

            Section::updateOrCreate(['class_id' => $classes[$i]->id, 'name' => 'B', 'tenant_id' => $schoolA->id], [
                'capacity' => 40,
            ]);
        }

        $class1 = $classes[1];
        $class2 = $classes[2];
        $class3 = $classes[3];
        $class8 = $classes[8];
        $class9 = $classes[9];
        $sectionA = Section::where('class_id', $class9->id)->where('name', 'A')->first();

        // 3. Subjects
        $science = Subject::updateOrCreate(['name' => 'Science', 'class_id' => $class8->id, 'tenant_id' => $schoolA->id], [
            'code' => 'SCI-8',
            'type' => 'both',
        ]);

        $physics = Subject::updateOrCreate(['name' => 'Physics', 'class_id' => $class9->id, 'tenant_id' => $schoolA->id], [
            'code' => 'PHY-9',
            'type' => 'both',
        ]);

        $math = Subject::updateOrCreate(['name' => 'Mathematics', 'class_id' => $class8->id, 'tenant_id' => $schoolA->id], [
            'code' => 'MATH-8',
            'type' => 'theory',
        ]);

        $english = Subject::updateOrCreate(['name' => 'English Literature', 'class_id' => $class8->id, 'tenant_id' => $schoolA->id], [
            'code' => 'ENG-8',
            'type' => 'theory',
        ]);

        $chemistry = Subject::updateOrCreate(['name' => 'Chemistry', 'class_id' => $class9->id, 'tenant_id' => $schoolA->id], [
            'code' => 'CHEM-9',
            'type' => 'both',
        ]);

        // 4. Seed All Reference Students from Video
        $studentRole = Role::where('slug', Role::STUDENT)->firstOrFail();
        $sampleStudents = [
            ['name' => 'Alfiya Farooqui', 'email' => 'alfiya@greenfield.edu', 'adm' => 'ADM-2026-001', 'roll' => '01', 'gender' => 'female', 'class' => $class9->id],
            ['name' => 'Shoaib Rastogi', 'email' => 'shoaib@greenfield.edu', 'adm' => 'ADM-2026-002', 'roll' => '02', 'gender' => 'male', 'class' => $class9->id],
            ['name' => 'Junaid Tyagi', 'email' => 'junaid@greenfield.edu', 'adm' => 'ADM-2026-003', 'roll' => '01', 'gender' => 'male', 'class' => $class2->id],
            ['name' => 'Palak Chauhan', 'email' => 'palak@greenfield.edu', 'adm' => 'ADM-2026-004', 'roll' => '02', 'gender' => 'female', 'class' => $class2->id],
            ['name' => 'Areeba Saifi', 'email' => 'areeba@greenfield.edu', 'adm' => 'ADM-2026-005', 'roll' => '03', 'gender' => 'female', 'class' => $class2->id],
            ['name' => 'Aarav Idrisi', 'email' => 'aarav.idrisi@greenfield.edu', 'adm' => 'ADM-2026-006', 'roll' => '04', 'gender' => 'male', 'class' => $class2->id],
            ['name' => 'Aarav Rastogi', 'email' => 'aarav.r@greenfield.edu', 'adm' => 'ADM-2026-007', 'roll' => '01', 'gender' => 'male', 'class' => $class3->id],
            ['name' => 'Ananya Qureshi', 'email' => 'ananya@greenfield.edu', 'adm' => 'ADM-2026-008', 'roll' => '02', 'gender' => 'female', 'class' => $class3->id],
            ['name' => 'Anas Gupta', 'email' => 'anas@greenfield.edu', 'adm' => 'ADM-2026-009', 'roll' => '03', 'gender' => 'male', 'class' => $class3->id],
            ['name' => 'Rehan Khan', 'email' => 'rehan@greenfield.edu', 'adm' => 'ADM-2026-010', 'roll' => '01', 'gender' => 'male', 'class' => $class1->id],
            ['name' => 'Alex Miller', 'email' => 'student@greenfield.edu', 'adm' => 'ADM-2026-084', 'roll' => '101', 'gender' => 'male', 'class' => $class8->id],
            ['name' => 'Sophia Chen', 'email' => 'sophia@greenfield.edu', 'adm' => 'ADM-2026-085', 'roll' => '102', 'gender' => 'female', 'class' => $class8->id],
            ['name' => 'David Kumar', 'email' => 'david@greenfield.edu', 'adm' => 'ADM-2026-086', 'roll' => '103', 'gender' => 'male', 'class' => $class8->id],
            ['name' => 'Emma Watson', 'email' => 'emma@greenfield.edu', 'adm' => 'ADM-2026-087', 'roll' => '104', 'gender' => 'female', 'class' => $class8->id],
        ];

        foreach ($sampleStudents as $sData) {
            $u = User::withoutGlobalScopes()->updateOrCreate(['email' => $sData['email']], [
                'name' => $sData['name'],
                'password' => Hash::make('password123'),
                'role_id' => $studentRole->id,
                'tenant_id' => $schoolA->id,
                'status' => 'active',
            ]);

            $sec = Section::where('class_id', $sData['class'])->first();

            Student::updateOrCreate(['user_id' => $u->id, 'tenant_id' => $schoolA->id], [
                'class_id' => $sData['class'],
                'section_id' => $sec?->id ?? $sectionA->id,
                'admission_number' => $sData['adm'],
                'roll_number' => $sData['roll'],
                'gender' => $sData['gender'],
                'status' => 'active',
            ]);
        }

        // 5. Teacher Allocations
        TeacherAllocation::updateOrCreate([
            'teacher_user_id' => $teacherUser->id,
            'class_id' => $class9->id,
            'section_id' => $sectionA->id,
            'subject_id' => $physics->id,
            'tenant_id' => $schoolA->id,
        ]);

        // 6. Timetable
        Timetable::updateOrCreate([
            'class_id' => $class9->id,
            'section_id' => $sectionA->id,
            'subject_id' => $physics->id,
            'day_of_week' => 'monday',
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'tenant_id' => $schoolA->id,
        ], [
            'teacher_user_id' => $teacherUser->id,
            'room_number' => 'Lab 2',
        ]);

        // 7. Sample Attendance
        $firstStudent = Student::where('admission_number', 'ADM-2026-001')->first();
        if ($firstStudent) {
            Attendance::updateOrCreate([
                'tenant_id' => $schoolA->id,
                'student_id' => $firstStudent->id,
                'date' => date('Y-m-d'),
            ], [
                'class_id' => $class9->id,
                'section_id' => $sectionA->id,
                'status' => 'present',
                'marked_by_user_id' => $teacherUser->id,
            ]);
        }

        // 8. Homework
        Homework::updateOrCreate([
            'class_id' => $class9->id,
            'section_id' => $sectionA->id,
            'subject_id' => $physics->id,
            'title' => 'Electromagnetic Induction & Lenz Law Problem Set',
            'tenant_id' => $schoolA->id,
        ], [
            'assigned_by_user_id' => $teacherUser->id,
            'description' => 'Complete problems 1 through 8 regarding magnetic flux and Lenz law.',
            'due_date' => date('Y-m-d', strtotime('+2 days')),
        ]);

        // 9. Notices
        Notice::updateOrCreate([
            'title' => 'Term 1 Mid-Year Examination Circular',
            'tenant_id' => $schoolA->id,
        ], [
            'created_by_user_id' => $adminUser->id,
            'content' => 'Mid-term examinations will commence from Oct 15. The detailed timetable is attached.',
            'audience_type' => 'all',
            'is_published' => true,
            'published_at' => now(),
        ]);

        TenantContext::clear();
    }
}
