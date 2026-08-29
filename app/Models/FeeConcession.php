<?php

namespace App\Models;

use App\Tenancy\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeConcession extends Model
{
    use HasFactory, TenantScoped;

    protected $fillable = [
        'tenant_id',
        'student_id',
        'title',
        'discount_type',
        'discount_value',
        'reason',
        'approved_by_user_id',
        'is_active',
    ];

    protected $casts = [
        'discount_value' => 'float',
        'is_active' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }
}
