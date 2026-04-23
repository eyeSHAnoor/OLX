<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'endpoint' => 'required',
            'keys.p256dh' => 'required',
            'keys.auth' => 'required',
        ]);

        $user = $request->user();

        // Save subscription
        $user->updatePushSubscription(
            $request->endpoint,
            $request->keys['p256dh'],
            $request->keys['auth']
        );

        return response()->json([
            'message' => 'Subscribed successfully'
        ]);
    }
}