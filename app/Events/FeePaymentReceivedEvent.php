<?php

namespace App\Events;

use App\Models\FeePayment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FeePaymentReceivedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public FeePayment $payment,
        public int $tenantId
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("tenant.{$this->tenantId}.student.{$this->payment->student_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'fee.payment_received';
    }

    public function broadcastWith(): array
    {
        return [
            'receipt_number' => $this->payment->receipt_number,
            'amount_paid' => $this->payment->amount_paid,
            'paid_at' => $this->payment->paid_at->toIso8601String(),
        ];
    }
}
