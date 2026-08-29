<?php

namespace App\Models;

use App\Tenancy\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiAnswerSheetEvaluation extends Model
{
    use HasFactory, TenantScoped;

    protected $fillable = [
        'tenant_id',
        'created_by_user_id',
        'exam_id',
        'student_id',
        'submission_url',
        'extracted_text',
        'marking_rubric',
        'question_evaluations',
        'total_score_awarded',
        'total_possible_score',
        'strengths',
        'areas_for_improvement',
        'teacher_reviewed',
        'final_score',
        'status',
    ];

    protected $casts = [
        'marking_rubric' => 'array',
        'question_evaluations' => 'array',
        'total_score_awarded' => 'float',
        'total_possible_score' => 'float',
        'final_score' => 'float',
        'strengths' => 'array',
        'areas_for_improvement' => 'array',
        'teacher_reviewed' => 'boolean',
    ];

    public function examiner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
