<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;

class ScheduledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public ?string $url = null,
        public bool $isEmail = false // Add this parameter
    ) {}

    public function via($notifiable)
    {
        $channels = ['database', 'broadcast', WebPushChannel::class];
        
        // Add mail channel if isEmail is true
        if ($this->isEmail) {
            $channels[] = 'mail';
        }
        
        return $channels;
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'type' => 'scheduled_notification',
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'type' => 'scheduled_notification',
        ]);
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title($this->title)
            ->icon('/images/notif-logo.png')
            ->body($this->message)
            ->data([
                'url' => $this->url,
                'type' => 'scheduled_notification',
            ]);
    }

    // Add this new method for email
    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject($this->title)
            ->greeting('Greetings!')
            ->line($this->message);

        if ($this->url) {
            $mail->action('View Details', $this->url);
        }

        return $mail->line('Thank you for using our application!');
    }

    public function toArray($notifiable)
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'type' => 'scheduled_notification',
        ];
    }
}