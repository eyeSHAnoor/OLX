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

class GiftWonNotification extends Notification implements ShouldQueue
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

    /**
     * Database notification
     */
    public function toArray($notifiable)
    {
        // Log::info('Sending Gift Won Database Notification', [
        //     'user_id' => $notifiable->id,
        //     'gift_name' => $this->gift->name,
        //     'assignment_id' => $this->giftAssignment->id,
        // ]);
        return [
            'title' => '🎁 Congratulations! You\'ve Won a Gift!',
            'gift_id' => $this->gift->id,
            'gift_name' => $this->gift->name,
            'gift_image' => $this->gift->image,
            'gift_description' => $this->gift->description,
            'assignment_id' => $this->giftAssignment->id,
            'period_id' => $this->period->id,
            'period_name' => $this->period->name,
            'message' => $this->getNotificationMessage(),
            'type' => 'gift_won',
            // 'url' => route('user.my-gifts', absolute: false),
        ];
    }

    /**
     * Broadcast notification for real-time updates
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title' => '🎁 Congratulations! You\'ve Won a Gift!',
            'gift_name' => $this->gift->name,
            'gift_image' => $this->gift->image,
            'assignment_id' => $this->giftAssignment->id,
            'message' => $this->getNotificationMessage(),
            'period_name' => $this->period->name,
            // 'url' => route('user.my-gifts', absolute: false),
        ]);
    }

    /**
     * Email notification using 7 C's of Communication
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('🎁 Congratulations ' . $notifiable->name . '! You\'ve Won a Gift!')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('## 🎉 Exciting News! You\'ve Won a Gift!')
            ->line('---')
            ->line('We are thrilled to announce that you have been selected to receive a special gift as part of our **' . $this->period->name . '** campaign!')
            ->line('')
            ->line('### 🎁 Your Gift:')
            ->line('**' . $this->gift->name . '**')
            ->line('*' . ($this->gift->description ?? 'A special reward for your loyalty') . '*')
            ->line('')
            ->line('### 📋 What You Need to Know:')
            ->line('✅ **Congratulations!** Your continuous 4-month subscription has earned you this exclusive reward.')
            ->line('✅ **Gift Status:** Your gift has been reserved and is ready for delivery.')
            ->line('✅ **Campaign Period:** ' . $this->period->name . ' (' . $this->period->start_date->format('M d, Y') . ' - ' . $this->period->end_date->format('M d, Y') . ')')
            ->line('')
            ->line('### 📝 Next Steps:')
            ->line('1. Log in to your account to view your gift details')
            ->line('2. Your gift will be processed and delivered according to our campaign schedule')
            ->line('3. You will receive updates on the delivery status')
            ->line('')
            ->line('---')
            ->line('### 💝 Thank You for Your Loyalty!')
            ->line('This gift is our way of saying **thank you** for being a valued subscriber. Your continued trust and support mean the world to us.')
            ->line('')
            ->line('If you have any questions or need assistance, our support team is always here to help.')
            ->line('')
            // ->action('🎁 View Your Gift', route('user.my-gifts'))
            ->line('')
            ->line('Warm regards,')
            ->line('**The ' . config('app.name') . ' Team**')
            ->line('*Delivering smiles, one gift at a time.*');
    }

    /**
     * Web Push notification
     */
    public function toWebPush($notifiable, $notification)
    {
        // Log::info('Sending Gift Won Web Push Notification', [
        //     'user_id' => $notifiable->id,
        //     'gift_name' => $this->gift->name,
        //     'assignment_id' => $this->giftAssignment->id,
        // ]);

        return (new WebPushMessage)
            ->title('🎁 You\'ve Won a Gift!')
            ->icon($this->gift->image ? '/storage/' . $this->gift->image : '/icon.png')
            ->body("Congratulations! You've received {$this->gift->name} for your loyalty!")
            ->data([
                'gift_id' => $this->gift->id,
                'assignment_id' => $this->giftAssignment->id,
                // 'url' => route('user.my-gifts', absolute: false),
            ])
            ->vibrate([200, 100, 200])
            ->tag('gift-won')
            ->requireInteraction(true);
    }

    /**
     * Get formatted notification message using 7 C's
     */
    private function getNotificationMessage(): string
    {
        return "🎁 Congratulations! You've won {$this->gift->name} as part of our {$this->period->name} loyalty program. Thank you for being a valued subscriber! 🎉";
    }
}