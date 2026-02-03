<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Illuminate\Database\Eloquent\Collection;


#[TypeScript]
class UserPreferencesData extends Data
{
    public function __construct(
        public ?int    $id,

        public ?int    $user_id,
        public ?string $language,
        public ?string $timezone,
        public ?string $date_format,
        public ?string $currency,

        public ?string $created_at,
        public ?string $updated_at,

    )
    {
    }
}
