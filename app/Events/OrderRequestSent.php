<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class OrderRequestSent implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public $orderRequest;

    /**
     * Create a new event instance.
     *
     * @param array $orderRequest  // All order data
     */
    public function __construct(array $orderRequest)
    {
        $this->orderRequest = $orderRequest;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return PrivateChannel
     */
    public function broadcastOn()
    {
        // Broadcast only to the buyer
        return new PrivateChannel('buyer.' . $this->orderRequest['buyer_id']);
    }

    /**
     * Customize the broadcasted payload
     *
     * @return array
     */
    public function broadcastWith()
    {
        Log::info('Broadcasting OrderRequestSent with data: ', $this->orderRequest);
        return $this->orderRequest;
    }
}