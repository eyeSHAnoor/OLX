<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class InternalOrderData extends Data
{
    public function __construct(
        public ?int $id,
        public ?string $order_number,
        public ?int $branch_id = null,
        public ?string $delivery_type = null,
        public ?int $customer_id = null,
        public ?string $customer_name = null,
        public ?string $status = null,
        public ?string $warehouse_comment = null,
        public ?int $created_by = null,
        public ?int $confirmed_by = null,
        /** @var InternalOrderItemData[]|null */
        public ?array $items = null,
        /** @var ShipOrderData[]|null */
        public ?array $ship_orders = null,
        public ?string $created_at = null,
        public ?string $updated_at = null,
        public ?CustomerData $customer,
    ) {
    }
}
