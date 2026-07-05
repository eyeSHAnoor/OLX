<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

class GiftAssignmentData extends Data
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $gift_period_id,
        public readonly int $gift_id,
        public readonly int $user_id,
        public readonly int $assigned_by,
        public readonly ?string $assigned_at,
        public readonly string $status,
        public readonly ?string $notes,
        public readonly ?string $created_at,
        public readonly ?string $updated_at,
        public readonly Lazy|GiftData|null $gift,
        public readonly Lazy|UserData|null $user,
        public readonly Lazy|UserData|null $assignedBy,
    ) {}
}