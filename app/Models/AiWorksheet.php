<?php

namespace App\Models;

use App\Tenancy\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiWorksheet extends Model
{
    use HasFactory, TenantScoped;

    protected $fillable = [
        'tenant_id',
        'created_by_user_id',
        'class_id',
        'subject_id',
        'title',
        'topic',
        'difficulty',
        'instructions',
        'content',
        'solution_guide',
        'status',
    ];

    protected $casts = [
        'content' => 'array',
        'solution_guide' => 'array',
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
