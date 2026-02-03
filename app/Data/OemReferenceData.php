<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class OemReferenceData extends Data
{
    public function __construct(
        public ?int $id,
        public ?int $product_id,
        public ?string $oem_brand,
        public ?string $oem_display_no,
        public ?string $oem_article_no,
        public ?string $created_at,
        public ?string $updated_at,
    ) {
    }
}