<?php

namespace App\Data;

use App\Models\Ad;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class AdImageData extends Data
{
    public function __construct(
        public int $id,
        public string $image_path,
    ) {}

    public static function fromModel($image): self
    {
        return new self(
            id: $image->id,
            image_path: $image->image_path
        );
    }
}
