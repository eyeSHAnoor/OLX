<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function before(User $auth, string $ability): ?bool
    {
        if ($auth->hasRole(Role::SupperAdmin)) {
            return !! $auth->hasRole(Role::SupperAdmin);
        }

        return null; // must return null not false
    }


    public function viewAny(User $auth): bool
    {
        return !! $auth->hasRole(Role::SupperAdmin);
    }

    public function view(User $auth, User $user): bool
    {
        return !! $auth->hasRole(Role::SupperAdmin);
    }

    public function create(User $auth): bool
    {
        return !! $auth->hasRole(Role::SupperAdmin);
    }

    public function update(User $auth, User $user): bool
    {
        return !! $auth->hasRole(Role::SupperAdmin);
    }

    public function delete(User $auth, User $user): bool
    {
        return $auth->id === $user->id;
    }

    public function restore(User $auth, User $user): bool
    {
        return !! $auth->hasRole(Role::SupperAdmin);
    }

    public function forceDelete(User $auth, User $user): bool
    {
        return !! $auth->hasRole(Role::SupperAdmin);
    }
}
