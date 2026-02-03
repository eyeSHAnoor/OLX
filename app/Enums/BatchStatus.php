<?php

namespace App\Enums;

enum BatchStatus: string
{
    case SCHEDULED = 'scheduled';
    case STARTED = 'started';
    case COMPLETED = 'completed';
    case ACADEMIC_DELIVERY = 'academic_delivery';
    case CANCELLED = 'cancelled';
    case POSTPONED = 'postponed';

    public function label(): string
    {
        return match ($this) {
            self::SCHEDULED => 'Scheduled',
            self::STARTED => 'Started',
            self::COMPLETED => 'Completed',
            self::ACADEMIC_DELIVERY => 'Academic Delivery',
            self::CANCELLED => 'Cancelled',
            self::POSTPONED => 'Postponed',
        };
    }

    public static function getAll(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }
}
