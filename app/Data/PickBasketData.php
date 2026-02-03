<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class PickBasketData extends Data
{
    public function __construct(
        public ?int $id,
        public ?string $basket_number,
        public ?string $status,
        /** @var PickBasketItemData[]|null */
        public ?array $items = null,
        public ?int $items_count = null,
        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {
    }

    public static function fromModel(\App\Models\PickBasket $basket): self
    {
        return new self(
            id: $basket->id,
            basket_number: $basket->basket_number,
            status: $basket->status,
            items: $basket->items?->map(
                fn($i) => PickBasketItemData::fromModel($i)
            )->toArray(),
            items_count: $basket->items?->count() ?? 0,
            created_at: $basket->created_at?->format('Y-m-d H:i:s'),
            updated_at: $basket->updated_at?->format('Y-m-d H:i:s'),
        );
    }
}
