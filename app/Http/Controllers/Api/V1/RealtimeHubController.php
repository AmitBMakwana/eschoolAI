<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\AuditLogService;
use App\Services\NotificationHubService;
use App\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RealtimeHubController extends Controller
{
    public function __construct(
        protected NotificationHubService $hubService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Get list of authorized WebSocket channels for current user.
     */
    public function channels(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenantId = TenantContext::id();

        $channels = [
            "private-tenant.{$tenantId}.user.{$user->id}",
            "private-tenant.{$tenantId}.notices",
        ];

        if ($user->isStudent()) {
            $student = Student::where('user_id', $user->id)->first();
            if ($student) {
                $channels[] = "private-tenant.{$tenantId}.class.{$student->class_id}";
                $channels[] = "private-tenant.{$tenantId}.student.{$student->id}";
            }
        } elseif ($user->isTeacher()) {
            $channels[] = "private-tenant.{$tenantId}.teachers";
        } elseif ($user->isAdmin() || $user->isPrincipal()) {
            $channels[] = "private-tenant.{$tenantId}.admin";
        }

        return response()->json([
            'success' => true,
            'data' => [
                'tenant_id' => $tenantId,
                'authorized_channels' => $channels,
            ],
        ]);
    }

    /**
     * Trigger school-wide emergency broadcast.
     */
    public function emergencyAlert(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->isPrincipal() && !$user->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Only administrators can issue emergency alerts.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:200',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $notice = $this->hubService->broadcastEmergencyAlert(
            title: $request->input('title'),
            message: $request->input('message'),
            senderUserId: $user->id
        );

        $this->auditLogService->log(
            event: 'realtime.emergency_alert_dispatched',
            auditable: $notice,
            newValues: ['title' => $notice->title],
            request: $request
        );

        return response()->json([
            'success' => true,
            'message' => 'Emergency alert broadcasted via WebSocket and mobile notifications.',
            'data' => $notice,
        ], 201);
    }
}
