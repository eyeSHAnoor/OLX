<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class ProductData extends Data
{
    public function __construct(
        public ?int $id,
        public ?string $sku,
        public ?string $name,
        public ?string $description,
        public ?string $brand,
        public ?string $status,
        public ?string $match_type,
        public ?int $category_id,
        public ?int $warehouse_position_id,
        // public ?string $category,
        public ?float $quantity,
        public ?string $ean,
        public ?string $unit,
        public ?float $tax,
        public ?string $barcode,
        public ?float $low_stock,
        public ?float $price,
        public ?float $max_discount,
        public ?string $created_at = null,
        public ?string $updated_at = null,
        public ?array $article_details,

        public ?array $category,

        /** @var DataCollection<OrderItemData> */
        public ?DataCollection $order_items,

        /** @var DataCollection<CrossReferenceData> */
        public ?DataCollection $cross_references,

        /** @var DataCollection<OemReferenceData> */
        public ?DataCollection $oem_references,

        /** @var DataCollection<InventoryData> */
        public ?DataCollection $inventories,

        public ?FileData $file,
        public ?CategoryData $categoryData,
    ) {
    }

    public static function fromModel($product): self
    {
        return new self(
            id: $product->id,
            sku: $product->sku,
            name: $product->name,
            description: $product->description,
            brand: $product->brand,
            status: $product->status,
            match_type: $product->match_type ?? 'general',
            category_id: $product->category_id,
            warehouse_position_id: $product->warehouse_position_id,
            // category: $product->category,
            quantity: $product->quantity !== null ? (float) $product->quantity : null,
            ean: $product->ean,
            unit: $product->unit,
            price: $product->price !== null ? (float) $product->price : null,
            barcode: $product->barcode,
             low_stock: $product->low_stock !== null ? (float) $product->low_stock : null,
            tax: $product->tax !== null ? (float) $product->tax : null,
            max_discount: $product->max_discount !== null ? (float) $product->max_discount : null,
            created_at: $product->created_at?->toDateTimeString(),
            updated_at: $product->updated_at?->toDateTimeString(),
            article_details: $product->article_details,
            order_items: $product->relationLoaded('orderItems') && $product->orderItems
            ? OrderItemData::collect($product->orderItems)
            : null,
            cross_references: $product->relationLoaded('crossReferences') && $product->crossReferences
            ? CrossReferenceData::collect($product->crossReferences)
            : null,
            oem_references: $product->relationLoaded('oemReferences') && $product->oemReferences
            ? OemReferenceData::collect($product->oemReferences)
            : null,
            inventories: $product->relationLoaded('inventories') && $product->inventories
            ? InventoryData::collect($product->inventories)
            : null,
            file: $product->relationLoaded('file') && $product->file
            ? FileData::from($product->file)
            : null,
            categoryData: $product->relationLoaded('categoryData') && $product->categoryData
            ? CategoryData::from($product->categoryData)
            : null,
            category: $product->categoryData
                ? $product->categoryData->only([
                    'id',
                    'name',
                    'slug',
                    'status',
                    'created_at',
                ])
                : null,
        );
    }
}