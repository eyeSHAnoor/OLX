<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class CampaignGiftData extends Data
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $gift_period_id,
        public readonly int $gift_id,
        public readonly int $allocated_quantity,
        public readonly int $remaining_quantity,
        public readonly ?string $notes,
        public readonly ?string $created_at,
        public readonly ?string $updated_at,
        public readonly ?GiftData $gift,
    ) {}
}