<?php

namespace App\Models;

use App\Tenancy\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamMark extends Model
{
    use HasFactory, TenantScoped;

    protected $fillable = [
        'tenant_id',
        'exam_id',
        'student_id',
        'marks_obtained',
        'is_absent',
        'grade',
        'grade_point',
        'remarks',
        'entered_by_user_id',
    ];

    protected $casts = [
        'marks_obtained' => 'float',
        'grade_point' => 'float',
        'is_absent' => 'boolean',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by_user_id');
    }
}
