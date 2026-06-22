<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class CommentReplyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Comment $reply,
        public Comment $parentComment
    ) {}

    public function via($notifiable)
    {
        return ['database', 'broadcast']; // you can add WebPush if needed
    }

    public function toArray($notifiable)
    {
        return [
            'reply_id'   => $this->reply->id,
            'ad_id'      => $this->parentComment->ad_id,
            'replier_id' => $this->reply->user_id,
            'replier_name' => $this->reply->user->name ?? 'Someone',
            'message'    => "{$this->reply->user->name} replied to your comment",
            'url'        => route('ads.show', $this->parentComment->ad_id) . '#comment-' . $this->reply->id,
            'type'       => 'comment_reply',
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'reply_id'   => $this->reply->id,
            'ad_id'      => $this->parentComment->ad_id,
            'replier_name' => $this->reply->user->name ?? 'Someone',
            'message'    => "{$this->reply->user->name} replied to your comment",
            'url'        => route('ads.show', $this->parentComment->ad_id),
        ]);
    }
}