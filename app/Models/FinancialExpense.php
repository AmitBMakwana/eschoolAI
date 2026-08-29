<?php

namespace App\Models;

use App\Tenancy\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialExpense extends Model
{
    use HasFactory, TenantScoped;

    protected $fillable = [
        'tenant_id',
        'title',
        'category',
        'amount',
        'expense_date',
        'vendor',
        'payment_method',
        'receipt_url',
        'recorded_by_user_id',
    ];

    protected $casts = [
        'amount' => 'float',
        'expense_date' => 'date',
    ];

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }
}
