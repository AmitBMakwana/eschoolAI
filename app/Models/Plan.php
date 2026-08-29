<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_monthly',
        'price_annual',
        'student_limit',
        'storage_limit_gb',
        'ai_credit_quota',
        'features',
        'is_active',
        'is_popular',
    ];

    protected $casts = [
        'price_monthly' => 'float',
        'price_annual' => 'float',
        'student_limit' => 'integer',
        'storage_limit_gb' => 'integer',
        'ai_credit_quota' => 'integer',
        'features' => 'array',
        'is_active' => 'boolean',
        'is_popular' => 'boolean',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function hasFeature(string $featureKey): bool
    {
        return in_array($featureKey, $this->features ?? [], true);
    }
}
