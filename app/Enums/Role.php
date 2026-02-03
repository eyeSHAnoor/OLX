<?php

namespace App\Enums;

enum Role: string
{
    case SupperAdmin = 'super_admin';
    case Admin = 'admin';
    case Staff = 'staff';
    case Finance = 'finance';
    case Instructor = 'instructor';
    case Participant = 'participant';
    case Auditor = 'auditor';
    case Manager = 'Manager';

    case User = 'user';
    case Receptionist = 'receptionist';

    public function label(): string
    {
        return match ($this) {
            self::SupperAdmin => 'Super Admin',
            self::Admin => 'Admin',
            self::Staff => 'Staff',
            self::Finance => 'Finance',
            self::Instructor => 'Instructor',
            self::Participant => 'Participant',
            self::Auditor => 'Auditor',
            self::Manager => 'Manager',
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
