<?php

namespace App\Data;

use App\Models\Inventory;
use Spatie\LaravelData\Data;

class InventoryData extends Data
{
    public function __construct(
        public ?int $id,
        public int $product_id,
        public ?string $product_name,
        public ?string $product_sku,
        public ?int $warehouse_location_id,
        public ?string $warehouse_code,
        public ?string $warehouse_zone,
        public int $quantity,
        public ?int $reserved_quantity,
        public ?int $total_quantity,
    ) {
    }

    public static function fromModel(Inventory $inventory): self
    {
        return new self(
            id: $inventory->id,
            product_id: $inventory->product_id,
            product_name: $inventory->product?->name,
            product_sku: $inventory->product?->sku,
            warehouse_location_id: $inventory->warehouse_location_id,
            warehouse_code: $inventory->warehouseLocation?->code,
            warehouse_zone: $inventory->warehouseLocation?->zone,
            quantity: $inventory->quantity,
            reserved_quantity: $inventory->reserved_quantity,
            total_quantity: $inventory->total_quantity,
        );
    }
}
