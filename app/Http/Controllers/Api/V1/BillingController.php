<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\Plan;
use App\Services\BillingService;
use App\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BillingController extends Controller
{
    public function __construct(
        protected BillingService $billingService
    ) {}

    /**
     * Get all active SaaS subscription plans.
     */
    public function plans(): JsonResponse
    {
        $plans = Plan::where('is_active', true)
            ->orderBy('price_monthly')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $plans,
        ]);
    }

    /**
     * Get the active subscription overview, quota usage, and AI credits for current school.
     */
    public function subscription(Request $request): JsonResponse
    {
        $tenant = TenantContext::get();

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'No active tenant context.',
            ], 404);
        }

        $overview = $this->billingService->getTenantBillingOverview($tenant);

        return response()->json([
            'success' => true,
            'data' => $overview,
        ]);
    }

    /**
     * Subscribe or change plan (School Admin only).
     */
    public function subscribe(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isSchoolAdmin() && !$user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only school administrators can manage subscriptions.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'plan_slug' => 'required|string|exists:plans,slug',
            'billing_cycle' => 'required|string|in:monthly,annual',
            'coupon_code' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $tenant = TenantContext::get();
        $plan = Plan::where('slug', $request->input('plan_slug'))->firstOrFail();

        $subscription = $this->billingService->subscribe(
            tenant: $tenant,
            plan: $plan,
            billingCycle: $request->input('billing_cycle', 'monthly'),
            couponCode: $request->input('coupon_code')
        );

        return response()->json([
            'success' => true,
            'message' => "Successfully subscribed to the {$plan->name} plan.",
            'data' => [
                'subscription_id' => $subscription->id,
                'plan' => $plan->name,
                'status' => $subscription->status,
                'renews_at' => $subscription->renews_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * List invoices for the active tenant.
     */
    public function invoices(Request $request): JsonResponse
    {
        $invoices = Invoice::orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $invoices->items(),
            'meta' => [
                'current_page' => $invoices->currentPage(),
                'total' => $invoices->total(),
            ],
        ]);
    }

    /**
     * Validate a promo / coupon code.
     */
    public function validateCoupon(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
            'plan_slug' => 'required|string|exists:plans,slug',
            'billing_cycle' => 'required|string|in:monthly,annual',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $coupon = Coupon::where('code', strtoupper($request->input('code')))->first();

        if (!$coupon || !$coupon->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired coupon code.',
            ], 404);
        }

        $plan = Plan::where('slug', $request->input('plan_slug'))->firstOrFail();
        $basePrice = $request->input('billing_cycle') === 'annual' ? $plan->price_annual : $plan->price_monthly;
        $discount = $coupon->calculateDiscount($basePrice);

        return response()->json([
            'success' => true,
            'data' => [
                'code' => $coupon->code,
                'description' => $coupon->description,
                'discount_type' => $coupon->discount_type,
                'discount_value' => $coupon->discount_value,
                'discount_amount' => $discount,
                'final_price' => max(0, round($basePrice - $discount, 2)),
            ],
        ]);
    }

    /**
     * Platform-wide commercial metrics (Super Admin only).
     */
    public function platformMetrics(Request $request): JsonResponse
    {
        if (!$request->user()->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Super Admin access required.',
            ], 403);
        }

        $metrics = $this->billingService->getPlatformBillingMetrics();

        return response()->json([
            'success' => true,
            'data' => $metrics,
        ]);
    }
}
