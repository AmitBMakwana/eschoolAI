<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogService
{
    /**
     * Log a system or tenant event.
     */
    public function log(
        string $event,
        ?Model $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $userId = null,
        ?int $tenantId = null,
        ?Request $request = null
    ): AuditLog {
        $request = $request ?? request();
        $tenantId = $tenantId ?? TenantContext::id() ?? ($auditable && isset($auditable->tenant_id) ? $auditable->tenant_id : null);
        $userId = $userId ?? $request?->user()?->id;

        return AuditLog::create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'event' => $event,
            'auditable_type' => $auditable ? get_class($auditable) : null,
            'auditable_id' => $auditable?->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }
}
