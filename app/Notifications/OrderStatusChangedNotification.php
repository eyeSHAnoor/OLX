<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Order;

class OrderStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $order;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        // Send email, store in database, and broadcast
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Email representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject("Order #{$this->order->id} Status Updated")
                    ->greeting("Hello {$notifiable->name},")
                    ->line("Your order for '{$this->order->ad->ad_title}' is now '{$this->order->status}'.")
                    ->line("Quantity: {$this->order->qty}")
                    ->line("Price: Rs. " . number_format($this->order->price))
                     ->action('Review Order', url("/orders/{$this->order->id}/review"))
                     ->line('You can confirm or cancel the order from the page.')
                    ->line('Thank you for using our platform!');
    }

    /**
     * Store notification in the database.
     */
    public function toArray($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'ad_id' => $this->order->ad_id,
            'ad_title' => $this->order->ad->ad_title,
            'status' => $this->order->status,
            'qty' => $this->order->qty,
            'price' => $this->order->price,
            'message' => "Your order #{$this->order->id} for '{$this->order->ad->ad_title}' was {$this->order->status}.",
            'type' => 'order_accepted',
            'url' => route('orders', [], false)
        ];
    }

    /**
     * Broadcast representation of the notification.
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'order_id' => $this->order->id,
            'ad_title' => $this->order->ad->ad_title,
            'status' => $this->order->status,
            'qty' => $this->order->qty,
            'price' => $this->order->price,
            'message' => "Your order #{$this->order->id} has been {$this->order->status}.",
            'type' => 'order_accepted',
            'url' => route('orders', [], false)
        ]);
    }
}