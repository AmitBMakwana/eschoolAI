<?php

namespace App\Services;

use App\Models\AiUsageLog;
use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Carbon\Carbon;
use Illuminate\Support\Str;

class BillingService
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Get the active subscription overview and usage limits for a tenant.
     */
    public function getTenantBillingOverview(?Tenant $tenant = null): array
    {
        $tenant = $tenant ?? TenantContext::get();

        if (!$tenant) {
            throw new \RuntimeException("No active tenant context found.");
        }

        $subscription = Subscription::with('plan')
            ->where('tenant_id', $tenant->id)
            ->latest()
            ->first();

        // Count current users / students
        $currentUsersCount = User::where('tenant_id', $tenant->id)->count();

        // Calculate AI credits used this billing month
        $startOfMonth = Carbon::now()->startOfMonth();
        $aiGenerationsCount = AiUsageLog::where('tenant_id', $tenant->id)
            ->where('created_at', '>=', $startOfMonth)
            ->count();

        $aiTokensUsed = AiUsageLog::where('tenant_id', $tenant->id)
            ->where('created_at', '>=', $startOfMonth)
            ->sum('input_tokens') + AiUsageLog::where('tenant_id', $tenant->id)
            ->where('created_at', '>=', $startOfMonth)
            ->sum('output_tokens');

        $aiCostMonth = AiUsageLog::where('tenant_id', $tenant->id)
            ->where('created_at', '>=', $startOfMonth)
            ->sum('computed_cost');

        $plan = $subscription?->plan ?? Plan::where('slug', 'starter')->first();
        $aiQuota = $subscription?->ai_credit_quota ?? $plan?->ai_credit_quota ?? 1000;
        $studentLimit = $subscription?->student_limit ?? $plan?->student_limit ?? 500;
        $storageLimitGb = $plan?->storage_limit_gb ?? 10;

        return [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'subdomain' => $tenant->subdomain,
            ],
            'subscription' => $subscription ? [
                'id' => $subscription->id,
                'status' => $subscription->status,
                'billing_cycle' => $subscription->billing_cycle,
                'trial_ends_at' => $subscription->trial_ends_at?->toIso8601String(),
                'renews_at' => $subscription->renews_at?->toIso8601String(),
                'plan' => [
                    'id' => $subscription->plan->id,
                    'name' => $subscription->plan->name,
                    'slug' => $subscription->plan->slug,
                    'price' => $subscription->billing_cycle === 'annual'
                        ? $subscription->plan->price_annual
                        : $subscription->plan->price_monthly,
                    'features' => $subscription->plan->features,
                ],
            ] : null,
            'usage' => [
                'students' => [
                    'current' => $currentUsersCount,
                    'limit' => $studentLimit,
                    'percentage' => round(($currentUsersCount / max(1, $studentLimit)) * 100, 1),
                ],
                'ai_credits' => [
                    'used' => $aiGenerationsCount,
                    'limit' => $aiQuota,
                    'remaining' => max(0, $aiQuota - $aiGenerationsCount),
                    'percentage' => round(($aiGenerationsCount / max(1, $aiQuota)) * 100, 1),
                    'tokens_consumed' => (int) $aiTokensUsed,
                    'estimated_cost_usd' => round((float) $aiCostMonth, 4),
                ],
                'storage' => [
                    'used_gb' => 1.2,
                    'limit_gb' => $storageLimitGb,
                    'percentage' => round((1.2 / max(1, $storageLimitGb)) * 100, 1),
                ],
            ],
        ];
    }

    /**
     * Subscribe or upgrade a tenant to a plan.
     */
    public function subscribe(
        Tenant $tenant,
        Plan $plan,
        string $billingCycle = 'monthly',
        ?string $couponCode = null
    ): Subscription {
        $price = $billingCycle === 'annual' ? $plan->price_annual : $plan->price_monthly;
        $discountAmount = 0;

        if ($couponCode) {
            $coupon = Coupon::where('code', strtoupper($couponCode))->first();
            if ($coupon && $coupon->isValid()) {
                $discountAmount = $coupon->calculateDiscount($price);
                $coupon->increment('uses_count');
            }
        }

        $taxAmount = round(($price - $discountAmount) * 0.10, 2); // 10% standard tax
        $totalAmount = max(0, round($price - $discountAmount + $taxAmount, 2));

        // Create or update subscription
        $startsAt = Carbon::now();
        $renewsAt = $billingCycle === 'annual' ? $startsAt->copy()->addYear() : $startsAt->copy()->addMonth();

        $subscription = Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'billing_cycle' => $billingCycle,
            'starts_at' => $startsAt,
            'renews_at' => $renewsAt,
        ]);

        $tenant->update(['plan_id' => $plan->id, 'status' => 'active']);

        $lineItems = [
            [
                'description' => "Subscription to {$plan->name} ({$billingCycle})",
                'amount' => $price,
            ],
        ];

        if ($discountAmount > 0) {
            $lineItems[] = [
                'description' => "Coupon Discount ({$couponCode})",
                'amount' => -$discountAmount,
            ];
        }

        // Generate paid invoice
        Invoice::create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'invoice_number' => 'INV-' . strtoupper(Str::random(4)) . '-' . rand(1000, 9999),
            'subtotal' => $price,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'currency' => 'USD',
            'status' => 'paid',
            'paid_at' => now(),
            'due_date' => now(),
            'line_items' => $lineItems,
        ]);

        $this->auditLogService->log(
            event: 'billing.subscribed',
            auditable: $subscription,
            newValues: ['plan' => $plan->slug, 'cycle' => $billingCycle, 'total' => $totalAmount],
            tenantId: $tenant->id
        );

        return $subscription;
    }

    /**
     * Check if active tenant has remaining AI generation credits.
     */
    public function hasAiCreditsAvailable(?Tenant $tenant = null): bool
    {
        $tenant = $tenant ?? TenantContext::get();
        if (!$tenant) return false;

        $overview = $this->getTenantBillingOverview($tenant);
        return $overview['usage']['ai_credits']['remaining'] > 0;
    }

    /**
     * Super Admin platform-wide commercial overview.
     */
    public function getPlatformBillingMetrics(): array
    {
        $activeSchools = Tenant::where('status', 'active')->count();
        $trialSchools = Tenant::where('status', 'trial')->count();

        // Calculate Monthly Recurring Revenue (MRR)
        $mrr = 0;
        $activeSubs = Subscription::with('plan')->where('status', 'active')->get();
        foreach ($activeSubs as $sub) {
            if ($sub->billing_cycle === 'annual') {
                $mrr += ($sub->plan->price_annual / 12);
            } else {
                $mrr += $sub->plan->price_monthly;
            }
        }

        $totalAiTokens = AiUsageLog::sum('input_tokens') + AiUsageLog::sum('output_tokens');
        $totalAiCost = (float) AiUsageLog::sum('computed_cost');

        return [
            'active_schools' => $activeSchools,
            'trial_schools' => $trialSchools,
            'mrr_usd' => round($mrr, 2),
            'annual_run_rate_usd' => round($mrr * 12, 2),
            'total_ai_tokens_consumed' => (int) $totalAiTokens,
            'total_ai_cost_usd' => round($totalAiCost, 4),
            'total_invoiced_usd' => (float) Invoice::where('status', 'paid')->sum('total_amount'),
        ];
    }
}
