<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Illuminate\Database\Eloquent\Collection;


#[TypeScript]
class WarehousePositionData extends Data
{
    public function __construct(
        public ?int $id,

        // public ?int $warehouse_id,
        public ?string $code,
        public ?string $zone,
        public ?string $type,
        public ?int $capacity,

        public ?string $created_at,
        public ?string $updated_at,

            // public ?WarehouseData $warehouse,

        #[DataCollectionOf(PutawayData::class)]
        public ?Collection $putaways,
    ) {
    }
}
