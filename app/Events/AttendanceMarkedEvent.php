<?php

namespace App\Events;

use App\Models\Attendance;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttendanceMarkedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Attendance $attendance,
        public int $tenantId
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("tenant.{$this->tenantId}.class.{$this->attendance->class_id}"),
            new PrivateChannel("tenant.{$this->tenantId}.student.{$this->attendance->student_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'attendance.marked';
    }

    public function broadcastWith(): array
    {
        return [
            'student_id' => $this->attendance->student_id,
            'status' => $this->attendance->status,
            'date' => $this->attendance->date->toDateString(),
            'marked_at' => now()->toIso8601String(),
        ];
    }
}
