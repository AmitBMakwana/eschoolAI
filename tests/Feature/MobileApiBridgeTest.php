<?php

namespace Tests\Feature;

use App\Models\DeviceToken;
use App\Models\MobileNotification;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileApiBridgeTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $schoolA;
    protected User $teacherSchoolA;
    protected User $studentSchoolA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->schoolA = Tenant::where('subdomain', 'greenfield')->firstOrFail();
        $this->teacherSchoolA = User::where('email', 'teacher@greenfield.edu')->firstOrFail();
        $this->studentSchoolA = User::where('email', 'student@greenfield.edu')->firstOrFail();
    }

    public function test_mobile_bootstrap_for_teacher_and_student(): void
    {
        // 1. Teacher Bootstrap
        $teacherBoot = $this->actingAs($this->teacherSchoolA)
            ->getJson('/api/v1/mobile/bootstrap');

        $teacherBoot->assertStatus(200)
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
                ]
            ]);

        $this->assertNotEmpty($teacherBoot->json('data.quick_actions'));

        // 2. Student Bootstrap
        $studentBoot = $this->actingAs($this->studentSchoolA)
            ->getJson('/api/v1/mobile/bootstrap');

        $studentBoot->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'email' => 'student@greenfield.edu',
                        'role' => 'student',
                    ],
                    'student_profile' => [
                        'class_name' => 'Class 8',
                    ]
                ]
            ]);
    }

    public function test_device_token_registration_and_notification_center(): void
    {
        // 1. Register FCM Push Device Token
        $regRes = $this->actingAs($this->studentSchoolA)
            ->postJson('/api/v1/mobile/devices/register', [
                'token' => 'fcm_mock_token_abc_123_xyz',
                'platform' => 'android',
                'device_name' => 'Pixel 9 Pro',
            ]);

        $regRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'token' => 'fcm_mock_token_abc_123_xyz',
                    'platform' => 'android',
                ]
            ]);

        $this->assertDatabaseHas('device_tokens', [
            'tenant_id' => $this->schoolA->id,
            'user_id' => $this->studentSchoolA->id,
            'token' => 'fcm_mock_token_abc_123_xyz',
        ]);

        // 2. Dispatch a notification
        $notification = MobileNotification::create([
            'tenant_id' => $this->schoolA->id,
            'user_id' => $this->studentSchoolA->id,
            'title' => 'New Homework Assigned',
            'body' => 'Science Chapter 4 worksheet is due on Monday.',
            'type' => 'homework',
            'is_read' => false,
        ]);

        // 3. Query notifications
        $listRes = $this->actingAs($this->studentSchoolA)
            ->getJson('/api/v1/mobile/notifications');

        $listRes->assertStatus(200);
        $this->assertEquals(1, $listRes->json('meta.unread_count'));

        // 4. Mark as read
        $readRes = $this->actingAs($this->studentSchoolA)
            ->postJson("/api/v1/mobile/notifications/{$notification->id}/read");

        $readRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'is_read' => true,
                ]
            ]);
    }

    public function test_offline_delta_sync_and_student_feed(): void
    {
        // 1. Delta sync
        $syncRes = $this->actingAs($this->studentSchoolA)
            ->getJson('/api/v1/mobile/sync/delta?last_synced_at=' . urlencode(now()->subHours(24)->toIso8601String()));

        $syncRes->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertArrayHasKey('notices', $syncRes->json('data.delta'));
        $this->assertArrayHasKey('homework', $syncRes->json('data.delta'));
        $this->assertArrayHasKey('timetables', $syncRes->json('data.delta'));

        // 2. Student Daily Feed
        $feedRes = $this->actingAs($this->studentSchoolA)
            ->getJson('/api/v1/mobile/student-feed');

        $feedRes->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertArrayHasKey('active_homework', $feedRes->json('data'));
        $this->assertArrayHasKey('recent_notices', $feedRes->json('data'));
        $this->assertArrayHasKey('upcoming_exams', $feedRes->json('data'));
    }
}
