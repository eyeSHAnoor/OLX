<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Illuminate\Database\Eloquent\Collection;


#[TypeScript]
class UserProfileData extends Data
{
    public function __construct(
        public ?int    $id,

        public ?int $user_id,
        public ?string $company_name,
        public ?string $address,
        public ?string $phone_1,
        public ?string $phone_2,
        public ?string $contact_person,
        public ?string $company_email,
        public ?string $verified_at,
        public ?string $verified_by,

        public ?string $created_at,
        public ?string $updated_at,

    )
    {
    }
}
