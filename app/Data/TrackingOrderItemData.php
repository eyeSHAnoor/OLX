<?php

namespace App\Data;

use App\Models\TrackingOrderItem;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class TrackingOrderItemData extends Data
{
    public function __construct(
        public ?int $id,
        public ?int $tracking_order_id,
        public ?string $item_name,
        public ?string $sku,
        public ?int $quantity,
        public ?int $delivered_qty,
        public ?string $customer_type,
        public ?string $customer_name,
        public ?int $price,
        public ?string $po_number,
        public ?string $status,
        public ?string $remarks,
        public ?string $shipped_at,
        public ?string $delivered_at,
        public ?int $created_by = null,
        public ?string $created_by_name = null,
        public ?string $created_at = null,
        public ?string $updated_at = null,

        // ✅ Add product data
        public ?array $product = null,
    ) {}

    public static function fromModel(TrackingOrderItem $item): self
    {
        return new self(
            id: $item->id,
            tracking_order_id: $item->tracking_order_id,
            item_name: $item->item_name,
            sku: $item->sku,
            quantity: $item->quantity,
            delivered_qty: $item->delivered_qty,
            customer_type: $item->customer_type,
            customer_name: $item->customer_name,
            price: $item->price,
            po_number: $item->po_number,
            status: ucfirst($item->status),
            shipped_at: optional($item->shipped_at)->format('Y-m-d H:i'),
            delivered_at: optional($item->delivered_at)->format('Y-m-d H:i'),
            remarks: $item->remarks,
            created_by: $item->created_by,
            created_by_name: $item->creator?->name,
            created_at: optional($item->created_at)->format('Y-m-d H:i'),
            updated_at: optional($item->updated_at)->format('Y-m-d H:i'),

            // ✅ Map product if exists
            product: $item->product ? [
                'id' => $item->product->id,
                'name' => $item->product->name,
                'sku' => $item->product->sku,
                'category_id' => $item->product->category_id,
                'quantity' => $item->product->quantity,
                'status' => $item->product->status,
            ] : null,
        );
    }
}
