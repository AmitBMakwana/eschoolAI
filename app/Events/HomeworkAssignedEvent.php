<?php

namespace App\Events;

use App\Models\Homework;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HomeworkAssignedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Homework $homework,
        public int $tenantId
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("tenant.{$this->tenantId}.class.{$this->homework->class_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'homework.assigned';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->homework->id,
            'title' => $this->homework->title,
            'class_id' => $this->homework->class_id,
            'due_date' => $this->homework->due_date->toDateString(),
            'assigned_at' => now()->toIso8601String(),
        ];
    }
}
