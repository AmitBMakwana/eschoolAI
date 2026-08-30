<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalAndSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_enterprise_portal_view_loads_successfully(): void
    {
        $response = $this->get('/portal');

        $response->assertStatus(200);
        $response->assertSee('eschoolAI');
        $response->assertSee('Fees');
    }

    public function test_demo_school_generator_artisan_command(): void
    {
        $this->seed();

        $this->artisan('schoolos:demo-school', [
            '--name' => 'Oxford International Academy',
            '--subdomain' => 'oxford',
        ])->assertSuccessful();

        $this->assertDatabaseHas('tenants', [
            'name' => 'Oxford International Academy',
            'subdomain' => 'oxford',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'admin@oxford.edu',
        ]);
    }
}
