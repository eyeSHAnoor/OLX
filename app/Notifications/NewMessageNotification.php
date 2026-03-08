<?php
// app/Notifications/NewMessageNotification.php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class NewMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public $chat, public $message)
    {
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'chat_id' => $this->chat->id,
            'ad_id' => $this->chat->ad_id,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $this->message->sender->name,
            'message' => "{$this->message->sender->name} sends You a message",
            // 'message' => $this->message->body,
            'conversation_id' => $this->chat->id,
            'type' => 'new_message'
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'chat_id' => $this->chat->id,
            'message' => "{$this->message->sender->name} sends You a message",
            'sender_id' => $this->message->sender_id,
            'sender_name' => $this->message->sender->name ?? 'Unknown',
        ]);
    }
}