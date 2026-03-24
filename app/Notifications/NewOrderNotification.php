<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;

class NewOrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public $order)
    {
    }

    // Send via database + broadcast
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    // Database payload
    public function toArray($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'ad_id' => $this->order->ad_id,
            'buyer_id' => $this->order->buyer_id,
            'buyer_name' => $this->order->buyer->name ?? 'Unknown',
            'message' => "{$this->order->buyer->name} placed an order on your ad",
            'type' => 'new_order'
        ];
    }

    // Broadcast payload for real-time
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'order_id' => $this->order->id,
            'ad_id' => $this->order->ad_id,
            'buyer_id' => $this->order->buyer_id,
            'buyer_name' => $this->order->buyer->name ?? 'Unknown',
            'message' => "{$this->order->buyer->name} placed an order on your ad",
            'type' => 'new_order'
        ]);
    }
}