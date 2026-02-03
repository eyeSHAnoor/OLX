<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use App\Models\InternalOrderHistory;

#[TypeScript]
class InternalOrderItemHistoryData extends Data
{
    public function __construct(
        public ?int $id,
        public ?int $internal_order_item_id = null,
        public ?string $order_number = null,
        public ?string $product_name = null,
        public ?int $quantity = null,
        public ?string $status = null,
        public ?string $message = null,
        public ?int $action_by = null,
        public ?string $action_by_name = null,
        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {
    }

    public static function fromModel(InternalOrderHistory $history): self
    {
        return new self(
            id: $history->id,
            internal_order_item_id: $history->internal_order_item_id,
            order_number: $history->order_number,
            product_name: $history->product_name,
            quantity: $history->quantity,
            status: $history->status,
            message: $history->message,
            action_by: $history->action_by,
            action_by_name: optional($history->actionByUser)->name,
            created_at: optional($history->created_at)->format('Y-m-d H:i'),
            updated_at: optional($history->updated_at)->format('Y-m-d H:i'),
        );
    }
}
