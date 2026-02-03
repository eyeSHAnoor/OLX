<?php

namespace App\Data;

use App\Models\StockMovement;
use Spatie\LaravelData\Data;

class StockMovementData extends Data
{
    public function __construct(
        public ?int $id,
        public int $product_id,
        public ?string $product_name,
        public ?string $product_sku,
        public string $type, // in/out
        public int $quantity,
        public ?int $reserved_quantity,
        public ?int $total_quantity,
        public ?int $inbound_order_item_id,
        public ?int $outbound_order_item_id,
        public ?int $warehouse_location_id,
        public ?string $warehouse_code,
        public ?string $warehouse_zone,
        public ?string $movement_date,
        public ?string $remarks,
        public ?string $user_name,
    ) {
    }

    public static function fromModel(StockMovement $movement): self
    {
        return new self(
            id: $movement->id,
            product_id: $movement->product_id,
            product_name: $movement->product?->name,
            product_sku: $movement->product?->sku,
            type: $movement->type,
            quantity: $movement->quantity,
            reserved_quantity: $movement->reserved_quantity,
            total_quantity: $movement->total_quantity,
            inbound_order_item_id: $movement->inbound_order_item_id,
            outbound_order_item_id: $movement->outbound_order_item_id,
            warehouse_location_id: $movement->warehouse_location_id,
            warehouse_code: $movement->warehouseLocation?->code,
            warehouse_zone: $movement->warehouseLocation?->zone,
            movement_date: $movement->movement_date?->format('Y-m-d H:i'),
            remarks: $movement->remarks,
            user_name: $movement->user?->name,
        );
    }
}
