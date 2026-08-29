<?php

namespace App\Models;

use App\Tenancy\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsageLog extends Model
{
    use HasFactory, TenantScoped;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'module',
        'provider',
        'model',
        'input_tokens',
        'output_tokens',
        'computed_cost',
        'created_at',
    ];

    protected $casts = [
        'input_tokens' => 'integer',
        'output_tokens' => 'integer',
        'computed_cost' => 'float',
        'created_at' => 'datetime',
    ];

    protected $appends = [
        'total_tokens',
        'cost_usd',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTotalTokensAttribute(): int
    {
        return (int) ($this->input_tokens + $this->output_tokens);
    }

    public function getCostUsdAttribute(): float
    {
        return (float) $this->computed_cost;
    }
}
