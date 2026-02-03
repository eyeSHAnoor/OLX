<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Illuminate\Database\Eloquent\Collection;


#[TypeScript]
class WarehouseData extends Data
{
    public function __construct(
        public ?int $id,

        public ?int $merchant_id,
        public ?string $code,
        public ?string $name,
        public ?string $address,
        public ?string $city,
        public ?string $country,
        public ?string $contact_person,
        public ?string $contact_phone,

        public ?FileData $file,


        #[DataCollectionOf(WarehousePositionData::class)]
        public ?Collection $positions,

        public ?string $created_at,
        public ?string $updated_at,
    ) {
    }
}
