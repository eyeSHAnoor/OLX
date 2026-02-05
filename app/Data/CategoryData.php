<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\DataCollection;

class CategoryData extends Data
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $slug,
        public ?int $parent_id,
        public ?int $position,
        public ?string $created_at,
        public ?string $updated_at,
        public ?CategoryData $parent,
        #[DataCollectionOf(CategoryData::class)]
        public ?DataCollection $children,
        #[DataCollectionOf(CategoryData::class)]
        public ?DataCollection $children_recursive,
        public ?string $image_url, 
    ) {}

    public static function fromModel(Category $category): self
    {
        $imageUrl = $category->files[0]->file_url ?? null;

        return new self(
            id: $category->id,
            name: $category->name,
            slug: $category->slug,
            parent_id: $category->parent_id,
            position: $category->position,
            created_at: $category->created_at?->toDateTimeString(),
            updated_at: $category->updated_at?->toDateTimeString(),
            parent: $category->parent ? self::fromModel($category->parent) : null,
            children: $category->relationLoaded('children') && $category->children->isNotEmpty()
                ? self::collect($category->children->map(fn($child) => self::fromModel($child)))
                : null,
            children_recursive: $category->relationLoaded('childrenRecursive') && $category->childrenRecursive->isNotEmpty()
                ? self::collect($category->childrenRecursive->map(fn($child) => self::fromModel($child)))
                : null,
            image_url: $imageUrl,
        );
    }

}