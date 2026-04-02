<?php

namespace App\Events;

use App\Models\Message;
use App\Models\Conversation; 
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// App\Events\ConversationUpdated.php
class ConversationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public $conversation;
    public $userId; // the user to notify

    public function __construct(Conversation $conversation, $userId)
    {
        $this->conversation = $conversation->load(['buyer', 'seller', 'product', 'messages' => function($q) {
            $q->latest()->limit(1);
        }]);
        $this->userId = $userId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('user.' . $this->userId);
    }
}