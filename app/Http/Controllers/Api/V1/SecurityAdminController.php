<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Tenant;
use App\Services\AuditLogService;
use App\Services\TenantSecurityService;
use App\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SecurityAdminController extends Controller
{
    public function __construct(
        protected TenantSecurityService $securityService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Searchable and filterable security audit trail.
     */
    public function auditTrail(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = AuditLog::with('user:id,name,email');

        if ($user->isSuperAdmin() && $request->has('tenant_id')) {
            $query->withoutGlobalScopes()->where('tenant_id', $request->input('tenant_id'));
        }

        if ($request->has('event')) {
            $query->where('event', 'like', '%' . $request->input('event') . '%');
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(25);

        return response()->json([
            'success' => true,
            'data' => $logs->items(),
            'meta' => [
                'current_page' => $logs->currentPage(),
                'total' => $logs->total(),
            ],
        ]);
    }

    /**
     * Quarantine and archive a school tenant (Super Admin only).
     */
    public function archiveTenant(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Super Admin only.'], 403);
        }

        $tenant = Tenant::findOrFail($id);
        $archived = $this->securityService->archiveTenant($tenant, $user->id);

        $this->auditLogService->log(
            event: 'platform.tenant_quarantined',
            auditable: $archived,
            newValues: ['tenant_name' => $archived->name, 'status' => 'suspended'],
            request: $request
        );

        return response()->json([
            'success' => true,
            'message' => "School tenant '{$archived->name}' has been archived and all sessions revoked.",
            'data' => $archived,
        ]);
    }
}
