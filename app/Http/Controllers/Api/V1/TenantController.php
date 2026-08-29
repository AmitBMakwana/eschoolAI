<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Tenant;
use App\Services\AuditLogService;
use App\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Get current tenant information.
     */
    public function current(Request $request): JsonResponse
    {
        $tenant = TenantContext::get();

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'No active tenant context found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $tenant,
        ]);
    }

    /**
     * Update current tenant settings (School Admin only).
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isSchoolAdmin() && !$user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only school administrators can update settings.',
            ], 403);
        }

        $tenant = TenantContext::get();
        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.',
            ], 404);
        }

        $oldSettings = $tenant->settings;
        $newSettings = array_merge($oldSettings ?? [], $request->input('settings', []));

        $tenant->update(['settings' => $newSettings]);

        $this->auditLogService->log(
            event: 'tenant.settings_updated',
            auditable: $tenant,
            oldValues: $oldSettings,
            newValues: $newSettings,
            request: $request
        );

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully.',
            'data' => $tenant,
        ]);
    }

    /**
     * Get tenant audit logs (School Admin only).
     */
    public function auditLogs(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isSchoolAdmin() && !$user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $logs = AuditLog::with('user:id,name,email')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $logs->items(),
            'meta' => [
                'current_page' => $logs->currentPage(),
                'total' => $logs->total(),
                'last_page' => $logs->lastPage(),
            ],
        ]);
    }
}
