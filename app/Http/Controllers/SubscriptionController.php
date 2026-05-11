<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\JazzCashService;
use App\Models\Plan;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Support\Facades\Route;
use App\Notifications\NewManualSubscriptionNotification;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    // Show subscription page with plans
    public function index()
    {
        $plans = Plan::orderBy('sort_order')->get();

        return Inertia::render('home/Subscription', [
            'plans' => $plans
        ]);
    }

    public function submitManual(Request $request)
    {
        $user = auth()->user();

        // Check if user already has an active subscription
        $activeSubscription = $user->subscription; // your User model has `subscription()`
        // dd($activeSubscription);
        if ($activeSubscription && $activeSubscription->isActive()) {
            return redirect()->back()->with('error', 'You already have an active subscription.');
        }
        if ($activeSubscription && $activeSubscription->isPending()) {
            return redirect()->back()->with('error', 'You already have a pending subscription. Please wait for admin response.');
        }

        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'payment_method' => 'required|string',
            'receipt' => 'required|image|max:2048',
        ]);

        $plan = Plan::findOrFail($request->plan_id);

        $receipt = $request->file('receipt')->store(
            'receipts/user_'.$user->id.'/'.now()->format('Y_m'),
            'private'
        );

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'payment_status' => 'pending',
            'payment_gateway' => 'manual',
            'payment_method' => $request->payment_method,
            'amount_paid' => $plan->price,
            'receipt_image' => $receipt,
        ]);

        $superAdmins = User::role('super_admin')->get(); // or role('super-admin')->get() if using Spatie

        if ($superAdmins->isNotEmpty()) {
            Notification::send($superAdmins, new NewManualSubscriptionNotification($user, $subscription));
        }

        return redirect()->back()->with('success', 'Payment submitted. Waiting for admin approval.');
    }

    public function complete(int $userId)
    {
        $user = User::findOrFail($userId);

        // Find the user's pending subscription
        $pendingSubscription = $user->subscription()->where('payment_status', 'pending')->first();

        if (! $pendingSubscription) {
            return redirect()->route('home')->with('error', 'No pending subscription found for this user.');
        }

        DB::transaction(function () use ($pendingSubscription, $user) {
            // Mark the subscription as active
            $pendingSubscription->update([
                'payment_status' => 'completed',
                'starts_at' => Carbon::now(),
                'ends_at' => Carbon::now()->addDays($pendingSubscription->plan->duration_days ?? 30),
            ]);

            // Update user subscription status
            $user->status = 'active';
            $user->save();
        });

        return redirect()->back()->with('success', 'User subscription has been completed and activated.');
    }

    public function reject(int $userId)
    {
        $user = User::findOrFail($userId);

        $pendingSubscription = $user->subscription()->where('payment_status', 'pending')->first();

        if (! $pendingSubscription) {
            return redirect()->back()->with('error', 'No pending subscription found for this user.');
        }

        DB::transaction(function () use ($pendingSubscription, $user) {
            // Mark subscription as rejected
            $pendingSubscription->update([
                'payment_status' => 'rejected',
            ]);

            // Update user's subscription status if no other active/pending subscriptions
            $hasOtherSubscriptions = $user->subscription()->whereIn('payment_status', ['pending', 'completed'])->exists();
            if (! $hasOtherSubscriptions) {
                $user->status = 'inactive';
                $user->save();
            }
        });

        return redirect()->back()->with('success', 'User subscription has been rejected.');
    }

    public function initiateJazzCash(Request $request)
    {
        $user = auth()->user();
        
        // Check if user already has an active subscription
        if ($user->activeSubscription()->exists()) {
            return redirect()->back()->with('error', 'You already have an active subscription.');
        }
        
        if ($user->pendingSubscription()->exists()) {
            return redirect()->back()->with('error', 'You already have a pending subscription.');
        }
        
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);
        
        $plan = Plan::findOrFail($request->plan_id);
        
        // Create pending subscription
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'payment_status' => 'pending',
            'payment_gateway' => 'jazzcash',
            'amount_paid' => $plan->price,
            'payment_data' => [
                'initiated_at' => now()
            ]
        ]);
        
        // Prepare JazzCash payment data
        $jazzCashService = app(JazzCashService::class);
        $paymentData = $jazzCashService->preparePaymentRequest(
            $plan, 
            $user, 
            $subscription->id,
            $user->phone || '03123456789'
        );

        Log::info($paymentData);
        
        // Store payment data
        $subscription->update([
            'payment_data' => array_merge($subscription->payment_data, [
                'jazzcash_request' => $paymentData
            ])
        ]);

        
        // Return view with auto-submitting form
        return Inertia::render('payment/JazzCashRedirect', [
            'paymentData' => $paymentData,
            'endpoint' => config('jazzcash.endpoints.' . config('jazzcash.environment'))
        ]);
        // return view('payment.redirect', [
        // 'endpoint' => config('jazzcash.endpoints.' . config('jazzcash.environment')),
        // 'data' => $paymentData
        // ]);
    }
}
