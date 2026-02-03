<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class ShipOrderData extends Data
{
    /**
     * @param ShipOrderItemData[]|null $items
     */
    public function __construct(
        public ?int $id,
        public ?string $shipment_id,
        public ?string $shipment_date,
        public ?int $total_items,
        public ?int $internal_order_id,
        /** @var ShipOrderItemData[]|null */
        public ?array $items,
        public ?string $created_at,
        public ?string $updated_at,
    ) {
    }
}
