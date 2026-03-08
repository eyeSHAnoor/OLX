<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserReport;

class UserReportPolicy
{
    /**
     * Determine whether the user can view any reports.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin(); // Assuming you have an isAdmin method
    }

    /**
     * Determine whether the user can view the report.
     */
    public function view(User $user, UserReport $userReport): bool
    {
        return $user->isAdmin() || $user->id === $userReport->reported_by;
    }

    /**
     * Determine whether the user can create reports.
     */
    public function create(User $user): bool
    {
        return true; // Any authenticated user can create reports
    }

    /**
     * Determine whether the user can update the report.
     */
    public function update(User $user, UserReport $userReport): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the report.
     */
    public function delete(User $user, UserReport $userReport): bool
    {
        return $user->isAdmin();
    }
}