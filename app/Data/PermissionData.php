<?php

namespace App\Data;

use Illuminate\Database\Eloquent\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;


#[TypeScript]
class PermissionData extends Data
{
    public function __construct(
        public ?int $id,
        public ?string $name,
        public ?string $guard_name,
        public ?string $group,

        #[DataCollectionOf(RoleData::class)]
        public ?Collection $roles,

    ) {
    }
}
