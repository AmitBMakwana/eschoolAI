<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        TenantContext::clear();

        // 1. Roles & Plans
        $superAdminRole = Role::where('slug', Role::SUPER_ADMIN)->firstOrFail();
        $schoolAdminRole = Role::where('slug', Role::SCHOOL_ADMIN)->firstOrFail();
        $teacherRole = Role::where('slug', Role::TEACHER)->firstOrFail();
        $studentRole = Role::where('slug', Role::STUDENT)->firstOrFail();
        $parentRole = Role::where('slug', Role::PARENT)->firstOrFail();

        $proPlan = Plan::where('slug', 'professional')->firstOrFail();
        $starterPlan = Plan::where('slug', 'starter')->firstOrFail();

        // 2. Demo School A (Greenfield International School - Professional Plan)
        $schoolA = Tenant::updateOrCreate(['subdomain' => 'greenfield'], [
            'name' => 'Greenfield International School',
            'code' => 'GIS-001',
            'email' => 'contact@greenfield.edu',
            'phone' => '+1234567890',
            'address' => '100 Academic Way, Metro City',
            'status' => 'active',
            'plan_id' => $proPlan->id,
            'settings' => [
                'theme' => 'light',
                'currency' => 'USD',
                'academic_year' => '2026-2027',
                'ai_provider' => 'openai',
            ],
        ]);

        Subscription::updateOrCreate(['tenant_id' => $schoolA->id], [
            'plan_id' => $proPlan->id,
            'status' => 'active',
            'billing_cycle' => 'monthly',
            'starts_at' => Carbon::now()->subDays(15),
            'renews_at' => Carbon::now()->addDays(15),
        ]);

        // 3. Demo School B (Oakridge Academy - Starter Plan)
        $schoolB = Tenant::updateOrCreate(['subdomain' => 'oakridge'], [
            'name' => 'Oakridge Academy',
            'code' => 'OAK-002',
            'email' => 'contact@oakridge.edu',
            'phone' => '+1987654321',
            'address' => '200 Knowledge Blvd, Hill Valley',
            'status' => 'active',
            'plan_id' => $starterPlan->id,
            'settings' => [
                'theme' => 'light',
                'currency' => 'USD',
                'academic_year' => '2026-2027',
                'ai_provider' => 'gemini',
            ],
        ]);

        Subscription::updateOrCreate(['tenant_id' => $schoolB->id], [
            'plan_id' => $starterPlan->id,
            'status' => 'active',
            'billing_cycle' => 'annual',
            'starts_at' => Carbon::now()->subMonths(2),
            'renews_at' => Carbon::now()->addMonths(10),
        ]);

        // 4. Platform Super Admin User (Platform scope, tenant_id is explicitly null)
        User::withoutGlobalScopes()->updateOrCreate(['email' => 'superadmin@schoolos.com'], [
            'name' => 'Platform Super Admin',
            'password' => Hash::make('password123'),
            'role_id' => $superAdminRole->id,
            'tenant_id' => null,
            'status' => 'active',
        ]);

        // 5. School A Users
        User::withoutGlobalScopes()->updateOrCreate(['email' => 'admin@greenfield.edu'], [
            'name' => 'Greenfield Admin',
            'password' => Hash::make('password123'),
            'role_id' => $schoolAdminRole->id,
            'tenant_id' => $schoolA->id,
            'status' => 'active',
        ]);

        User::withoutGlobalScopes()->updateOrCreate(['email' => 'teacher@greenfield.edu'], [
            'name' => 'Sarah Johnson (Science Teacher)',
            'password' => Hash::make('password123'),
            'role_id' => $teacherRole->id,
            'tenant_id' => $schoolA->id,
            'status' => 'active',
        ]);

        User::withoutGlobalScopes()->updateOrCreate(['email' => 'student@greenfield.edu'], [
            'name' => 'Alex Miller',
            'password' => Hash::make('password123'),
            'role_id' => $studentRole->id,
            'tenant_id' => $schoolA->id,
            'status' => 'active',
        ]);

        User::withoutGlobalScopes()->updateOrCreate(['email' => 'parent@greenfield.edu'], [
            'name' => 'Robert Miller',
            'password' => Hash::make('password123'),
            'role_id' => $parentRole->id,
            'tenant_id' => $schoolA->id,
            'status' => 'active',
        ]);

        // 6. School B Users
        User::withoutGlobalScopes()->updateOrCreate(['email' => 'admin@oakridge.edu'], [
            'name' => 'Oakridge Admin',
            'password' => Hash::make('password123'),
            'role_id' => $schoolAdminRole->id,
            'tenant_id' => $schoolB->id,
            'status' => 'active',
        ]);

        User::withoutGlobalScopes()->updateOrCreate(['email' => 'teacher@oakridge.edu'], [
            'name' => 'David Lee (Math Teacher)',
            'password' => Hash::make('password123'),
            'role_id' => $teacherRole->id,
            'tenant_id' => $schoolB->id,
            'status' => 'active',
        ]);

        TenantContext::clear();
    }
}
