<?php

namespace App\Models;

use App\Tenancy\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentFeeInvoice extends Model
{
    use HasFactory, TenantScoped;

    protected $fillable = [
        'tenant_id',
        'student_id',
        'invoice_number',
        'title',
        'subtotal',
        'concession_amount',
        'fine_amount',
        'total_amount',
        'paid_amount',
        'balance_due',
        'due_date',
        'status',
        'line_items',
    ];

    protected $casts = [
        'subtotal' => 'float',
        'concession_amount' => 'float',
        'fine_amount' => 'float',
        'total_amount' => 'float',
        'paid_amount' => 'float',
        'balance_due' => 'float',
        'due_date' => 'date',
        'line_items' => 'array',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(FeePayment::class);
    }
}
