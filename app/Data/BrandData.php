<?php

namespace App\Data;

use App\Models\Ad;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class BrandData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
    ) {}

    public static function fromModel($brand): self
    {
        return new self(
            id: $brand->id,
            name: $brand->name
        );
    }
}