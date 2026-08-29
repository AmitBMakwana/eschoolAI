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

        // 2. Classes & Sections
        $class8 = SchoolClass::updateOrCreate(['name' => 'Class 8', 'tenant_id' => $schoolA->id], [
            'code' => 'C8',
            'order_index' => 8,
        ]);

        $sectionA = Section::updateOrCreate(['class_id' => $class8->id, 'name' => 'A', 'tenant_id' => $schoolA->id], [
            'capacity' => 35,
            'class_teacher_id' => $teacherUser->id,
        ]);

        $sectionB = Section::updateOrCreate(['class_id' => $class8->id, 'name' => 'B', 'tenant_id' => $schoolA->id], [
            'capacity' => 35,
        ]);

        $class9 = SchoolClass::updateOrCreate(['name' => 'Class 9', 'tenant_id' => $schoolA->id], [
            'code' => 'C9',
            'order_index' => 9,
        ]);

        Section::updateOrCreate(['class_id' => $class9->id, 'name' => 'A', 'tenant_id' => $schoolA->id], [
            'capacity' => 35,
        ]);

        // 3. Subjects
        $science = Subject::updateOrCreate(['name' => 'Science', 'class_id' => $class8->id, 'tenant_id' => $schoolA->id], [
            'code' => 'SCI-8',
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

        // 4. Link Demo Student (Alex Miller)
        $studentAlex = Student::updateOrCreate(['user_id' => $studentUser->id, 'tenant_id' => $schoolA->id], [
            'class_id' => $class8->id,
            'section_id' => $sectionA->id,
            'parent_id' => $parentProfile->id,
            'admission_number' => 'ADM-2026-084',
            'roll_number' => '101',
            'dob' => '2012-05-14',
            'gender' => 'male',
            'blood_group' => 'O+',
            'enrollment_date' => '2026-06-01',
            'status' => 'active',
        ]);

        // Additional sample students
        $studentRole = Role::where('slug', Role::STUDENT)->firstOrFail();
        $sampleStudents = [
            ['name' => 'Sophia Chen', 'email' => 'sophia@greenfield.edu', 'adm' => 'ADM-2026-085', 'roll' => '102', 'gender' => 'female'],
            ['name' => 'David Kumar', 'email' => 'david@greenfield.edu', 'adm' => 'ADM-2026-086', 'roll' => '103', 'gender' => 'male'],
            ['name' => 'Emma Watson', 'email' => 'emma@greenfield.edu', 'adm' => 'ADM-2026-087', 'roll' => '104', 'gender' => 'female'],
        ];

        foreach ($sampleStudents as $sData) {
            $u = User::withoutGlobalScopes()->updateOrCreate(['email' => $sData['email']], [
                'name' => $sData['name'],
                'password' => Hash::make('password123'),
                'role_id' => $studentRole->id,
                'tenant_id' => $schoolA->id,
                'status' => 'active',
            ]);

            Student::updateOrCreate(['user_id' => $u->id, 'tenant_id' => $schoolA->id], [
                'class_id' => $class8->id,
                'section_id' => $sectionA->id,
                'admission_number' => $sData['adm'],
                'roll_number' => $sData['roll'],
                'gender' => $sData['gender'],
                'status' => 'active',
            ]);
        }

        // 5. Teacher Allocations
        TeacherAllocation::updateOrCreate([
            'teacher_user_id' => $teacherUser->id,
            'class_id' => $class8->id,
            'section_id' => $sectionA->id,
            'subject_id' => $science->id,
            'tenant_id' => $schoolA->id,
        ]);

        // 6. Timetable
        Timetable::updateOrCreate([
            'class_id' => $class8->id,
            'section_id' => $sectionA->id,
            'subject_id' => $science->id,
            'day_of_week' => 'monday',
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'tenant_id' => $schoolA->id,
        ], [
            'teacher_user_id' => $teacherUser->id,
            'room_number' => 'Lab 2',
        ]);

        // 7. Sample Attendance
        Attendance::updateOrCreate([
            'student_id' => $studentAlex->id,
            'date' => date('Y-m-d'),
            'tenant_id' => $schoolA->id,
        ], [
            'class_id' => $class8->id,
            'section_id' => $sectionA->id,
            'status' => 'present',
            'marked_by_user_id' => $teacherUser->id,
        ]);

        // 8. Homework
        Homework::updateOrCreate([
            'class_id' => $class8->id,
            'section_id' => $sectionA->id,
            'subject_id' => $science->id,
            'title' => 'Frictional Forces Lab Worksheet',
            'tenant_id' => $schoolA->id,
        ], [
            'assigned_by_user_id' => $teacherUser->id,
            'description' => 'Complete questions 1 through 10 on page 48 regarding static vs kinetic friction.',
            'due_date' => date('Y-m-d', strtotime('+3 days')),
        ]);

        // 9. Notices
        Notice::updateOrCreate([
            'title' => 'Science Exhibition & Robotics Fair 2026',
            'tenant_id' => $schoolA->id,
        ], [
            'created_by_user_id' => $adminUser->id,
            'content' => 'Annual Science & Tech Fair will take place on Friday, 18th September. All students are encouraged to submit project proposals.',
            'audience_type' => 'all',
            'is_published' => true,
            'published_at' => now(),
        ]);

        TenantContext::clear();
    }
}
