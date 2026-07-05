<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\DataCollection;

class GiftPeriodData extends Data
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly ?string $start_date,
        public readonly ?string $end_date,
        public readonly bool $is_active,
        public readonly ?string $created_at,
        public readonly ?string $updated_at,
        public readonly ?int $assignments_count,
        /** @var DataCollection<CampaignGiftData> */
        public readonly ?DataCollection $campaign_gifts,
        /** @var DataCollection<GiftAssignmentData> */
        public readonly Lazy|DataCollection|null $assignments,
    ) {}
}