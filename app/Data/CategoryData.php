<?php

namespace App\Data;

use App\Models\Warehouse;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Illuminate\Database\Eloquent\Collection;


#[TypeScript]
class CategoryData extends Data
{
    public function __construct(
        public ?int $id,

        // public ?int $parent_id,
        // public ?int $merchant_id,
        public ?string $name,
        public ?string $description,

        // public ?string $created_at,
        // public ?string $updated_at,

        // public ?CategoryData $parent,

        // #[DataCollectionOf(CategoryData::class)]
        // public ?Collection $children,

        // #[DataCollectionOf(CategoryData::class)]
        // public ?Collection $childrenRecursive,

    ) {

    }
}
