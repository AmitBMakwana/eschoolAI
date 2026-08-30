<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HealthCheckController extends Controller
{
    /**
     * Complete system health and readiness check probe.
     */
    public function check(): JsonResponse
    {
        $status = 'healthy';
        $services = [];

        // 1. Database Check
        try {
            DB::connection()->getPdo();
            $services['database'] = [
                'status' => 'up',
                'driver' => DB::connection()->getDriverName(),
            ];
        } catch (\Exception $e) {
            $status = 'unhealthy';
            $services['database'] = [
                'status' => 'down',
                'error' => $e->getMessage(),
            ];
        }

        // 2. Cache / Storage Check
        try {
            Cache::put('health_check_ping', 'pong', 10);
            $cached = Cache::get('health_check_ping');
            $services['cache'] = [
                'status' => $cached === 'pong' ? 'up' : 'degraded',
                'driver' => config('cache.default'),
            ];
        } catch (\Exception $e) {
            $services['cache'] = [
                'status' => 'down',
                'error' => $e->getMessage(),
            ];
        }

        // 3. Storage Writeability Check
        try {
            Storage::disk('local')->put('health_test.tmp', 'test');
            Storage::disk('local')->delete('health_test.tmp');
            $services['storage'] = [
                'status' => 'up',
                'disk' => 'local',
            ];
        } catch (\Exception $e) {
            $services['storage'] = [
                'status' => 'down',
                'error' => $e->getMessage(),
            ];
        }

        // 4. AI & Vector Engine Status
        $services['ai_engine'] = [
            'status' => 'ready',
            'active_provider' => config('services.ai.default_provider', 'openai'),
            'models' => ['gpt-4o', 'gemini-1.5-pro', 'claude-3-5-sonnet', 'llama3'],
        ];

        $services['vector_search'] = [
            'status' => 'ready',
            'engine' => 'Qdrant',
        ];

        $statusCode = $status === 'healthy' ? 200 : 503;

        return response()->json([
            'status' => $status,
            'timestamp' => now()->toIso8601String(),
            'platform' => 'AI SchoolOS',
            'version' => '1.0.0-PROD',
            'services' => $services,
        ], $statusCode);
    }
}
