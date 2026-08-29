<?php

namespace App\Models;

use App\Tenancy\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiStudentInsight extends Model
{
    use HasFactory, TenantScoped;

    protected $fillable = [
        'tenant_id',
        'student_id',
        'subject_id',
        'academic_year',
        'overall_trend',
        'gpa_trajectory',
        'attendance_correlation',
        'strength_topics',
        'struggling_topics',
        'personalized_recommendations',
        'generated_at',
    ];

    protected $casts = [
        'gpa_trajectory' => 'array',
        'attendance_correlation' => 'array',
        'strength_topics' => 'array',
        'struggling_topics' => 'array',
        'personalized_recommendations' => 'array',
        'generated_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
