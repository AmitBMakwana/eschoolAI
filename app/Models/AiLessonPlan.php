<?php

namespace App\Models;

use App\Tenancy\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiLessonPlan extends Model
{
    use HasFactory, TenantScoped;

    protected $fillable = [
        'tenant_id',
        'created_by_user_id',
        'class_id',
        'subject_id',
        'topic',
        'duration_minutes',
        'prompt_version',
        'learning_outcomes',
        'prerequisites',
        'activities',
        'teaching_aids',
        'formative_assessments',
        'homework_recommendations',
        'full_plan_text',
        'status',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
        'learning_outcomes' => 'array',
        'prerequisites' => 'array',
        'activities' => 'array',
        'teaching_aids' => 'array',
        'formative_assessments' => 'array',
        'homework_recommendations' => 'array',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
