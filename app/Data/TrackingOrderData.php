<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use App\Models\TrackingOrder;

#[TypeScript]
class TrackingOrderData extends Data
{
    public function __construct(
        public ?int $id,
        public ?string $order_number,
        public ?string $comments,
        public ?string $supplier,
        public ?string $status,
        public ?string $payment_status,
        public ?string $parcel_status,
        public ?int $created_by,
        public ?string $created_by_name = null,

        /** @var TrackingOrderItemData[]|null */
        public ?array $items = null,

        /** @var CarrierData[]|null */
        public ?array $carriers = null,

        /** @var array|null */
        public ?array $files = null,

        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {
    }

    public static function fromModel(TrackingOrder $order): self
    {
        return new self(
            id: $order->id,
            order_number: $order->order_number,
            comments: $order->comments,
            supplier: $order->supplier,
            status: $order->status,
            payment_status: $order->payment_status,
            parcel_status: $order->parcel_status,
            created_by: $order->created_by,
            created_by_name: $order->creator?->name,
            items: $order->items?->map(fn($item) => TrackingOrderItemData::fromModel($item))->toArray(),
            carriers: $order->carriers?->map(fn($carrier) => CarrierData::fromModel($carrier))->toArray(),
            files: $order->files?->map(fn($file) => [
                'id' => $file->id,
                'file_name' => $file->file_name,
                'url' => $file->file_location ? asset('storage/' . $file->file_location) : null,
                'collection' => $file->collection,
                'created_at' => optional($file->created_at)->format('Y-m-d H:i'),
            ])->toArray(),
            created_at: optional($order->created_at)->format('Y-m-d H:i'),
            updated_at: optional($order->updated_at)->format('Y-m-d H:i'),
        );
    }
}
