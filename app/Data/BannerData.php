<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class BannerData extends Data
{
    public function __construct(
        public int $id,
        public string $title,
        public string $image_url,
        public ?string $link,
        public string $position,
        public ?int $target_category_id,
        public ?string $start_date,
        public ?string $end_date,
        public bool $status,
        public ?CategoryData $category,
        public string $created_at,
        public string $updated_at,
    ) {}
}