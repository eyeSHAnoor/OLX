<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Illuminate\Database\Eloquent\Collection;


#[TypeScript]
class UserLoginLogData extends Data
{
    public function __construct(
        public ?int      $id,

        public ?int      $user_id,
        public ?string   $ip_address,
        public ?string   $device,
        public ?string   $user_agent,

        public ?string   $created_at,
        public ?string   $updated_at,

        public ?UserData $user,

    )
    {
    }
}
