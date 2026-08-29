<?php

namespace Tests\Feature;

use App\Models\FeeHead;
use App\Models\FeeStructure;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentFeeInvoice;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $schoolA;
    protected User $adminSchoolA;
    protected User $studentSchoolA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->schoolA = Tenant::where('subdomain', 'greenfield')->firstOrFail();
        $this->adminSchoolA = User::where('email', 'admin@greenfield.edu')->firstOrFail();
        $this->studentSchoolA = User::where('email', 'student@greenfield.edu')->firstOrFail();
    }

    public function test_can_list_and_create_fee_heads_and_structures(): void
    {
        // 1. List fee heads
        $listRes = $this->actingAs($this->adminSchoolA)
            ->getJson('/api/v1/finance/fee-heads');

        $listRes->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertNotEmpty($listRes->json('data'));

        // 2. Create new fee head (e.g. Sports & Physical Education Fee)
        $createHeadRes = $this->actingAs($this->adminSchoolA)
            ->postJson('/api/v1/finance/fee-heads', [
                'name' => 'Sports & Athletic Facility Fee',
                'code' => 'SPORT',
                'description' => 'Swimming pool and sports field access',
                'is_mandatory' => false,
            ]);

        $createHeadRes->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Sports & Athletic Facility Fee',
                ]
            ]);

        $headId = $createHeadRes->json('data.id');
        $class8 = SchoolClass::where('name', 'Class 8')->firstOrFail();

        // 3. Allocate structure to Class 8
        $strRes = $this->actingAs($this->adminSchoolA)
            ->postJson('/api/v1/finance/structures', [
                'fee_head_id' => $headId,
                'class_id' => $class8->id,
                'amount' => 30.00,
                'frequency' => 'monthly',
            ]);

        $strRes->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'amount' => 30.00,
                ]
            ]);
    }

    public function test_batch_invoice_generation_with_concessions(): void
    {
        $class8 = SchoolClass::where('name', 'Class 8')->firstOrFail();

        // Generate batch invoices for Class 8 ($450 Tuition + $50 Lab + $25 Library = $525 subtotal)
        $batchRes = $this->actingAs($this->adminSchoolA)
            ->postJson('/api/v1/finance/invoices/generate-batch', [
                'class_id' => $class8->id,
                'title' => 'Class 8 Term Fee - October 2026',
                'due_date' => '2026-10-15',
            ]);

        $batchRes->assertStatus(201)
            ->assertJson(['success' => true]);

        $this->assertGreaterThanOrEqual(4, $batchRes->json('data'));

        // Verify Alex Miller received 10% scholarship ($525 - $52.50 = $472.50)
        $alexStudent = Student::where('user_id', $this->studentSchoolA->id)->firstOrFail();
        $alexInvoice = StudentFeeInvoice::where('student_id', $alexStudent->id)->firstOrFail();

        $this->assertEquals(525.00, $alexInvoice->subtotal);
        $this->assertEquals(52.50, $alexInvoice->concession_amount);
        $this->assertEquals(472.50, $alexInvoice->total_amount);
        $this->assertEquals('unpaid', $alexInvoice->status);
    }

    public function test_fee_collection_partial_and_full_payment(): void
    {
        $class8 = SchoolClass::where('name', 'Class 8')->firstOrFail();

        // 1. Generate batch invoice
        $this->actingAs($this->adminSchoolA)
            ->postJson('/api/v1/finance/invoices/generate-batch', [
                'class_id' => $class8->id,
                'title' => 'Class 8 Term Fee - October 2026',
                'due_date' => '2026-10-15',
            ]);

        $alexStudent = Student::where('user_id', $this->studentSchoolA->id)->firstOrFail();
        $alexInvoice = StudentFeeInvoice::where('student_id', $alexStudent->id)->firstOrFail();

        // 2. Collect partial payment of $200
        $partialPayRes = $this->actingAs($this->adminSchoolA)
            ->postJson('/api/v1/finance/payments/collect', [
                'student_fee_invoice_id' => $alexInvoice->id,
                'amount_paid' => 200.00,
                'payment_method' => 'card',
                'transaction_reference' => 'TXN-CARD-9081',
                'notes' => 'First installment via parent debit card',
            ]);

        $partialPayRes->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'amount_paid' => 200.00,
                    'status' => 'successful',
                ]
            ]);

        $this->assertNotEmpty($partialPayRes->json('data.receipt_number'));

        // Refresh invoice
        $alexInvoice->refresh();
        $this->assertEquals(200.00, $alexInvoice->paid_amount);
        $this->assertEquals(272.50, $alexInvoice->balance_due);
        $this->assertEquals('partially_paid', $alexInvoice->status);

        // 3. Collect remaining balance of $272.50
        $finalPayRes = $this->actingAs($this->adminSchoolA)
            ->postJson('/api/v1/finance/payments/collect', [
                'student_fee_invoice_id' => $alexInvoice->id,
                'amount_paid' => 272.50,
                'payment_method' => 'bank_transfer',
                'transaction_reference' => 'TXN-WIRE-4412',
            ]);

        $finalPayRes->assertStatus(201);

        $alexInvoice->refresh();
        $this->assertEquals(472.50, $alexInvoice->paid_amount);
        $this->assertEquals(0.00, $alexInvoice->balance_due);
        $this->assertEquals('paid', $alexInvoice->status);
    }

    public function test_financial_analytics_and_student_invoice_view(): void
    {
        $class8 = SchoolClass::where('name', 'Class 8')->firstOrFail();

        // 1. Generate batch invoice
        $this->actingAs($this->adminSchoolA)
            ->postJson('/api/v1/finance/invoices/generate-batch', [
                'class_id' => $class8->id,
                'title' => 'Class 8 Term Fee - October 2026',
                'due_date' => '2026-10-15',
            ]);

        // 2. Query Analytics
        $analyticsRes = $this->actingAs($this->adminSchoolA)
            ->getJson('/api/v1/finance/analytics');

        $analyticsRes->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertGreaterThan(0, $analyticsRes->json('data.total_invoiced'));
        $this->assertGreaterThan(0, $analyticsRes->json('data.total_expenses'));

        // 3. Student views invoices
        $studentInvoicesRes = $this->actingAs($this->studentSchoolA)
            ->getJson('/api/v1/finance/invoices');

        $studentInvoicesRes->assertStatus(200);
        $this->assertCount(1, $studentInvoicesRes->json('data'));
        $this->assertEquals('Class 8 Term Fee - October 2026', $studentInvoicesRes->json('data.0.title'));
    }
}
