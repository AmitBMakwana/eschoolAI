<?php

namespace App\Models;

use App\Tenancy\Scopes\TenantScope;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'role_id',
        'name',
        'email',
        'phone',
        'password',
        'avatar_url',
        'status',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Boot model with tenant scoping (except for Super Admin users).
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function ($user) {
            if (empty($user->tenant_id) && TenantContext::check()) {
                $user->tenant_id = TenantContext::id();
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function hasRole(string|array $roles): bool
    {
        if (!$this->role) {
            return false;
        }

        $roles = (array) $roles;
        return in_array($this->role->slug, $roles, true);
    }

    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->role?->hasPermission($permissionSlug) ?? false;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role?->slug === Role::SUPER_ADMIN;
    }

    public function isSchoolAdmin(): bool
    {
        return $this->role?->slug === Role::SCHOOL_ADMIN;
    }

    public function isPrincipal(): bool
    {
        return $this->role?->slug === Role::PRINCIPAL;
    }

    public function isTeacher(): bool
    {
        return $this->role?->slug === Role::TEACHER;
    }

    public function isStudent(): bool
    {
        return $this->role?->slug === Role::STUDENT;
    }

    public function isParent(): bool
    {
        return $this->role?->slug === Role::PARENT;
    }

    public function isAccountant(): bool
    {
        return $this->role?->slug === Role::ACCOUNTANT;
    }

    public function isStaff(): bool
    {
        return $this->role?->slug === Role::STAFF;
    }
}
