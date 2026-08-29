<?php

namespace App\Http\Controllers\Api\V1;

use App\AI\AIManager;
use App\AI\Prompts\PromptRegistry;
use App\Http\Controllers\Controller;
use App\Models\AiUsageLog;
use App\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AIController extends Controller
{
    public function __construct(
        protected AIManager $aiManager
    ) {}

    /**
     * Preview and test a versioned prompt through the swappable AI provider.
     */
    public function promptPreview(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'template_key' => 'required|string',
            'variables' => 'nullable|array',
            'provider' => 'nullable|in:mock,openai,gemini,claude,ollama',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $templateData = PromptRegistry::get(
                $request->input('template_key'),
                $request->input('variables', [])
            );

            $response = $this->aiManager->generateStructured(
                module: $templateData['module'],
                prompt: $templateData['prompt'],
                schema: $templateData['schema'],
                options: [
                    'provider' => $request->input('provider'),
                    'system_prompt' => $templateData['system_prompt'],
                ]
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'rendered_prompt' => $templateData['prompt'],
                    'version' => $templateData['version'],
                    'ai_response' => $response->toArray(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Tenant-scoped AI usage and credit consumption logs.
     */
    public function usageStats(Request $request): JsonResponse
    {
        $tenantId = TenantContext::id();

        $logs = AiUsageLog::with('user:id,name,email')
            ->where('tenant_id', $tenantId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $totalInput = (int) AiUsageLog::where('tenant_id', $tenantId)->sum('input_tokens');
        $totalOutput = (int) AiUsageLog::where('tenant_id', $tenantId)->sum('output_tokens');

        $summary = [
            'total_generations' => AiUsageLog::where('tenant_id', $tenantId)->count(),
            'total_tokens' => $totalInput + $totalOutput,
            'total_cost_usd' => (float) AiUsageLog::where('tenant_id', $tenantId)->sum('computed_cost'),
            'by_module' => AiUsageLog::where('tenant_id', $tenantId)
                ->select('module', DB::raw('count(*) as count'), DB::raw('sum(input_tokens + output_tokens) as tokens'))
                ->groupBy('module')
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => $summary,
                'logs' => $logs->items(),
                'meta' => [
                    'current_page' => $logs->currentPage(),
                    'total' => $logs->total(),
                ],
            ],
        ]);
    }

    /**
     * Super Admin global AI metrics across all schools.
     */
    public function globalMetrics(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $totalInput = (int) AiUsageLog::withoutGlobalScopes()->sum('input_tokens');
        $totalOutput = (int) AiUsageLog::withoutGlobalScopes()->sum('output_tokens');

        $metrics = [
            'total_calls' => AiUsageLog::withoutGlobalScopes()->count(),
            'total_tokens' => $totalInput + $totalOutput,
            'total_cost_usd' => (float) AiUsageLog::withoutGlobalScopes()->sum('computed_cost'),
            'by_provider' => AiUsageLog::withoutGlobalScopes()
                ->select('provider', DB::raw('count(*) as calls'), DB::raw('sum(computed_cost) as cost'))
                ->groupBy('provider')
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $metrics,
        ]);
    }
}
