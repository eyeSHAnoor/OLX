<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class ScheduledNotificationData extends Data
{
    public function __construct(
        public ?int $id,
        public string $title,
        public string $message,
        public ?string $url,
        public string $scheduled_at,
        public bool $is_sent,
        public string $created_at,
        public ?string $formatted_scheduled_at = null,
        public ?string $status_text = null,
        public ?string $status_color = null,
    ) {
        $this->formatted_scheduled_at = $this->scheduled_at ? date('M d, Y H:i', strtotime($this->scheduled_at)) : null;
        $this->status_text = $this->is_sent ? 'Sent' : 'Pending';
        $this->status_color = $this->is_sent ? 'green' : 'yellow';
    }
}