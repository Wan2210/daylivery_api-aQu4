<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderStatusUpdated extends Notification
{
    use Queueable;

    public function __construct(
        public string $orderNumber,
        public string $status,
        public ?int $orderId = null,
        public ?string $title = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'order_status_updated',
            'order_id' => $this->orderId,
            'order_number' => $this->orderNumber,
            'status' => $this->status,
            'title' => $this->title ?? 'Status pesanan diperbarui',
            'message' => 'Pesanan '.$this->orderNumber.' berstatus '.$this->status.'.',
            'readable_status' => $this->status,
            'created_at' => now()->toIso8601String(),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
