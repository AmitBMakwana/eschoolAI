<?php

namespace App\Tenancy;

use App\Models\Tenant;

class TenantContext
{
    private static ?Tenant $currentTenant = null;
    private static bool $bypassScope = false;

    /**
     * Set the current tenant.
     */
    public static function set(?Tenant $tenant): void
    {
        self::$currentTenant = $tenant;
    }

    /**
     * Get the current active tenant.
     */
    public static function get(): ?Tenant
    {
        return self::$currentTenant;
    }

    /**
     * Get the current active tenant ID or null.
     */
    public static function id(): ?int
    {
        return self::$currentTenant?->id;
    }

    /**
     * Check if a tenant is currently resolved.
     */
    public static function check(): bool
    {
        return self::$currentTenant !== null;
    }

    /**
     * Clear the tenant context.
     */
    public static function clear(): void
    {
        self::$currentTenant = null;
        self::$bypassScope = false;
    }

    /**
     * Temporarily execute callback bypassing tenant scope (for Super Admin tasks).
     */
    public static function withoutScope(callable $callback): mixed
    {
        $previousBypass = self::$bypassScope;
        self::$bypassScope = true;

        try {
            return $callback();
        } finally {
            self::$bypassScope = $previousBypass;
        }
    }

    /**
     * Check if tenant scope is currently bypassed.
     */
    public static function isScopeBypassed(): bool
    {
        return self::$bypassScope;
    }
}
