<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;

class OrderCreated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public $order;

    public function __construct($order)
    {
        $this->order = $order;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('seller.' . $this->order->seller_id);
    }

    public function broadcastWith()
    {
        Log::info('Broadcasting OrderCreated with data: ', ['order' => $this->order]);
        return [
            'id' => $this->order->id,
            'ad_id' => $this->order->ad_id,
            'buyer_id' => $this->order->buyer_id,
            'seller_id' => $this->order->seller_id,
            'qty' => $this->order->qty,
            'price' => $this->order->price,
            'status' => 'pending',
        ];
    }
}