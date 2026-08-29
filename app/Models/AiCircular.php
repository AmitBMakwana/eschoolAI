<?php

namespace App\Models;

use App\Tenancy\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiCircular extends Model
{
    use HasFactory, TenantScoped;

    protected $fillable = [
        'tenant_id',
        'created_by_user_id',
        'notice_id',
        'title',
        'audience',
        'event_topic',
        'tone',
        'generated_body',
        'final_content',
        'is_dispatched',
        'dispatched_at',
    ];

    protected $casts = [
        'is_dispatched' => 'boolean',
        'dispatched_at' => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function notice(): BelongsTo
    {
        return $this->belongsTo(Notice::class);
    }
}
