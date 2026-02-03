<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use App\Models\Carrier;

#[TypeScript]
class CarrierData extends Data
{
    public function __construct(
        public ?int $id,
        public ?string $tracking_id,
        public ?string $forwarder,
        public ?string $carrier,
        public ?string $expected_delivery_date,
        public ?string $shipped_at,
        public ?string $delivered_at,
        public ?int $tracking_order_id = null
    ) {
    }

    public static function fromModel(Carrier $carrier): self
    {
        return new self(
            id: $carrier->id,
            tracking_id: $carrier->tracking_id,
            forwarder: $carrier->forwarder,
            carrier: $carrier->carrier,
            expected_delivery_date: optional($carrier->expected_delivery_date)?->format('Y-m-d'),
            shipped_at: optional($carrier->shipped_at)?->format('Y-m-d'),
            delivered_at: optional($carrier->delivered_at)?->format('Y-m-d'),
            tracking_order_id: $carrier->tracking_order_id
        );
    }
}
