<?php

namespace App\Models;

use App\Tenancy\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeworkSubmission extends Model
{
    use HasFactory, TenantScoped;

    protected $fillable = [
        'tenant_id',
        'homework_id',
        'student_id',
        'submission_text',
        'attachment_url',
        'submitted_at',
        'status',
        'teacher_feedback',
        'marks',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'marks' => 'float',
    ];

    public function homework(): BelongsTo
    {
        return $this->belongsTo(Homework::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
