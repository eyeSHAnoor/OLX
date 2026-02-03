<?php

namespace App\Data;

use App\Enums\CourseStatus;
use App\Models\Student;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Illuminate\Database\Eloquent\Collection;

#[TypeScript]
class CountryData extends Data
{
    public function __construct(
        public ?string $country,
        public ?int    $dial_code,
        public ?string $country_code,
    )
    {
    }
}
