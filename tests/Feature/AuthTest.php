<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_super_admin_can_login(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'superadmin@schoolos.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'email' => 'superadmin@schoolos.com',
                        'role' => 'super-admin',
                    ],
                    'redirect_url' => '/platform/dashboard',
                ]
            ]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_teacher_login_resolves_school_and_role(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'teacher@greenfield.edu',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'email' => 'teacher@greenfield.edu',
                        'role' => 'teacher',
                    ],
                    'tenant' => [
                        'subdomain' => 'greenfield',
                    ],
                    'redirect_url' => '/teacher/dashboard',
                ]
            ]);
    }

    public function test_invalid_password_is_rejected(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@greenfield.edu',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid email or password.',
            ]);
    }

    public function test_auth_me_returns_profile_with_permissions(): void
    {
        $teacher = User::where('email', 'teacher@greenfield.edu')->firstOrFail();
        $token = $teacher->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'email' => 'teacher@greenfield.edu',
                        'role' => 'teacher',
                    ],
                    'tenant' => [
                        'subdomain' => 'greenfield',
                    ]
                ]
            ]);

        $permissions = $response->json('data.user.permissions');
        $this->assertContains('attendance.mark', $permissions);
        $this->assertContains('ai.lesson_plan', $permissions);
    }

    public function test_logout_revokes_token(): void
    {
        $teacher = User::where('email', 'teacher@greenfield.edu')->firstOrFail();
        $token = $teacher->createToken('test_token')->plainTextToken;

        $this->assertDatabaseCount('personal_access_tokens', 1);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
