<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class TecdocArticleData extends Data
{
    public function __construct(
        public ?int $id,
        public ?string $article_id,
        public ?string $article_no,
        public ?string $supplier_name,
        public ?string $article_product_name,
        public ?string $created_at,
        public ?string $updated_at,
    ) {
    }
}