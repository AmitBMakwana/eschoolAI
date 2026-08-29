<?php

namespace App\Models;

use App\Tenancy\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamPaper extends Model
{
    use HasFactory, TenantScoped;

    protected $fillable = [
        'tenant_id',
        'subject_id',
        'class_id',
        'title',
        'instructions',
        'total_marks',
        'duration_minutes',
        'sections',
        'created_by_user_id',
    ];

    protected $casts = [
        'sections' => 'array',
        'total_marks' => 'float',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
