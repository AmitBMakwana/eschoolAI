<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    // Standard platform and school role constants
    public const SUPER_ADMIN = 'super-admin';
    public const SCHOOL_ADMIN = 'school-admin';
    public const PRINCIPAL = 'principal';
    public const TEACHER = 'teacher';
    public const STUDENT = 'student';
    public const PARENT = 'parent';
    public const ACCOUNTANT = 'accountant';
    public const STAFF = 'staff';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'scope',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->slug === self::SUPER_ADMIN) {
            return true;
        }

        return $this->permissions->contains('slug', $permissionSlug);
    }
}
