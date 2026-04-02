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
    private Brand $model;

    public function __construct(
        public int $id,
        public string $name,
        Brand $model
    ) {
        $this->model = $model;
    }

    public static function fromModel(Brand $brand): self
    {
        return new self(
            id: $brand->id,
            name: $brand->name,
            model: $brand,
        );
    }

    #[Computed]
    #[DataCollectionOf(CategoryData::class)]
    public function categories(): array
    {
        return $this->model->categories
            ->map(fn ($category) => CategoryData::fromModel($category))
            ->toArray();
    }

    // ADD THIS
    #[Computed]
    #[DataCollectionOf(BrandModelData::class)]
    public function models(): array
    {
        return $this->model->models
            ->map(fn ($model) => BrandModelData::fromModel($model))
            ->toArray();
    }
}