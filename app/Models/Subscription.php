<?php

namespace App\Models;

use App\Tenancy\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use HasFactory, TenantScoped;

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'status',
        'billing_cycle',
        'trial_ends_at',
        'starts_at',
        'renews_at',
        'ends_at',
        'canceled_at',
        'custom_ai_credits',
        'custom_student_limit',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'starts_at' => 'datetime',
        'renews_at' => 'datetime',
        'ends_at' => 'datetime',
        'canceled_at' => 'datetime',
        'custom_ai_credits' => 'integer',
        'custom_student_limit' => 'integer',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trialing'], true);
    }

    public function getAiCreditQuotaAttribute(): int
    {
        return $this->custom_ai_credits ?? $this->plan?->ai_credit_quota ?? 1000;
    }

    public function getStudentLimitAttribute(): int
    {
        return $this->custom_student_limit ?? $this->plan?->student_limit ?? 500;
    }
}
