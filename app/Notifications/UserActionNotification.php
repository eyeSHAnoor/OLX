<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class UserActionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $actionType;
    protected $reason;
    protected $responseMessage;
    protected $reportId;
    protected $expiresAt;

    public function __construct($actionType, $reason, $responseMessage, $reportId, $expiresAt = null)
    {
        $this->actionType = $actionType;
        $this->reason = $reason;
        $this->responseMessage = $responseMessage;
        $this->reportId = $reportId;
        $this->expiresAt = $expiresAt;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast', 'mail'];
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject('Important: Account Action Notification - ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name . ',');

        switch ($this->actionType) {
            case 'warn':
                $mail->line('We have issued a warning on your account.')
                    ->line('**Reason:** ' . ucfirst(str_replace('_', ' ', $this->reason)))
                    ->line('**Warning Count:** ' . ($notifiable->warning_count + 1))
                    ->line('**Message from Admin:**')
                    ->line('"' . $this->responseMessage . '"')
                    ->line('Please review our community guidelines to avoid further actions on your account.')
                    ->action('View Community Guidelines', route('guidelines'));
                break;

            case 'suspend':
                $mail->line('Your account has been temporarily suspended.')
                    ->line('**Reason:** ' . ucfirst(str_replace('_', ' ', $this->reason)))
                    ->line('**Suspension Period:** 7 days')
                    ->line('**Valid Until:** ' . $this->expiresAt->format('F j, Y'))
                    ->line('**Message from Admin:**')
                    ->line('"' . $this->responseMessage . '"')
                    ->line('Your account will be automatically reactivated after the suspension period.')
                    ->action('Contact Support', route('contact'));
                break;

            case 'ban':
                $mail->line('Your account has been permanently banned.')
                    ->line('**Reason:** ' . ucfirst(str_replace('_', ' ', $this->reason)))
                    ->line('**Message from Admin:**')
                    ->line('"' . $this->responseMessage . '"')
                    ->line('This action is permanent and cannot be reversed.')
                    ->line('If you believe this was a mistake, please contact our support team.')
                    ->action('Contact Support', route('contact'));
                break;
        }

        return $mail->line('Thank you for your understanding.');
    }

    public function toDatabase($notifiable)
    {
        $data = [
            'type' => 'user_action',
            'action_type' => $this->actionType,
            'reason' => $this->reason,
            'reason_label' => ucfirst(str_replace('_', ' ', $this->reason)),
            'report_id' => $this->reportId,
            'message' => $this->getDatabaseMessage(),
            'body' => $this->getDatabaseBody(),
            'action_text' => $this->getActionText(),
            'action_url' => $this->getActionUrl(),
            'severity' => $this->getSeverity(),
            'created_at' => now(),
        ];

        if ($this->expiresAt) {
            $data['expires_at'] = $this->expiresAt;
        }

        return $data;
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'type' => 'user_action',
            'action_type' => $this->actionType,
            'message' => $this->getBroadcastMessage(),
            'severity' => $this->getSeverity(),
            'time' => now()->diffForHumans(),
        ]);
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'user_action',
            'action_type' => $this->actionType,
            'reason' => $this->reason,
            'report_id' => $this->reportId,
            'message' => $this->getDatabaseMessage(),
        ];
    }

    private function getDatabaseMessage(): string
    {
        switch ($this->actionType) {
            case 'warn':
                return "Your account has received a warning";
            case 'suspend':
                return "Your account has been temporarily suspended";
            case 'ban':
                return "Your account has been permanently banned";
            default:
                return "An action has been taken on your account";
        }
    }

    private function getDatabaseBody(): string
    {
        switch ($this->actionType) {
            case 'warn':
                return "You have received a warning. Further violations may result in suspension.";
            case 'suspend':
                return "Your account is suspended until " . $this->expiresAt->format('M j, Y');
            case 'ban':
                return "Your account has been permanently banned from our platform";
            default:
                return "Please check your email for more details";
        }
    }

    private function getBroadcastMessage(): string
    {
        switch ($this->actionType) {
            case 'warn':
                return "⚠️ Warning issued on your account";
            case 'suspend':
                return "🔒 Your account has been suspended";
            case 'ban':
                return "🚫 Your account has been banned";
            default:
                return "Account action notification";
        }
    }

    private function getActionText(): ?string
    {
        switch ($this->actionType) {
            case 'warn':
                return 'View Guidelines';
            case 'suspend':
            case 'ban':
                return 'Contact Support';
            default:
                return null;
        }
    }

    private function getActionUrl(): ?string
    {
        switch ($this->actionType) {
            case 'warn':
                return route('guidelines');
            case 'suspend':
            case 'ban':
                return route('contact');
            default:
                return null;
        }
    }

    private function getSeverity(): string
    {
        switch ($this->actionType) {
            case 'warn':
                return 'warning';
            case 'suspend':
                return 'error';
            case 'ban':
                return 'critical';
            default:
                return 'info';
        }
    }
}