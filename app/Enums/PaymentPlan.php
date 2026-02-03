<?php

namespace App\Enums;

enum PaymentPlan: string
{
    case SINGLE = 'single';
    case INSTALLMENT = 'installment';

    public function label(): string
    {
        return match ($this) {
            self::SINGLE => 'Single',
            self::INSTALLMENT => 'Installment',
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
