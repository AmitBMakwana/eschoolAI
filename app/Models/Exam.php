<?php

namespace App\Models;

use App\Tenancy\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    use HasFactory, TenantScoped;

    protected $fillable = [
        'tenant_id',
        'exam_term_id',
        'class_id',
        'subject_id',
        'title',
        'exam_date',
        'start_time',
        'end_time',
        'total_marks',
        'passing_marks',
        'room_number',
    ];

    protected $casts = [
        'exam_date' => 'date',
        'total_marks' => 'float',
        'passing_marks' => 'float',
    ];

    public function term(): BelongsTo
    {
        return $this->belongsTo(ExamTerm::class, 'exam_term_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function marks(): HasMany
    {
        return $this->hasMany(ExamMark::class, 'exam_id');
    }
}
