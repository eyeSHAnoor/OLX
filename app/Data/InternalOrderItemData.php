<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use App\Models\InternalOrderItem;

#[TypeScript]
class InternalOrderItemData extends Data
{
    public function __construct(
        public ?int $id,
        public ?string $product_name,
        public ?string $catalog_number,
        public ?float $quantity,
        public ?float $received_quantity,
        public ?float $confirmed_quantity,
        public ?float $correction_quantity,
        public ?float $undelivered_quantity,
        public ?string $status,
        public ?string $unit,
        public ?string $comments,
        public ?string $customer_name,
        public ?string $created_at = null,
        public ?string $updated_at = null,
        /** @var InternalOrderItemHistoryData[]|null */
        public ?array $history = null,
        public ?int $created_by = null,
        public ?string $created_by_name = null
    ) {
    }

    public static function fromModel(InternalOrderItem $item): self
    {
        return new self(
            id: $item->id,
            product_name: $item->product_name,
            catalog_number: $item->catalog_number,
            quantity: $item->quantity,
            received_quantity: $item->received_quantity,
            confirmed_quantity: $item->confirmed_quantity,
            correction_quantity: $item->correction_quantity,
            undelivered_quantity: $item->undelivered_quantity,
            unit: $item->unit,
            status: ucfirst($item->status),
            comments: $item->comments,
            customer_name: $item->customer_name,
            created_at: optional($item->created_at)->format('Y-m-d H:i'),
            updated_at: optional($item->updated_at)->format('Y-m-d H:i'),
            history: $item->histories?->map(fn($h) => InternalOrderItemHistoryData::fromModel($h))->toArray(),
            created_by: $item->created_by,
            created_by_name: $item->creator?->name
        );
    }
}
