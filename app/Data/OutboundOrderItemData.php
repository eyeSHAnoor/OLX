<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class OutboundOrderItemData extends Data
{
    public function __construct(
        public ?int $id,
        public ?int $product_id,
        public ?string $product_name = null,
        public ?string $product_brand = null,
        public ?string $product_sku = null,
        public ?int $qty = null,
        public ?string $barcode = null,
        public ?int $shipped_qty = null,
        public ?int $available_qty = null,
        public ?int $price = null,
        public ?string $status = null,
        public ?string $warehouse_locations = null, // optional: JSON/string of picked locations
    ) {
    }

    public static function fromModel(\App\Models\OutboundOrderItem $item): self
    {
        // If using Inventory table to track movement, you can fetch locations:
        // $locations = $item->product?->inventories()
        //     ->where('type', 'OUT')
        //     ->where('remarks', $item->outboundOrder?->order_number)
        //     ->pluck('warehouse_location_id')
        //     ->toArray();

        return new self(
            id: $item->id,
            product_id: $item->product_id,
            product_name: $item->product?->name,
            product_sku: $item->product?->sku,
            qty: $item->qty,
            barcode: $item->barcode,
            product_brand: $item->brand,
            price: $item->price,
            status: $item->status,
            shipped_qty: $item->shipped_qty,
            available_qty: $item->available_qty,
            // warehouse_locations: $locations ? json_encode($locations) : null,
        );
    }
}
