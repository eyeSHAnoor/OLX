<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;

class GiftCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public $giftAssignment,
        public $gift,
        public $period
    ) {}

    public function via($notifiable)
    {
        return ['database', 'broadcast', 'mail', WebPushChannel::class];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => '⚠️ Gift Assignment Cancelled',
            'gift_id' => $this->gift->id,
            'gift_name' => $this->gift->name,
            'assignment_id' => $this->giftAssignment->id,
            'period_name' => $this->period->name,
            'reason' => $this->giftAssignment->notes,
            'message' => "Your gift '{$this->gift->name}' assignment has been cancelled.",
            'type' => 'gift_cancelled',
            // 'url' => route('user.my-gifts', absolute: false),
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title' => '⚠️ Gift Cancelled',
            'gift_name' => $this->gift->name,
            'message' => "Your gift '{$this->gift->name}' assignment has been cancelled.",
            // 'url' => route('user.my-gifts', absolute: false),
        ]);
    }

    public function toMail($notifiable)
    {
        $mailMessage = (new MailMessage)
            ->subject('⚠️ Gift Assignment Update - ' . $this->gift->name)
            ->greeting('Dear ' . $notifiable->name . ',')
            ->line('We need to inform you about an update to your gift assignment.')
            ->line('')
            ->line('**Gift:** ' . $this->gift->name)
            ->line('**Campaign:** ' . $this->period->name)
            ->line('**Status:** Cancelled')
            ->line('**Date:** ' . now()->format('F d, Y'));

        if ($this->giftAssignment->notes) {
            $mailMessage->line('')
                ->line('**Reason:** ' . $this->giftAssignment->notes);
        }

        $mailMessage->line('')
            ->line('We understand this may be disappointing. You remain eligible for future campaigns and rewards.')
            ->line('')
            ->line('If you have any questions, please contact our support team.')
            // ->action('View Details', route('user.my-gifts'))
            ->line('Thank you for your understanding and continued loyalty.');

        return $mailMessage;
    }

    public function toWebPush($notifiable, $notification)
    {
        // Log::info('Sending Gift Cancelled Web Push Notification', [
        //     'user_id' => $notifiable->id,
        //     'gift_name' => $this->gift->name,
        // ]);

        return (new WebPushMessage)
            ->title('⚠️ Gift Assignment Update')
            ->icon('/images/notif-logo.png')
            ->body("Your gift '{$this->gift->name}' assignment has been cancelled.")
            ->data([
                'gift_id' => $this->gift->id,
                // 'url' => route('user.my-gifts', absolute: false),
            ])
            ->vibrate([100, 50, 100])
            ->tag('gift-cancelled');
    }
}