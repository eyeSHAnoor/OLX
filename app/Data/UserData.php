<?php

namespace App\Data;

use App\Models\UserPreference;
use App\Models\UserProfile;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Illuminate\Database\Eloquent\Collection;


#[TypeScript]
class UserData extends Data
{
    #[Computed]
    public ?string $role;

    public function __construct(
        public ?int $id,
        public ?int $tenant_id,
        public ?string $name,
        public ?string $phone,
        public ?string $email,
        public ?string $email_verified_at,
        //        public ?string     $password,
        public ?string $remember_token,

        public ?string $status,

        public ?string $created_at,
        public ?string $updated_at,

        public ?string $avatar,

        #[DataCollectionOf(RoleData::class)]
        public ?Collection $roles,


        #[DataCollectionOf(PermissionData::class)]
        public ?Collection $permission,

        #[DataCollectionOf(FileData::class)]
        public ?Collection $files,

        public ?UserProfile $profile,
        public ?UserPreference $preferences,

        #[DataCollectionOf(UserNotificationSettingData::class)]
        public ?Collection $notificationSettings,

        public ?UserLoginLogData $lastLogin,


    ) {
        $this->role = $this->roles && $this->roles->isNotEmpty() ? $this->roles[0]?->name : null;
    }
}
