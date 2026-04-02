<?php

namespace App\Data;

use App\Models\BrandModel;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class BrandModelData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public int $brand_id
    ) {}

    public static function fromModel(BrandModel $model): self
    {
        return new self(
            id: $model->id,
            name: $model->name,
            brand_id: $model->brand_id,
        );
    }
}