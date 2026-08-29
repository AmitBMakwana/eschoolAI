<?php

namespace App\Models;

use App\Tenancy\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RagDocument extends Model
{
    use HasFactory, TenantScoped;

    protected $fillable = [
        'tenant_id',
        'uploaded_by_user_id',
        'class_id',
        'subject_id',
        'title',
        'document_type',
        'file_path',
        'file_size_bytes',
        'mime_type',
        'total_chunks',
        'status',
    ];

    protected $casts = [
        'file_size_bytes' => 'integer',
        'total_chunks' => 'integer',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function chunks(): HasMany
    {
        return $this->hasMany(RagChunk::class);
    }
}
