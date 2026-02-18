<?php
// app/Policies/SubscriptionPolicy.php

namespace App\Policies;

use App\Models\User;
use App\Models\Subscription;

class SubscriptionPolicy
{
    public function view(User $user, Subscription $subscription)
    {
        // Admin can view any receipt
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        // Users can only view their own receipts
        return $user->id === $subscription->user_id;
    }
}