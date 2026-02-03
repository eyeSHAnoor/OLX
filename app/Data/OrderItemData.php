<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript] // ✅ Only once on the class
class OrderItemData extends Data
{
    public function __construct(
        public ?int $id,
        public ?int $inbound_order_id,
        public ?int $product_id = null,
        public ?string $unique_id = null,
        public ?string $sku = null,
        public ?string $category = null,
        public ?string $brand = null,
        public ?string $item_name = null,
        public ?string $barcode = null,
        public ?string $supplies_barcode = null,
        public ?string $rfid_barcode = null,
        public ?float $qty = 0,
        public ?float $received_qty = 0,
        public ?string $unit = null,
        public ?float $purchase_price = 0,
        public ?float $rebate = 0,
        public ?float $markup = 0,
        public ?float $tax = 0,
        public ?string $status,
        public ?float $sales_price = 0,
        public ?float $max_discount = 0,
        public ?string $kpd_code = null,
        public ?float $net_purchase_price = 0,
        public ?float $total_price = 0,
        public ?string $received_at = null,
        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {
    }
}
