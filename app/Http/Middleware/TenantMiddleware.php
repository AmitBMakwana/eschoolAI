<?php

namespace App\Http\Middleware;

use App\Tenancy\TenantContext;
use App\Tenancy\TenantResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    public function __construct(
        protected TenantResolver $resolver
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, bool $required = true): Response
    {
        $tenant = $this->resolver->resolve($request);

        if ($tenant) {
            if ($tenant->status === 'suspended') {
                return response()->json([
                    'success' => false,
                    'message' => 'This school account has been suspended. Please contact platform support.',
                    'error_code' => 'TENANT_SUSPENDED'
                ], 403);
            }

            TenantContext::set($tenant);
        } elseif ($required && $request->user() && !$request->user()->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant context could not be resolved for this request.',
                'error_code' => 'TENANT_REQUIRED'
            ], 400);
        }

        $response = $next($request);

        return $response;
    }
}
