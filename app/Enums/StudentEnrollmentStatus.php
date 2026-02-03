<?php

namespace App\Enums;

enum StudentEnrollmentStatus: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case INACTIVATING = 'inactivating';
    case INACTIVATED = 'inactivated';
    case SUSPENDING = 'suspending';
    case SUSPENDED = 'suspended';
    case CANCELLED = 'cancelled';
    case POSTPONED = 'postponed';
    case COMPLETED = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
            self::INACTIVATING => 'inactivating',
            self::INACTIVATED => 'Inactivated',
            self::SUSPENDING => 'Suspending',
            self::SUSPENDED => 'Suspended',
            self::CANCELLED => 'Cancelled',
            self::POSTPONED => 'Postponed',
            self::COMPLETED => 'Completed',
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
