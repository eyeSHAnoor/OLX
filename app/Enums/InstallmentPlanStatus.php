<?php

namespace App\Enums;

enum InstallmentPlanStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case PARTIAL_PAID = 'partial_paid';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PARTIAL_PAID => 'Partial Paid',
            self::PAID => 'Paid',
            self::FAILED => 'Failed',
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
