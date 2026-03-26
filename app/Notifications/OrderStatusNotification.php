<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;

class OrderStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public $order, public $status)
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
            'seller_id' => $this->order->seller_id,
            'seller_name' => $this->order->seller->name ?? 'Seller',
            'status' => $this->status,
            'message' => $this->statusMessage(),
            'type' => 'order_status',
            'url' => route('orders', [], false)
        ];
    }

    // Broadcast payload
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'order_id' => $this->order->id,
            'ad_id' => $this->order->ad_id,
            'seller_id' => $this->order->seller_id,
            'seller_name' => $this->order->seller->name ?? 'Seller',
            'status' => $this->status,
            'message' => $this->statusMessage(),
            'type' => 'order_status',
            'url' => route('orders', [], false)
        ]);
    }

    protected function statusMessage()
    {
        if ($this->status === 'completed') {
            return "Your order #{$this->order->id} has been completed by the seller.";
        }

        if ($this->status === 'cancelled') {
            return "Your order #{$this->order->id} has been cancelled by the seller.";
        }

        return "Your order #{$this->order->id} status was updated.";
    }
}