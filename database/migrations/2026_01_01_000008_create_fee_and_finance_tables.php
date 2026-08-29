<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Fee Heads (e.g. Tuition Fee, Lab Fee, Transport Fee)
        Schema::create('fee_heads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name'); // "Tuition Fee"
            $table->string('code', 20)->nullable(); // "TUIT"
            $table->text('description')->nullable();
            $table->boolean('is_mandatory')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'name']);
        });

        // 2. Fee Structures (Amounts allocated per class)
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('fee_head_id')->constrained('fee_heads')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->string('academic_year', 20)->default('2026-2027');
            $table->decimal('amount', 8, 2);
            $table->enum('frequency', ['monthly', 'quarterly', 'term_wise', 'annual', 'one_time'])->default('monthly');
            $table->date('due_date')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'class_id', 'academic_year']);
        });

        // 3. Fee Concessions / Scholarships
        Schema::create('fee_concessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('title'); // "Merit Scholarship 20%", "Sibling Discount"
            $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('discount_value', 8, 2); // 20.00 or $50.00
            $table->text('reason')->nullable();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'student_id', 'is_active']);
        });

        // 4. Student Fee Invoices
        Schema::create('student_fee_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('invoice_number')->unique();
            $table->string('title'); // "Class 8 Tuition Fee - October 2026"
            $table->decimal('subtotal', 8, 2);
            $table->decimal('concession_amount', 8, 2)->default(0.00);
            $table->decimal('fine_amount', 8, 2)->default(0.00);
            $table->decimal('total_amount', 8, 2);
            $table->decimal('paid_amount', 8, 2)->default(0.00);
            $table->decimal('balance_due', 8, 2);
            $table->date('due_date');
            $table->enum('status', ['unpaid', 'partially_paid', 'paid', 'overdue', 'cancelled'])->default('unpaid');
            $table->json('line_items')->nullable(); // itemized breakdown
            $table->timestamps();

            $table->index(['tenant_id', 'student_id', 'status']);
            $table->index(['tenant_id', 'due_date', 'status']);
        });

        // 5. Fee Payments & Receipts
        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('student_fee_invoice_id')->constrained('student_fee_invoices')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('receipt_number')->unique();
            $table->decimal('amount_paid', 8, 2);
            $table->enum('payment_method', ['cash', 'card', 'bank_transfer', 'cheque', 'online_gateway'])->default('cash');
            $table->string('transaction_reference')->nullable();
            $table->timestamp('paid_at')->useCurrent();
            $table->foreignId('received_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->enum('status', ['successful', 'reversed'])->default('successful');
            $table->timestamps();

            $table->index(['tenant_id', 'student_fee_invoice_id']);
            $table->index(['tenant_id', 'paid_at']);
        });

        // 6. Operational Expenses
        Schema::create('financial_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('title'); // "Lab Consumables & Chemicals"
            $table->string('category'); // "Laboratory", "Utilities", "Maintenance", "Salaries"
            $table->decimal('amount', 8, 2);
            $table->date('expense_date');
            $table->string('vendor')->nullable();
            $table->enum('payment_method', ['cash', 'bank_transfer', 'cheque', 'card'])->default('bank_transfer');
            $table->string('receipt_url')->nullable();
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'category', 'expense_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_expenses');
        Schema::dropIfExists('fee_payments');
        Schema::dropIfExists('student_fee_invoices');
        Schema::dropIfExists('fee_concessions');
        Schema::dropIfExists('fee_structures');
        Schema::dropIfExists('fee_heads');
    }
};
