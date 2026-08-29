<?php

namespace App\AI;

use App\AI\Contracts\AIProviderInterface;
use App\AI\DTOs\AIResponse;
use App\AI\Providers\ClaudeProvider;
use App\AI\Providers\GeminiProvider;
use App\AI\Providers\MockAIProvider;
use App\AI\Providers\OllamaProvider;
use App\AI\Providers\OpenAIProvider;
use App\Models\AiUsageLog;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\Auth;

class AIManager
{
    protected array $providers = [];

    public function __construct()
    {
        $this->providers = [
            'mock' => new MockAIProvider(),
            'openai' => new OpenAIProvider(),
            'gemini' => new GeminiProvider(),
            'claude' => new ClaudeProvider(),
            'ollama' => new OllamaProvider(),
        ];
    }

    /**
     * Resolve provider for the active tenant or explicitly requested driver.
     */
    public function provider(?string $driver = null): AIProviderInterface
    {
        if ($driver && isset($this->providers[$driver])) {
            return $this->providers[$driver];
        }

        $tenant = TenantContext::get();
        if ($tenant && !empty($tenant->settings['ai_provider'])) {
            $configured = strtolower($tenant->settings['ai_provider']);
            if (isset($this->providers[$configured])) {
                return $this->providers[$configured];
            }
        }

        $default = env('AI_DEFAULT_PROVIDER', 'mock');
        return $this->providers[$default] ?? $this->providers['mock'];
    }

    /**
     * Generate text through the AI service layer with automated token logging and metering.
     */
    public function generateText(string $module, string $prompt, array $options = []): AIResponse
    {
        $this->checkQuotas();

        $provider = $this->provider($options['provider'] ?? null);
        $response = $provider->generateText($prompt, $options);

        $this->logUsage($module, $response);

        return $response;
    }

    /**
     * Generate structured output with automated token logging and metering.
     */
    public function generateStructured(string $module, string $prompt, array $schema, array $options = []): AIResponse
    {
        $this->checkQuotas();

        $provider = $this->provider($options['provider'] ?? null);
        $response = $provider->generateStructured($prompt, $schema, $options);

        $this->logUsage($module, $response);

        return $response;
    }

    /**
     * Generate vector embedding.
     */
    public function generateEmbedding(string $text, ?string $driver = null): array
    {
        $provider = $this->provider($driver);
        return $provider->generateEmbedding($text);
    }

    /**
     * Server-side quota guard checking available credits in subscription plan.
     */
    protected function checkQuotas(): void
    {
        $tenant = TenantContext::get();
        if (!$tenant) {
            return;
        }

        $sub = $tenant->subscription;
        if (!$sub || !$sub->plan) {
            return;
        }

        $limit = $sub->plan->max_ai_generations_per_month;
        if ($limit <= 0) {
            return; // Unlimited or custom
        }

        $startOfMonth = now()->startOfMonth();
        $usedCredits = AiUsageLog::where('tenant_id', $tenant->id)
            ->where('created_at', '>=', $startOfMonth)
            ->count();

        if ($usedCredits >= $limit) {
            throw new \RuntimeException("Monthly AI generation limit ({$limit} units) reached for this school. Please upgrade your subscription plan.");
        }
    }

    /**
     * Record execution to ai_usage_logs for accounting and audit.
     */
    protected function logUsage(string $module, AIResponse $response): void
    {
        $tenantId = TenantContext::id();
        $userId = Auth::id();

        AiUsageLog::create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'module' => $module,
            'provider' => $response->provider,
            'model' => $response->model,
            'input_tokens' => $response->promptTokens,
            'output_tokens' => $response->completionTokens,
            'computed_cost' => $response->costUsd,
            'created_at' => now(),
        ]);
    }
}
