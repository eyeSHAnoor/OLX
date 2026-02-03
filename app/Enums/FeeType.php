<?php

namespace App\Enums;

enum FeeType: string
{
    case LOCAL = 'local';
    case FOREIGN = 'foreign';

    public function label(): string
    {
        return match ($this) {
            self::LOCAL => 'Local',
            self::FOREIGN => 'Foreign',
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
