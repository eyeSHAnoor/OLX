<?php
// app/Notifications/NewRatingNotification.php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class NewRatingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $rater;
    protected $rating;
    protected $adId;

    public function __construct($rater, $rating, $adId)
    {
        $this->rater = $rater;
        $this->rating = $rating;
        $this->adId = $adId;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'new_rating',
            'rater_id' => $this->rater->id,
            'rater_name' => $this->rater->name,
            'rater_avatar' => $this->rater->profile->profile_image ?? null,
            'rating' => $this->rating->rating,
            'review' => $this->rating->review,
            'ad_id' => $this->adId,
            'message' => "{$this->rater->name} gave you a {$this->rating->star_rating} rating",
            'body' => "{$this->rater->name} rated you " . str_repeat('⭐', $this->rating->rating),
            'created_at' => now(),
            'url' => route('ads.show', $this->adId, false)
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'type' => 'new_rating',
            'rater_id' => $this->rater->id,
            'rater_name' => $this->rater->name,
            'rater_avatar' => $this->rater->profile->profile_image ?? null,
            'rating' => $this->rating->rating,
            'rating_display' => str_repeat('⭐', $this->rating->rating),
            'review' => $this->rating->review,
            'ad_id' => $this->adId,
            'message' => "{$this->rater->name} gave you a {$this->rating->rating}-star rating",
            'time' => now()->diffForHumans(),
            'url' => route('ads.show', $this->adId, false)
        ]);
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'new_rating',
            'rater_id' => $this->rater->id,
            'rater_name' => $this->rater->name,
            'rater_avatar' => $this->rater->profile->profile_image ?? null,
            'rating' => $this->rating->rating,
            'review' => $this->rating->review,
            'ad_id' => $this->adId,
            'message' => "{$this->rater->name} gave you a {$this->rating->star_rating} rating",
            'url' => route('ads.show', $this->adId, false)
        ];
    }
}