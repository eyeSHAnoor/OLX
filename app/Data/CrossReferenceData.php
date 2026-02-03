<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class CrossReferenceData extends Data
{
    public function __construct(
        public ?int $id,
        public ?int $product_id,
        public ?string $cross_reference_id,
        public ?TecdocArticleData $tecdoc_article,
        public ?string $created_at,
        public ?string $updated_at,
    ) {
    }
}