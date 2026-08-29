<?php

namespace App\Models;

use App\Tenancy\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeStructure extends Model
{
    use HasFactory, TenantScoped;

    protected $fillable = [
        'tenant_id',
        'fee_head_id',
        'class_id',
        'academic_year',
        'amount',
        'frequency',
        'due_date',
    ];

    protected $casts = [
        'amount' => 'float',
        'due_date' => 'date',
    ];

    public function feeHead(): BelongsTo
    {
        return $this->belongsTo(FeeHead::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
}
