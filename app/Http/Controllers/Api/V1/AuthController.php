<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Services\AuditLogService;
use App\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Unified Login endpoint: resolves tenant, user, role, and issues Sanctum token.
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'school_code' => 'nullable|string', // optional subdomain or code
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $email = $request->input('email');
        $password = $request->input('password');
        $schoolCode = $request->input('school_code');

        // Look up user bypassing default tenant scope
        $user = TenantContext::withoutScope(function () use ($email, $schoolCode) {
            $query = User::with(['role.permissions', 'tenant'])->where('email', $email);

            if ($schoolCode) {
                $query->whereHas('tenant', function ($q) use ($schoolCode) {
                    $q->where('subdomain', $schoolCode)->orWhere('code', $schoolCode);
                });
            }

            return $query->first();
        });

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.',
            ], 401);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => "Your account is currently {$user->status}. Please contact your administrator.",
            ], 403);
        }

        // If user belongs to a tenant, ensure the tenant is active
        if ($user->tenant) {
            if ($user->tenant->status === 'suspended') {
                return response()->json([
                    'success' => false,
                    'message' => 'The school account is suspended. Please contact platform administration.',
                ], 403);
            }
            TenantContext::set($user->tenant);
        }

        // Update last login timestamp
        $user->update(['last_login_at' => now()]);

        // Issue Sanctum token
        $tokenName = 'auth_token_' . ($request->header('User-Agent') ? substr($request->header('User-Agent'), 0, 30) : 'api');
        $token = $user->createToken($tokenName)->plainTextToken;

        // Log audit event
        $this->auditLogService->log(
            event: 'auth.login',
            auditable: $user,
            userId: $user->id,
            tenantId: $user->tenant_id,
            request: $request
        );

        // Build dashboard redirect hint based on role
        $roleSlug = $user->role?->slug ?? 'student';
        $dashboardRoute = match ($roleSlug) {
            'super-admin' => '/platform/dashboard',
            'school-admin', 'principal' => '/admin/dashboard',
            'teacher' => '/teacher/dashboard',
            'parent' => '/parent/dashboard',
            'accountant' => '/finance/dashboard',
            default => '/student/dashboard',
        };

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'avatar_url' => $user->avatar_url,
                    'role' => $user->role?->slug,
                    'role_name' => $user->role?->name,
                    'permissions' => $user->role?->permissions->pluck('slug') ?? [],
                ],
                'tenant' => $user->tenant ? [
                    'id' => $user->tenant->id,
                    'name' => $user->tenant->name,
                    'subdomain' => $user->tenant->subdomain,
                    'code' => $user->tenant->code,
                    'status' => $user->tenant->status,
                    'settings' => $user->tenant->settings,
                ] : null,
                'redirect_url' => $dashboardRoute,
            ],
        ]);
    }

    /**
     * Get the authenticated user profile and permissions.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['role.permissions', 'tenant']);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'avatar_url' => $user->avatar_url,
                    'role' => $user->role?->slug,
                    'role_name' => $user->role?->name,
                    'permissions' => $user->role?->permissions->pluck('slug') ?? [],
                ],
                'tenant' => $user->tenant ? [
                    'id' => $user->tenant->id,
                    'name' => $user->tenant->name,
                    'subdomain' => $user->tenant->subdomain,
                    'code' => $user->tenant->code,
                    'status' => $user->tenant->status,
                    'settings' => $user->tenant->settings,
                ] : null,
            ],
        ]);
    }

    /**
     * Logout and revoke current token.
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user) {
            $this->auditLogService->log(
                event: 'auth.logout',
                auditable: $user,
                userId: $user->id,
                tenantId: $user->tenant_id,
                request: $request
            );

            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }
}
