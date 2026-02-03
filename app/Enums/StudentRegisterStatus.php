<?php

namespace App\Enums;

enum StudentRegisterStatus: int
{
    case Completed = 1;
    case Pending = 0;

    public function label(): string
    {
        return match ($this) {
            self::Completed => 'Completed',
            self::Pending => 'Pending',
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
