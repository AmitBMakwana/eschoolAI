<?php

namespace App\Models;

use App\Tenancy\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradingScale extends Model
{
    use HasFactory, TenantScoped;

    protected $fillable = [
        'tenant_id',
        'grade',
        'min_percentage',
        'max_percentage',
        'grade_point',
        'description',
    ];

    protected $casts = [
        'min_percentage' => 'float',
        'max_percentage' => 'float',
        'grade_point' => 'float',
    ];

    /**
     * Find grade for a given percentage.
     */
    public static function gradeForPercentage(float $percentage): ?self
    {
        return static::where('min_percentage', '<=', $percentage)
            ->where('max_percentage', '>=', $percentage)
            ->orderBy('min_percentage', 'desc')
            ->first();
    }
}
