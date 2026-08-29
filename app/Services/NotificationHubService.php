<?php

namespace App\Services;

use App\Events\CircularBroadcastEvent;
use App\Models\DeviceToken;
use App\Models\MobileNotification;
use App\Models\Notice;
use App\Models\User;
use App\Tenancy\TenantContext;

class NotificationHubService
{
    /**
     * Dispatch multi-channel notification (In-App DB, Push Notification, Realtime WebSocket).
     */
    public function notifyUser(User $user, string $title, string $body, string $type, array $data = []): MobileNotification
    {
        $tenantId = TenantContext::id();

        // 1. Create In-App Mobile Notification record
        $notification = MobileNotification::create([
            'tenant_id' => $tenantId,
            'user_id' => $user->id,
            'title' => $title,
            'body' => $body,
            'type' => $type,
            'data' => $data,
            'is_read' => false,
        ]);

        // 2. Fetch active device push tokens
        $tokens = DeviceToken::where('user_id', $user->id)->pluck('token')->toArray();

        // 3. Simulated Push Dispatch Log (FCM / APNs)
        // In production, Http::post('https://fcm.googleapis.com/fcm/send', ...) is invoked

        return $notification;
    }

    /**
     * Broadcast emergency announcement to all active tenant channels.
     */
    public function broadcastEmergencyAlert(string $title, string $message, int $senderUserId): Notice
    {
        $tenantId = TenantContext::id();

        $notice = Notice::create([
            'tenant_id' => $tenantId,
            'created_by_user_id' => $senderUserId,
            'title' => 'EMERGENCY ALERT: ' . $title,
            'content' => $message,
            'audience_type' => 'all',
            'is_published' => true,
            'published_at' => now(),
        ]);

        // Trigger realtime WebSocket broadcast
        broadcast(new CircularBroadcastEvent($notice, $tenantId))->toOthers();

        // Notify all active tenant users
        $users = User::where('tenant_id', $tenantId)->get();
        foreach ($users as $user) {
            $this->notifyUser($user, 'URGENT: ' . $title, $message, 'circular', ['notice_id' => $notice->id]);
        }

        return $notice;
    }
}
