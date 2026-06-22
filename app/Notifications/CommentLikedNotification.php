<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class CommentLikedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Comment $comment,
        public int $likerId
    ) {}

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        $liker = \App\Models\User::find($this->likerId);
        return [
            'comment_id'  => $this->comment->id,
            'ad_id'       => $this->comment->ad_id,
            'liker_id'    => $this->likerId,
            'liker_name'  => $liker->name ?? 'Someone',
            'message'     => "{$liker->name} liked your comment",
            'url'         => route('ads.show', $this->comment->ad_id) . '#comment-' . $this->comment->id,
            'type'        => 'comment_liked',
        ];
    }

    public function toBroadcast($notifiable)
    {
        $liker = \App\Models\User::find($this->likerId);
        return new BroadcastMessage([
            'comment_id'  => $this->comment->id,
            'ad_id'       => $this->comment->ad_id,
            'liker_name'  => $liker->name ?? 'Someone',
            'message'     => "{$liker->name} liked your comment",
            'url'         => route('ads.show', $this->comment->ad_id),
        ]);
    }
}