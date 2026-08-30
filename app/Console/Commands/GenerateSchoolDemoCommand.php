<?php

namespace App\Console\Commands;

use App\Models\FeeHead;
use App\Models\FeeStructure;
use App\Models\Plan;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentFeeInvoice;
use App\Models\Subject;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GenerateSchoolDemoCommand extends Command
{
    protected $signature = 'schoolos:demo-school {--name=St. Xavier International Academy} {--subdomain=xavier}';
    protected $description = 'Provision and seed a fully functional demo school tenant with students, teachers, classes, fees, and curriculum.';

    public function handle(): int
    {
        $name = $this->option('name');
        $subdomain = Str::slug($this->option('subdomain'));

        $this->info("Creating tenant school: {$name} ({$subdomain}.schoolos.internal)...");

        // 1. Create Tenant
        $tenant = Tenant::updateOrCreate(
            ['subdomain' => $subdomain],
            [
                'name' => $name,
                'status' => 'active',
                'email' => "admin@{$subdomain}.edu",
                'phone' => '+1 555 019 2831',
                'currency' => 'USD',
                'timezone' => 'America/New_York',
                'plan_tier' => 'enterprise',
            ]
        );

        TenantContext::set($tenant);

        // 2. Assign Enterprise Subscription
        $plan = Plan::where('slug', 'enterprise')->first();
        if ($plan) {
            Subscription::updateOrCreate(
                ['tenant_id' => $tenant->id],
                [
                    'plan_id' => $plan->id,
                    'status' => 'active',
                    'billing_cycle' => 'annual',
                    'starts_at' => now(),
                    'renews_at' => now()->addYear(),
                    'custom_ai_credits' => 2000000,
                ]
            );
        }

        // 3. Create School Admin & Principal
        $adminRole = Role::where('slug', Role::SCHOOL_ADMIN)->first();
        $teacherRole = Role::where('slug', Role::TEACHER)->first();
        $studentRole = Role::where('slug', Role::STUDENT)->first();

        $admin = User::updateOrCreate(
            ['email' => "admin@{$subdomain}.edu"],
            [
                'tenant_id' => $tenant->id,
                'role_id' => $adminRole?->id,
                'name' => "Dr. Arthur Vance (Principal)",
                'password' => Hash::make('password'),
                'status' => 'active',
            ]
        );

        // 4. Create Teachers
        $teachers = [
            ['name' => 'Prof. Elena Rostova', 'email' => "elena@{$subdomain}.edu", 'subject' => 'Advanced Physics'],
            ['name' => 'Dr. Marcus Sterling', 'email' => "marcus@{$subdomain}.edu", 'subject' => 'Calculus & Statistics'],
            ['name' => 'Sarah Jenkins', 'email' => "sarah@{$subdomain}.edu", 'subject' => 'English Literature'],
        ];

        foreach ($teachers as $t) {
            User::updateOrCreate(
                ['email' => $t['email']],
                [
                    'tenant_id' => $tenant->id,
                    'role_id' => $teacherRole?->id,
                    'name' => $t['name'],
                    'password' => Hash::make('password'),
                    'status' => 'active',
                ]
            );
        }

        // 5. Create Classes, Sections & Subjects
        for ($grade = 9; $grade <= 12; $grade++) {
            $class = SchoolClass::updateOrCreate(
                ['tenant_id' => $tenant->id, 'name' => "Grade {$grade}"],
                ['code' => "G{$grade}", 'numeric_order' => $grade]
            );

            Section::updateOrCreate(
                ['tenant_id' => $tenant->id, 'class_id' => $class->id, 'name' => 'A'],
                ['capacity' => 35]
            );

            Subject::updateOrCreate(
                ['tenant_id' => $tenant->id, 'class_id' => $class->id, 'name' => 'Physics'],
                ['code' => "PHY{$grade}", 'type' => 'both']
            );

            Subject::updateOrCreate(
                ['tenant_id' => $tenant->id, 'class_id' => $class->id, 'name' => 'Mathematics'],
                ['code' => "MTH{$grade}", 'type' => 'theory']
            );
        }

        // 6. Create Demo Students
        $grade10 = SchoolClass::where('tenant_id', $tenant->id)->where('name', 'Grade 10')->first();
        $secA = Section::where('tenant_id', $tenant->id)->where('class_id', $grade10?->id)->first();

        $studentUsers = [
            ['name' => 'Liam Alexander', 'email' => "liam@{$subdomain}.edu", 'adm' => "ADM-{$subdomain}-01"],
            ['name' => 'Sophia Chen', 'email' => "sophia@{$subdomain}.edu", 'adm' => "ADM-{$subdomain}-02"],
            ['name' => 'Noah Miller', 'email' => "noah@{$subdomain}.edu", 'adm' => "ADM-{$subdomain}-03"],
        ];

        foreach ($studentUsers as $idx => $s) {
            $u = User::updateOrCreate(
                ['email' => $s['email']],
                [
                    'tenant_id' => $tenant->id,
                    'role_id' => $studentRole?->id,
                    'name' => $s['name'],
                    'password' => Hash::make('password'),
                    'status' => 'active',
                ]
            );

            Student::updateOrCreate(
                ['tenant_id' => $tenant->id, 'admission_number' => $s['adm']],
                [
                    'user_id' => $u->id,
                    'class_id' => $grade10->id,
                    'section_id' => $secA->id,
                    'roll_number' => str_pad((string)($idx + 1), 2, '0', STR_PAD_LEFT),
                    'dob' => '2010-05-15',
                    'gender' => 'other',
                    'blood_group' => 'O+',
                    'enrollment_date' => now()->subMonths(6),
                    'status' => 'active',
                ]
            );
        }

        // 7. Create Fee Structures
        $tuition = FeeHead::updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Tuition & Laboratory Fee'],
            ['description' => 'Semester academic tuition and lab access']
        );

        $structure = FeeStructure::updateOrCreate(
            ['tenant_id' => $tenant->id, 'class_id' => $grade10->id, 'fee_head_id' => $tuition->id],
            ['amount' => 1200.00, 'academic_year' => '2026-2027', 'due_date' => now()->addMonths(2)]
        );

        $this->info("✅ Successfully generated demo school '{$name}'!");
        $this->line("   - Subdomain: http://{$subdomain}.schoolos.internal");
        $this->line("   - Admin Login: admin@{$subdomain}.edu (password: password)");
        $this->line("   - Teacher Login: elena@{$subdomain}.edu (password: password)");
        $this->line("   - Student Login: liam@{$subdomain}.edu (password: password)");

        return 0;
    }
}
