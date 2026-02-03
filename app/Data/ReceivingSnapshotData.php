<?php
namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class ReceivingSnapshotData extends Data
{
    public function __construct(
        public ?int $id,
        public ?int $quantity,
        public ?string $sku,
        public ?string $product_name,
        public ?string $order_number,
        public ?string $barcode,
        public ?int $product_id = null,
        public ?int $order_id = null,

        // Foreign key column
        public ?int $inbound_order_item = null,

        // Relationship property
        public ?OrderItemData $order_item = null,

        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {
    }

    // Map from Eloquent model to Data object
    public static function fromModel(\App\Models\ReceivingSnapshot $snapshot): self
    {
        return new self(
            id: $snapshot->id,
            quantity: $snapshot->quantity,
            sku: $snapshot->sku,
            product_name: $snapshot->product_name,
            order_number: $snapshot->order_number,
            barcode: $snapshot->barcode,
            product_id: $snapshot->product_id,
            order_id: $snapshot->order_id,
            inbound_order_item: $snapshot->inbound_order_item, // integer FK
            order_item: $snapshot->inboundOrderItem ? OrderItemData::from($snapshot->inboundOrderItem) : null,
            created_at: $snapshot->created_at?->toDateTimeString(),
            updated_at: $snapshot->updated_at?->toDateTimeString(),
        );
    }
}
