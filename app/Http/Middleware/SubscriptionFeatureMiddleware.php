<?php

namespace App\Http\Middleware;

use App\Models\Plan;
use App\Models\Subscription;
use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionFeatureMiddleware
{
    /**
     * Handle an incoming request and ensure current tenant plan has the required feature.
     */
    public function handle(Request $request, Closure $next, string $featureKey): Response
    {
        $user = $request->user();

        // Platform Super Admin bypasses plan restrictions
        if ($user && $user->isSuperAdmin()) {
            return $next($request);
        }

        $tenant = TenantContext::get();

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant context required.',
            ], 400);
        }

        $subscription = Subscription::with('plan')
            ->where('tenant_id', $tenant->id)
            ->whereIn('status', ['active', 'trialing'])
            ->latest()
            ->first();

        $plan = $subscription?->plan ?? Plan::where('slug', 'starter')->first();

        if (!$plan || (!$plan->hasFeature($featureKey) && !$plan->hasFeature('all_modules'))) {
            return response()->json([
                'success' => false,
                'message' => "The feature '{$featureKey}' is not included in your school's current subscription plan ({$plan?->name}). Please upgrade your plan.",
                'error_code' => 'FEATURE_NOT_IN_PLAN',
                'required_feature' => $featureKey,
                'current_plan' => $plan?->name,
            ], 403);
        }

        return $next($request);
    }
}
