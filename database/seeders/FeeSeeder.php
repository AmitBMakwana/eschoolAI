<?php

namespace Database\Seeders;

use App\Models\FeeConcession;
use App\Models\FeeHead;
use App\Models\FeeStructure;
use App\Models\FinancialExpense;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Database\Seeder;

class FeeSeeder extends Seeder
{
    public function run(): void
    {
        $schoolA = Tenant::where('subdomain', 'greenfield')->firstOrFail();
        TenantContext::set($schoolA);

        $adminUser = User::withoutGlobalScopes()->where('email', 'admin@greenfield.edu')->firstOrFail();
        $class8 = SchoolClass::where('name', 'Class 8')->firstOrFail();
        $studentAlex = Student::where('admission_number', 'ADM-2026-084')->firstOrFail();

        // 1. Fee Heads
        $tuition = FeeHead::updateOrCreate(['name' => 'Tuition Fee', 'tenant_id' => $schoolA->id], [
            'code' => 'TUIT',
            'description' => 'Regular academic instructional fee',
            'is_mandatory' => true,
        ]);

        $lab = FeeHead::updateOrCreate(['name' => 'Computer & Science Lab Fee', 'tenant_id' => $schoolA->id], [
            'code' => 'LAB',
            'description' => 'Laboratory consumables and equipment maintenance',
            'is_mandatory' => true,
        ]);

        $library = FeeHead::updateOrCreate(['name' => 'Digital Library & LMS Access', 'tenant_id' => $schoolA->id], [
            'code' => 'LIB',
            'description' => 'Online educational resources and textbooks',
            'is_mandatory' => true,
        ]);

        // 2. Fee Structures for Class 8
        FeeStructure::updateOrCreate(['fee_head_id' => $tuition->id, 'class_id' => $class8->id, 'tenant_id' => $schoolA->id], [
            'academic_year' => '2026-2027',
            'amount' => 450.00,
            'frequency' => 'monthly',
            'due_date' => '2026-10-10',
        ]);

        FeeStructure::updateOrCreate(['fee_head_id' => $lab->id, 'class_id' => $class8->id, 'tenant_id' => $schoolA->id], [
            'academic_year' => '2026-2027',
            'amount' => 50.00,
            'frequency' => 'monthly',
            'due_date' => '2026-10-10',
        ]);

        FeeStructure::updateOrCreate(['fee_head_id' => $library->id, 'class_id' => $class8->id, 'tenant_id' => $schoolA->id], [
            'academic_year' => '2026-2027',
            'amount' => 25.00,
            'frequency' => 'monthly',
            'due_date' => '2026-10-10',
        ]);

        // 3. Sample Concession for Alex Miller (10% Academic Excellence scholarship)
        FeeConcession::updateOrCreate(['student_id' => $studentAlex->id, 'tenant_id' => $schoolA->id], [
            'title' => 'Academic Merit Scholarship (10%)',
            'discount_type' => 'percentage',
            'discount_value' => 10.00,
            'reason' => 'Top 5% score in baseline assessment',
            'approved_by_user_id' => $adminUser->id,
            'is_active' => true,
        ]);

        // 4. Sample Operational Expenses
        FinancialExpense::updateOrCreate(['title' => 'Science Lab Glassware & Reagents', 'tenant_id' => $schoolA->id], [
            'category' => 'Laboratory',
            'amount' => 840.00,
            'expense_date' => '2026-08-15',
            'vendor' => 'Apex Scientific Supplies',
            'payment_method' => 'bank_transfer',
            'recorded_by_user_id' => $adminUser->id,
        ]);

        FinancialExpense::updateOrCreate(['title' => 'High-Speed Campus Fiber Internet', 'tenant_id' => $schoolA->id], [
            'category' => 'Utilities',
            'amount' => 320.00,
            'expense_date' => '2026-08-20',
            'vendor' => 'Metro Telecom',
            'payment_method' => 'card',
            'recorded_by_user_id' => $adminUser->id,
        ]);

        TenantContext::clear();
    }
}
