<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Conversation;

Broadcast::channel('notifications', function () {
    return true; // anyone can listen
});

Broadcast::channel('conversation.{id}', function ($user, $id) {
    return Conversation::where('id', $id)
        ->where(function ($q) use ($user) {
            $q->where('buyer_id', $user->id)
              ->orWhere('seller_id', $user->id);
        })->exists();
});

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Broadcast::channel('buyer.{buyerId}', function ($user, $buyerId) {
//     // Only allow the logged-in user who is the buyer
//     return (int) $user->id === (int) $buyerId;
// });

Broadcast::channel('seller.{id}', function ($user, $id) {
    return $user->id == $id;
});

Broadcast::channel('buyer.{id}', function ($user, $id) {
    return $user->id == $id;
});

Broadcast::channel('admin-broadcast', function ($user) {
    // All authenticated users can listen
    return auth()->check();
});