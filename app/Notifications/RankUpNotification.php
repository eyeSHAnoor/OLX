<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;

class RankUpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $rank)
    {
    }

    // Send via database + broadcast + email
    public function via($notifiable)
    {
        return ['database', 'broadcast', 'mail'];
    }

    // Email message
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('🎉 Congratulations! Your Seller Rank Increased')
            ->greeting("Hello {$notifiable->name},")
            ->line("Great news! Your seller rank has increased to Rank {$this->rank}.")
            ->line("Your ads will now receive better visibility on the platform.")
            ->line('Keep completing orders to increase your rank even further!')
            ->action('View Your Ads', url('/my-ads'))
            ->line('Thank you for being a valued seller!');
    }

    // Database payload
    public function toArray($notifiable)
    {
        return [
            'rank' => $this->rank,
            'message' => "🎉 Your seller rank increased to Rank {$this->rank}",
            'type' => 'rank_up'
        ];
    }

    // Broadcast payload
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'rank' => $this->rank,
            'message' => "🎉 Your seller rank increased to Rank {$this->rank}",
            'type' => 'rank_up'
        ]);
    }
}
