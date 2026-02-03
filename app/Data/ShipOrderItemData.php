<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class ShipOrderItemData extends Data
{
    public function __construct(
        public ?int $id,
        public ?int $ship_order_id,
        public ?int $internal_order_item_id,
        public ?string $product_name,
        public ?int $confirmed_quantity_to_ship,
        public ?string $type,
        public ?string $customer_name,
        public ?string $created_at,
        public ?string $updated_at,
    ) {
    }
}
