<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Core System Roles
        $roles = [
            Role::SUPER_ADMIN => [
                'name' => 'Super Admin',
                'description' => 'Platform-level super administrator',
                'scope' => 'platform',
            ],
            Role::SCHOOL_ADMIN => [
                'name' => 'School Admin',
                'description' => 'Full administrative access for the school tenant',
                'scope' => 'school',
            ],
            Role::PRINCIPAL => [
                'name' => 'Principal',
                'description' => 'School principal with broad academic & administrative oversight',
                'scope' => 'school',
            ],
            Role::TEACHER => [
                'name' => 'Teacher',
                'description' => 'Teacher with class, attendance, assignment, and AI tools access',
                'scope' => 'school',
            ],
            Role::STUDENT => [
                'name' => 'Student',
                'description' => 'Student with personal academic view and AI tutor access',
                'scope' => 'school',
            ],
            Role::PARENT => [
                'name' => 'Parent',
                'description' => 'Parent with multi-child monitoring and fee payment access',
                'scope' => 'school',
            ],
            Role::ACCOUNTANT => [
                'name' => 'Accountant',
                'description' => 'Finance and fee collection specialist',
                'scope' => 'school',
            ],
            Role::STAFF => [
                'name' => 'Staff',
                'description' => 'Operational staff with restricted module access',
                'scope' => 'school',
            ],
        ];

        $roleModels = [];
        foreach ($roles as $slug => $data) {
            $roleModels[$slug] = Role::updateOrCreate(['slug' => $slug], [
                'name' => $data['name'],
                'description' => $data['description'],
                'scope' => $data['scope'],
                'is_system' => true,
            ]);
        }

        // 2. Create Core Permissions across modules
        $permissions = [
            // Academics
            ['name' => 'Manage Classes', 'slug' => 'classes.manage', 'module' => 'academics'],
            ['name' => 'View Classes', 'slug' => 'classes.view', 'module' => 'academics'],
            ['name' => 'Manage Students', 'slug' => 'students.manage', 'module' => 'academics'],
            ['name' => 'View Students', 'slug' => 'students.view', 'module' => 'academics'],
            ['name' => 'Manage Teachers', 'slug' => 'teachers.manage', 'module' => 'academics'],
            ['name' => 'View Teachers', 'slug' => 'teachers.view', 'module' => 'academics'],
            ['name' => 'Mark Attendance', 'slug' => 'attendance.mark', 'module' => 'academics'],
            ['name' => 'View Attendance', 'slug' => 'attendance.view', 'module' => 'academics'],
            ['name' => 'Manage Homework', 'slug' => 'homework.manage', 'module' => 'academics'],
            ['name' => 'Submit Homework', 'slug' => 'homework.submit', 'module' => 'academics'],
            ['name' => 'Manage Timetable', 'slug' => 'timetable.manage', 'module' => 'academics'],
            ['name' => 'View Timetable', 'slug' => 'timetable.view', 'module' => 'academics'],

            // Finance
            ['name' => 'Manage Fee Structures', 'slug' => 'fees.manage', 'module' => 'finance'],
            ['name' => 'Collect Fees', 'slug' => 'fees.collect', 'module' => 'finance'],
            ['name' => 'Approve Concessions', 'slug' => 'concessions.approve', 'module' => 'finance'],
            ['name' => 'View Fee Reports', 'slug' => 'finance.reports', 'module' => 'finance'],

            // Examinations
            ['name' => 'Manage Exams', 'slug' => 'exams.manage', 'module' => 'exams'],
            ['name' => 'Enter Results', 'slug' => 'results.enter', 'module' => 'exams'],
            ['name' => 'Publish Results', 'slug' => 'results.publish', 'module' => 'exams'],
            ['name' => 'View Results', 'slug' => 'results.view', 'module' => 'exams'],

            // Communication
            ['name' => 'Publish Notices', 'slug' => 'notices.publish', 'module' => 'communication'],
            ['name' => 'Send Messages', 'slug' => 'messages.send', 'module' => 'communication'],

            // AI Education
            ['name' => 'Use AI Assistant', 'slug' => 'ai.use', 'module' => 'ai'],
            ['name' => 'Generate Lesson Plans', 'slug' => 'ai.lesson_plan', 'module' => 'ai'],
            ['name' => 'Generate Question Papers', 'slug' => 'ai.question_paper', 'module' => 'ai'],
            ['name' => 'Generate Worksheets', 'slug' => 'ai.worksheet', 'module' => 'ai'],
            ['name' => 'Evaluate Answer Sheets', 'slug' => 'ai.evaluate_answers', 'module' => 'ai'],
            ['name' => 'Generate Circulars', 'slug' => 'ai.circular', 'module' => 'ai'],
            ['name' => 'Analyze Student Reports', 'slug' => 'ai.student_report', 'module' => 'ai'],
            ['name' => 'Manage Knowledge Base', 'slug' => 'ai.knowledge_base', 'module' => 'ai'],

            // Settings & Platform
            ['name' => 'Manage School Settings', 'slug' => 'settings.school', 'module' => 'settings'],
            ['name' => 'Manage Billing & Subscription', 'slug' => 'billing.manage', 'module' => 'billing'],
            ['name' => 'View Audit Logs', 'slug' => 'audit.view', 'module' => 'audit'],
        ];

        $permissionModels = [];
        foreach ($permissions as $perm) {
            $permissionModels[$perm['slug']] = Permission::updateOrCreate(['slug' => $perm['slug']], [
                'name' => $perm['name'],
                'module' => $perm['module'],
            ]);
        }

        // 3. Assign Permissions to School Admin (all school-level permissions)
        $schoolAdminPerms = array_values(array_map(fn($p) => $p->id, $permissionModels));
        $roleModels[Role::SCHOOL_ADMIN]->permissions()->sync($schoolAdminPerms);
        $roleModels[Role::PRINCIPAL]->permissions()->sync($schoolAdminPerms);

        // Teacher Permissions
        $teacherPermSlugs = [
            'classes.view', 'students.view', 'attendance.mark', 'attendance.view',
            'homework.manage', 'timetable.view', 'exams.manage', 'results.enter',
            'results.view', 'notices.publish', 'messages.send', 'ai.use',
            'ai.lesson_plan', 'ai.question_paper', 'ai.worksheet', 'ai.evaluate_answers',
            'ai.knowledge_base'
        ];
        $teacherPermIds = collect($teacherPermSlugs)->map(fn($slug) => $permissionModels[$slug]->id ?? null)->filter()->all();
        $roleModels[Role::TEACHER]->permissions()->sync($teacherPermIds);

        // Student Permissions
        $studentPermSlugs = [
            'classes.view', 'timetable.view', 'homework.submit', 'results.view',
            'messages.send', 'ai.use'
        ];
        $studentPermIds = collect($studentPermSlugs)->map(fn($slug) => $permissionModels[$slug]->id ?? null)->filter()->all();
        $roleModels[Role::STUDENT]->permissions()->sync($studentPermIds);

        // Parent Permissions
        $parentPermSlugs = [
            'classes.view', 'timetable.view', 'results.view', 'messages.send'
        ];
        $parentPermIds = collect($parentPermSlugs)->map(fn($slug) => $permissionModels[$slug]->id ?? null)->filter()->all();
        $roleModels[Role::PARENT]->permissions()->sync($parentPermIds);

        // Accountant Permissions
        $accountantPermSlugs = [
            'fees.manage', 'fees.collect', 'concessions.approve', 'finance.reports', 'students.view'
        ];
        $accountantPermIds = collect($accountantPermSlugs)->map(fn($slug) => $permissionModels[$slug]->id ?? null)->filter()->all();
        $roleModels[Role::ACCOUNTANT]->permissions()->sync($accountantPermIds);
    }
}
