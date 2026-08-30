<?php

namespace App\Models;

use App\Tenancy\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupLog extends Model
{
    use HasFactory, TenantScoped;

    protected $fillable = [
        'tenant_id',
        'backup_type',
        'file_path',
        'file_size_bytes',
        'checksum_sha256',
        'status',
    ];

    protected $casts = [
        'file_size_bytes' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
