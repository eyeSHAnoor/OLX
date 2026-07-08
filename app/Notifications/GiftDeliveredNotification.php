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

class GiftDeliveredNotification extends Notification implements ShouldQueue
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
            'title' => '🚚 Your Gift Has Been Delivered!',
            'gift_id' => $this->gift->id,
            'gift_name' => $this->gift->name,
            'gift_image' => $this->gift->image,
            'assignment_id' => $this->giftAssignment->id,
            'period_name' => $this->period->name,
            'message' => "Your gift '{$this->gift->name}' has been delivered! Enjoy!",
            'type' => 'gift_delivered',
            // 'url' => route('user.my-gifts', absolute: false),
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title' => '🚚 Gift Delivered!',
            'gift_name' => $this->gift->name,
            'message' => "Your gift '{$this->gift->name}' has been delivered!",
            // 'url' => route('user.my-gifts', absolute: false),
        ]);
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('🚚 Your Gift Has Been Delivered - ' . $this->gift->name)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Great news! Your gift has been delivered!')
            ->line('')
            ->line('**Gift:** ' . $this->gift->name)
            ->line('**Campaign:** ' . $this->period->name)
            ->line('**Delivered On:** ' . now()->format('F d, Y'))
            ->line('')
            ->line('We hope you enjoy your gift! If you have any questions, please contact our support team.')
            // ->action('View Details', route('user.my-gifts'))
            ->line('Thank you for your continued loyalty!');
    }

    public function toWebPush($notifiable, $notification)
    {
        // Log::info('Sending Gift Delivered Web Push Notification', [
        //     'user_id' => $notifiable->id,
        //     'gift_name' => $this->gift->name,
        // ]);

        return (new WebPushMessage)
            ->title('🚚 Gift Delivered!')
            ->icon($this->gift->image ? '/storage/' . $this->gift->image : '/icon.png')
            ->body("Your gift '{$this->gift->name}' has been delivered! Enjoy!")
            ->data([
                'gift_id' => $this->gift->id,
                // 'url' => route('user.my-gifts', absolute: false),
            ])
            ->vibrate([200, 100, 200])
            ->tag('gift-delivered');
    }
}