<?php
namespace App\Events;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\Channel;
class NotificationCreated implements ShouldBroadcast
{
    public $notification;
    // public $userId;

    public function __construct($notification)
    {
        $this->notification = $notification;
        // $this->userId = $userId;
    }

    public function broadcastOn()
    {
        // dd($this->userId);
        // return new PrivateChannel('notifications.' . $this->userId);
        return new Channel('notifications');
    }

    public function broadcastAs()
    {
        return 'notification.created';
    }



    public function broadcastWith()
    {
        // \Log::info('Broadcasting notification', [
        //     'notification' => $this->notification,
        //     'userId' => $this->userId
        // ]);
        return [
            'id' => $this->notification->id,
            'title' => $this->notification->title,
            'message' => $this->notification->message,
            'type' => $this->notification->type,
            'created_at' => $this->notification->created_at->toDateTimeString(),
        ];
    }
}
