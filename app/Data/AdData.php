<?php

namespace App\Data;

use App\Models\Ad;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class AdData extends Data
{
    public function __construct(
        public int $id,
        public int $user_id,
        public int $category_id,
        public ?int $brand_id,
        public string $ad_title,
        public ?string $description,
        public ?float $price,
        public ?string $city,
        public ?string $location,
        public ?string $seller_name,
        public ?string $seller_phone,
        #[DataCollectionOf(AdImageData::class)]
        public ?\Illuminate\Support\Collection $images = null,
        public ?BrandData $brand = null,
        public ?CategoryData $category = null,
        public ?array $search_keywords = null, // ✅ added
    ) {}

    #[Computed]
    public function priceWithCurrency(): string
    {
        return 'Pkr' . number_format($this->price ?? 0, 2);
    }

    #[Computed]
    public function searchKeywordsString(): string
    {
        return implode(', ', $this->search_keywords ?? []);
    }

    public static function fromModel(Ad $ad): self
    {
        return new self(
            id: $ad->id,
            user_id: $ad->user_id,
            category_id: $ad->category_id,
            brand_id: $ad->brand_id,
            ad_title: $ad->ad_title,
            description: $ad->description,
            price: $ad->price,
            city: $ad->city,
            location: $ad->location,
            seller_name: $ad->seller_name,
            seller_phone: $ad->seller_phone,
            images: $ad->images->map(fn($img) => AdImageData::fromModel($img)),
            brand: $ad->brand ? BrandData::fromModel($ad->brand) : null,
            category: $ad->category ? CategoryData::fromModel($ad->category) : null,
            search_keywords: $ad->search_keywords, // ✅ map here
        );
    }
}
