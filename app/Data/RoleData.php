<?php

namespace App\Data;

use Illuminate\Database\Eloquent\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;


#[TypeScript]
class RoleData extends Data
{
    public function __construct(
        public ?int $id,
        public ?string $name,
        public ?string $description,
        public ?string $guard_name,
        // public ?int $tenant_id,
        public ?int $users_count,

        #[DataCollectionOf(PermissionData::class)]
        public ?Collection $permissions,

    ) {
    }
}
