<?php

namespace App\Data;

use App\Models\Plan;
use Spatie\LaravelData\Data;

class PlanData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public float $price,
        public int $duration_days,

        public ?string $description,
        public ?array $features,
        public bool $is_popular,
        public int $sort_order,

        public float $daily_price,

        public string $created_at,
        public string $updated_at,
    ) {}

    public static function fromModel(Plan $plan): self
    {
        return new self(
            id: $plan->id,
            name: $plan->name,

            price: (float) $plan->price,
            duration_days: $plan->duration_days,

            description: $plan->description,
            features: $plan->features,

            is_popular: (bool) $plan->is_popular,
            sort_order: $plan->sort_order,

            daily_price: round($plan->daily_price, 2),

            created_at: $plan->created_at->toDateTimeString(),
            updated_at: $plan->updated_at->toDateTimeString(),
        );
    }
}
