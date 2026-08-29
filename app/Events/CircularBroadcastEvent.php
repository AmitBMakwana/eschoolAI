<?php

namespace App\Events;

use App\Models\Notice;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CircularBroadcastEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Notice $notice,
        public int $tenantId
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("tenant.{$this->tenantId}.notices"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'circular.broadcast';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->notice->id,
            'title' => $this->notice->title,
            'audience_type' => $this->notice->audience_type,
            'published_at' => now()->toIso8601String(),
        ];
    }
}
