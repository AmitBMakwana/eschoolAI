<?php

namespace App\Models;

use App\Tenancy\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeePayment extends Model
{
    use HasFactory, TenantScoped;

    protected $fillable = [
        'tenant_id',
        'student_fee_invoice_id',
        'student_id',
        'receipt_number',
        'amount_paid',
        'payment_method',
        'transaction_reference',
        'paid_at',
        'received_by_user_id',
        'notes',
        'status',
    ];

    protected $casts = [
        'amount_paid' => 'float',
        'paid_at' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(StudentFeeInvoice::class, 'student_fee_invoice_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }
}
