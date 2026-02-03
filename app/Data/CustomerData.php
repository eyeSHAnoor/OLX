<?php

namespace App\Data;

use Illuminate\Database\Eloquent\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class CustomerData extends Data
{
    public function __construct(
        public ?int $id,

        // Basic Info
        public ?string $name,
        public ?string $email,
        public ?string $phone,

        // Company Details
        public ?string $company_name,
        public ?string $tax_id,

        // Address Info
        public ?string $address,
        public ?string $city,
        public ?string $state,
        public ?string $country,
        public ?string $postal_code,

        // Optional link to User
        public ?int $user_id,

        public ?string $created_at,
        public ?string $updated_at,

            // Related Data (e.g. internal orders)
        #[DataCollectionOf(InternalOrderData::class)]
        public ?Collection $internal_orders,
    ) {
    }
}
