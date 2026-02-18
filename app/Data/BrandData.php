<?php

namespace App\Data;

use App\Models\Brand;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class BrandData extends Data
{
    // Add a private property to hold the original model
    private Brand $model;

    public function __construct(
        public int $id,
        public string $name,
        Brand $model // pass the model here
    ) {
        $this->model = $model;
    }

    public static function fromModel(Brand $brand): self
    {
        return new self(
            id: $brand->id,
            name: $brand->name,
            model: $brand, // store the model
        );
    }

    #[Computed]
    #[DataCollectionOf(CategoryData::class)]
    public function categories(): array
    {
        // map categories using CategoryData
        return $this->model->categories
            ->map(fn($category) => CategoryData::fromModel($category))
            ->toArray();
    }
}
