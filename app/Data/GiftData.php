<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class GiftData extends Data
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?string $image,
        public readonly int $quantity,
        public readonly bool $is_active,
        public readonly ?string $created_at,
        public readonly ?string $updated_at,
    ) {}
}