<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

class TenantSecurityService
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Quarantine and archive school tenant instance.
     */
    public function archiveTenant(Tenant $tenant, int $requestedByUserId): Tenant
    {
        DB::transaction(function () use ($tenant, $requestedByUserId) {
            $tenant->update([
                'status' => 'suspended',
            ]);

            // Revoke all active API tokens for users of this school
            $userIds = User::withoutGlobalScopes()->where('tenant_id', $tenant->id)->pluck('id');
            PersonalAccessToken::whereIn('tokenable_id', $userIds)
                ->where('tokenable_type', User::class)
                ->delete();
        });

        return $tenant;
    }
}
