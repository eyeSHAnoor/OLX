<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class PickBasketItemData extends Data
{
    public function __construct(
        public ?int $id,
        public ?int $pick_basket_id,
        public ?int $product_id,
        public ?string $sku,
        public ?string $item_name,
        public ?int $qty,
        /** @var int[]|null */
        public ?array $warehouse_location_ids = null,
        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {
    }

    public static function fromModel(\App\Models\PickBasketItem $item): self
    {
        return new self(
            id: $item->id,
            pick_basket_id: $item->pick_basket_id,
            product_id: $item->product_id,
            sku: $item->sku,
            item_name: $item->item_name,
            qty: $item->qty,
            warehouse_location_ids: $item->warehouse_location_ids,
            created_at: $item->created_at?->format('Y-m-d H:i:s'),
            updated_at: $item->updated_at?->format('Y-m-d H:i:s'),
        );
    }
}
