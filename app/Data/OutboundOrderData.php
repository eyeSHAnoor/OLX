<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class OutboundOrderData extends Data
{
    public function __construct(
        public ?int $id,
        public ?string $order_number,
        public ?int $customer_id = null,
        public ?string $customer_name = null,
        public ?string $status = null,
        public ?int $items_count = null,
        public ?string $vehicle = null,
        public ?string $notes = null,
        public ?string $order_date = null,
        public ?int $employer_id = null,
        /** @var UserData[]|null */
        public ?array $employer = null,
        /** @var CustomerData[]|null */
        public ?array $customer = null,
        /** @var OutboundOrderItemData[]|null */
        public ?array $items = null,
        public ?int $created_by = null,
        public ?int $updated_by = null,
        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {
    }

    /**
     * Create OutboundOrderData from model
     */
    public static function fromModel(\App\Models\OutboundOrder $order): self
    {
        return new self(
            id: $order->id,
            order_number: $order->order_number,
            customer_id: $order->customer_id,
            customer_name: $order->customer_name,
            status: $order->status,
            vehicle: $order->vehicle,
            notes: $order->notes,
            order_date: $order->order_date?->format('Y-m-d H:i:s'),
            employer_id: $order->employer_id,
            employer: $order->employer ? [UserData::fromModel($order->employer)] : null,
            customer: $order->customer ? [CustomerData::fromModel($order->customer)] : null,
            items_count: $order->items?->count(),
            items: $order->items?->map(fn($item) => OutboundOrderItemData::fromModel($item))->toArray(),
            created_by: $order->created_by,
            updated_by: $order->updated_by,
            created_at: $order->created_at?->format('Y-m-d H:i:s'),
            updated_at: $order->updated_at?->format('Y-m-d H:i:s'),
        );
    }
}
