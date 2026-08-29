<?php

namespace App\Models;

use App\Tenancy\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportCard extends Model
{
    use HasFactory, TenantScoped;

    protected $fillable = [
        'tenant_id',
        'student_id',
        'exam_term_id',
        'total_max_marks',
        'total_marks_obtained',
        'percentage',
        'overall_grade',
        'gpa',
        'class_rank',
        'attendance_percentage',
        'subject_breakdown',
        'teacher_remarks',
        'principal_remarks',
        'status',
        'generated_at',
    ];

    protected $casts = [
        'total_max_marks' => 'float',
        'total_marks_obtained' => 'float',
        'percentage' => 'float',
        'gpa' => 'float',
        'attendance_percentage' => 'float',
        'subject_breakdown' => 'array',
        'generated_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(ExamTerm::class, 'exam_term_id');
    }
}
