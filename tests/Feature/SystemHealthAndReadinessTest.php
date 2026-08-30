<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemHealthAndReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_healthz_and_api_health_endpoints(): void
    {
        // 1. Test /healthz web probe
        $healthzRes = $this->get('/healthz');

        $healthzRes->assertStatus(200)
            ->assertJson([
                'status' => 'healthy',
                'platform' => 'eschoolAI',
                'services' => [
                    'database' => [
                        'status' => 'up',
                    ],
                    'cache' => [
                        'status' => 'up',
                    ],
                    'storage' => [
                        'status' => 'up',
                    ],
                    'ai_engine' => [
                        'status' => 'ready',
                    ],
                    'vector_search' => [
                        'status' => 'ready',
                    ],
                ]
            ]);

        // 2. Test /api/v1/health probe
        $apiHealthRes = $this->getJson('/api/v1/health');

        $apiHealthRes->assertStatus(200)
            ->assertJson([
                'status' => 'healthy',
                'version' => '1.0.0-PROD',
            ]);
    }
}
