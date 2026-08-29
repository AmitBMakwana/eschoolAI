<?php

namespace App\Services;

use App\Models\FeeConcession;
use App\Models\FeePayment;
use App\Models\FeeStructure;
use App\Models\FinancialExpense;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentFeeInvoice;
use App\Tenancy\TenantContext;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FeeService
{
    /**
     * Generate fee invoices for all enrolled students in a class.
     */
    public function generateClassInvoices(int $classId, string $title, string $dueDate): Collection
    {
        $schoolClass = SchoolClass::findOrFail($classId);
        $structures = FeeStructure::with('feeHead')->where('class_id', $classId)->get();

        if ($structures->isEmpty()) {
            throw new \InvalidArgumentException("No fee structures configured for {$schoolClass->name}.");
        }

        $subtotal = $structures->sum('amount');
        $lineItems = $structures->map(function ($str) {
            return [
                'fee_head' => $str->feeHead?->name ?? 'Fee',
                'amount' => $str->amount,
            ];
        })->toArray();

        $students = Student::where('class_id', $classId)->where('status', 'active')->get();
        $generated = collect();

        DB::transaction(function () use ($students, $title, $dueDate, $subtotal, $lineItems, &$generated) {
            foreach ($students as $student) {
                // Check if student has active scholarship/concession
                $concession = FeeConcession::where('student_id', $student->id)->where('is_active', true)->first();
                $discountAmount = 0.0;

                if ($concession) {
                    if ($concession->discount_type === 'percentage') {
                        $discountAmount = round(($subtotal * $concession->discount_value) / 100, 2);
                    } else {
                        $discountAmount = min($subtotal, $concession->discount_value);
                    }
                }

                $totalAmount = max(0.0, $subtotal - $discountAmount);
                $invoiceNum = 'INV-' . strtoupper(Str::random(4)) . '-' . rand(1000, 9999);

                $invoice = StudentFeeInvoice::create([
                    'student_id' => $student->id,
                    'invoice_number' => $invoiceNum,
                    'title' => $title,
                    'subtotal' => $subtotal,
                    'concession_amount' => $discountAmount,
                    'fine_amount' => 0.0,
                    'total_amount' => $totalAmount,
                    'paid_amount' => 0.0,
                    'balance_due' => $totalAmount,
                    'due_date' => $dueDate,
                    'status' => 'unpaid',
                    'line_items' => $lineItems,
                ]);

                $generated->push($invoice);
            }
        });

        return $generated;
    }

    /**
     * Process fee payment and issue receipt.
     */
    public function recordPayment(StudentFeeInvoice $invoice, float $amount, string $method, ?string $ref, ?string $notes, int $receivedByUserId): FeePayment
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException("Payment amount must be greater than zero.");
        }

        if ($amount > $invoice->balance_due) {
            throw new \InvalidArgumentException("Payment amount cannot exceed balance due of ${$invoice->balance_due}.");
        }

        return DB::transaction(function () use ($invoice, $amount, $method, $ref, $notes, $receivedByUserId) {
            $newPaid = $invoice->paid_amount + $amount;
            $newBalance = max(0.0, $invoice->total_amount - $newPaid);
            $newStatus = $newBalance <= 0 ? 'paid' : 'partially_paid';

            $invoice->update([
                'paid_amount' => $newPaid,
                'balance_due' => $newBalance,
                'status' => $newStatus,
            ]);

            $receiptNum = 'REC-' . strtoupper(Str::random(4)) . '-' . rand(1000, 9999);

            return FeePayment::create([
                'student_fee_invoice_id' => $invoice->id,
                'student_id' => $invoice->student_id,
                'receipt_number' => $receiptNum,
                'amount_paid' => $amount,
                'payment_method' => $method,
                'transaction_reference' => $ref,
                'notes' => $notes,
                'paid_at' => now(),
                'received_by_user_id' => $receivedByUserId,
                'status' => 'successful',
            ]);
        });
    }

    /**
     * Get aggregate financial analytics and metrics.
     */
    public function getAnalytics(): array
    {
        $totalInvoiced = (float) StudentFeeInvoice::sum('total_amount');
        $totalCollected = (float) StudentFeeInvoice::sum('paid_amount');
        $totalOutstanding = (float) StudentFeeInvoice::sum('balance_due');
        $totalExpenses = (float) FinancialExpense::sum('amount');
        $netSurplus = $totalCollected - $totalExpenses;

        $defaultersCount = StudentFeeInvoice::whereIn('status', ['unpaid', 'partially_paid', 'overdue'])
            ->where('due_date', '<', now())
            ->distinct('student_id')
            ->count('student_id');

        return [
            'total_invoiced' => $totalInvoiced,
            'total_collected' => $totalCollected,
            'total_outstanding' => $totalOutstanding,
            'total_expenses' => $totalExpenses,
            'net_surplus' => $netSurplus,
            'collection_rate' => $totalInvoiced > 0 ? round(($totalCollected / $totalInvoiced) * 100, 1) : 0.0,
            'defaulters_count' => $defaultersCount,
        ];
    }
}
