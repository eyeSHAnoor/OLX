<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;


#[TypeScript]
class FileData extends Data
{
    public function __construct(
        public ?int    $id,
        public ?int    $fileable_id,
        public ?string $fileable_type,
        public ?string $file_location,
        public ?string $file_url,
        public ?string $collection,
        public ?string $file_name,
        public ?string $created_at,
        public ?string $updated_at,
    )
    {
    }
}
