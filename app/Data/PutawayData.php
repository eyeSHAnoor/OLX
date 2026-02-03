<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Illuminate\Database\Eloquent\Collection;


#[TypeScript]
class PutawayData extends Data
{
    public function __construct(
        public ?int                   $id,

        public ?int                   $asn_id,
        public ?int                   $warehouse_position_id,
        public ?int                   $putaway_by,
        public ?string                $sku_no,
        public ?string                $putaway_strategy,
        public ?string                $putaway_time,
        public ?int                   $qty,

        public ?string                $created_at,
        public ?string                $updated_at,

        public ?ASNData               $asn,
        public ?UserData              $putawayByUser,
        public ?WarehousePositionData $warehousePosition,

//        #[DataCollectionOf(FileData::class)]
//        public ?Collection $files,

    )
    {
    }
}
