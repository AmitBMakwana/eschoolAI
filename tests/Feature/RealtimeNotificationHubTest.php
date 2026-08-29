<?php

namespace Tests\Feature;

use App\Events\CircularBroadcastEvent;
use App\Models\MobileNotification;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RealtimeNotificationHubTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $schoolA;
    protected User $adminSchoolA;
    protected User $teacherSchoolA;
    protected User $studentSchoolA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->schoolA = Tenant::where('subdomain', 'greenfield')->firstOrFail();
        $this->adminSchoolA = User::where('email', 'admin@greenfield.edu')->firstOrFail();
        $this->teacherSchoolA = User::where('email', 'teacher@greenfield.edu')->firstOrFail();
        $this->studentSchoolA = User::where('email', 'student@greenfield.edu')->firstOrFail();
    }

    public function test_authorized_websocket_channels_for_roles(): void
    {
        // 1. Teacher channels
        $teacherRes = $this->actingAs($this->teacherSchoolA)
            ->getJson('/api/v1/realtime/channels');

        $teacherRes->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertContains("private-tenant.{$this->schoolA->id}.teachers", $teacherRes->json('data.authorized_channels'));
        $this->assertContains("private-tenant.{$this->schoolA->id}.notices", $teacherRes->json('data.authorized_channels'));

        // 2. Student channels
        $studentRes = $this->actingAs($this->studentSchoolA)
            ->getJson('/api/v1/realtime/channels');

        $studentRes->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertContains("private-tenant.{$this->schoolA->id}.notices", $studentRes->json('data.authorized_channels'));
        $this->assertStringContainsString("class", implode(',', $studentRes->json('data.authorized_channels')));
    }

    public function test_emergency_alert_broadcast_and_authorization(): void
    {
        Event::fake([CircularBroadcastEvent::class]);

        // 1. Regular teacher tries to trigger emergency alert -> 403 Forbidden
        $teacherRes = $this->actingAs($this->teacherSchoolA)
            ->postJson('/api/v1/realtime/emergency-alert', [
                'title' => 'Severe Weather Closure',
                'message' => 'Campus closed due to incoming blizzard.',
            ]);

        $teacherRes->assertStatus(403);

        // 2. Admin triggers emergency alert -> 201 Created
        $adminRes = $this->actingAs($this->adminSchoolA)
            ->postJson('/api/v1/realtime/emergency-alert', [
                'title' => 'Severe Weather Campus Closure',
                'message' => 'Due to incoming heavy storms, all classes are suspended immediately. School buses are departing now.',
            ]);

        $adminRes->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'audience_type' => 'all',
                ]
            ]);

        // Verify broadcast event dispatched
        Event::assertDispatched(CircularBroadcastEvent::class);

        // Verify notification inbox received alert
        $this->assertDatabaseHas('mobile_notifications', [
            'tenant_id' => $this->schoolA->id,
            'user_id' => $this->studentSchoolA->id,
            'type' => 'circular',
        ]);
    }
}
