<?php

namespace App\Models;

use App\Tenancy\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiGeneratedQuestionPaper extends Model
{
    use HasFactory, TenantScoped;

    protected $fillable = [
        'tenant_id',
        'created_by_user_id',
        'class_id',
        'subject_id',
        'exam_paper_id',
        'title',
        'duration_minutes',
        'total_marks',
        'blueprint',
        'sections',
        'marking_scheme',
        'answer_key',
        'status',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
        'total_marks' => 'float',
        'blueprint' => 'array',
        'sections' => 'array',
        'marking_scheme' => 'array',
        'answer_key' => 'array',
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

    public function examPaper(): BelongsTo
    {
        return $this->belongsTo(ExamPaper::class);
    }
}
