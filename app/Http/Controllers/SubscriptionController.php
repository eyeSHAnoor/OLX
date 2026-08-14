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
use App\Models\Referral;
use Illuminate\Support\Facades\Route;
use App\Notifications\NewManualSubscriptionNotification;
use App\Notifications\ManualSubscriptionPendingNotification;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SubscriptionCompletedNotification;
use App\Notifications\SubscriptionRejectedNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\UserReferralScore;

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
        $activeSubscription = $user->subscription;
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
            'amount_paid' => ($plan->discount > 0 && $plan->discount < $plan->price) ? $plan->discount : $plan->price,
            'receipt_image' => $receipt,
        ]);

        $superAdmins = User::role('super_admin')->get();

        if ($superAdmins->isNotEmpty()) {
            Notification::send($superAdmins, new NewManualSubscriptionNotification($user, $subscription));
        }
        $user->notify(new ManualSubscriptionPendingNotification($subscription));

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

            // =============================================
            // PROCESS REFERRAL AND AWARD POINTS
            // =============================================
            $this->processReferralAndAwardPoints($user);

            $user->notify(new SubscriptionCompletedNotification($pendingSubscription));
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

            // =============================================
            // SEND NOTIFICATIONS
            // =============================================
            // 1. Send notification to the user
            $user->notify(new SubscriptionRejectedNotification($pendingSubscription));
        });

        return redirect()->back()->with('success', 'User subscription has been rejected.');
    }

    public function cancel(int $userId)
    {
        $user = User::findOrFail($userId);

        $completedSubscription = $user->subscription()->where('payment_status', 'completed')->first();

        if (!$completedSubscription) {
            return redirect()->back()->with('error', 'No completed subscription found for this user.');
        }

        DB::transaction(function () use ($completedSubscription, $user) {
            // Mark subscription as cancelled
            $completedSubscription->update([
                'payment_status' => 'rejected',
            ]);

            // Update user's subscription status
            $hasOtherSubscriptions = $user->subscription()->whereIn('payment_status', ['pending', 'completed'])->exists();
            if (!$hasOtherSubscriptions) {
                $user->status = 'inactive';
                $user->save();
            }

            // Optional: Send notification to the user
            // $user->notify(new SubscriptionCancelledNotification($completedSubscription));
        });

        return redirect()->back()->with('success', 'User subscription has been cancelled successfully.');
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
            'amount_paid' => ($plan->discount > 0 && $plan->discount < $plan->price) ? $plan->discount : $plan->price,
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
    }

    /**
     * Process referral and award points when a user completes subscription
     * points_balance is NOT incremented - it's a fixed value assigned by admin
     */
    private function processReferralAndAwardPoints(User $newUser)
    {
        // Only first subscription triggers referral
        $previousSubscriptions = Subscription::where('user_id', $newUser->id)
            ->where('payment_status', 'completed')
            ->where('id', '!=', $newUser->subscription->id ?? 0)
            ->count();

        if ($previousSubscriptions > 0) {
            return;
        }

        // Find who referred this user
        $referral = Referral::where('referred_user_id', $newUser->id)
            ->where('status', 'registered')
            ->first();
        
        if (!$referral || !$referral->referrer) {
            return;
        }

        // Process chain starting from immediate referrer
        $this->processReferralChain($referral->referrer, $newUser, $referral->link_code);
    }

    private function processReferralChain(User $immediateReferrer, User $newUser, string $referralCode)
    {
        $level = 1;
        $current = $immediateReferrer;
        $previousAwardedPoints = 0;
        $visitedIds = [$immediateReferrer->id];
        $maxLevel = 50;

        while ($current && $level <= $maxLevel) {
            // 🛑 Check if current user is super admin - SKIP them and BREAK the chain
            if ($current->hasRole('super_admin')) {
                Log::info("Level {$level}: User {$current->id} is super admin. Skipping and breaking chain.");
                break; // Stop the chain completely when reaching super admin
            }

            if ($level === 1) {
                // Direct referrer gets their full points_balance
                $pointsToAward = $current->points_balance ?: 20;
            } else {
                // Upline gets: their points_balance - what the person BELOW actually got
                $pointsToAward = ($current->points_balance ?: 20) - $previousAwardedPoints;
                
                if ($pointsToAward < 0) {
                    $pointsToAward = 0;
                }
            }

            // Store what was ACTUALLY AWARDED at this level for the next iteration
            $previousAwardedPoints = $pointsToAward;

            // Update or create referral record
            Referral::updateOrCreate(
                [
                    'referrer_id' => $current->id,
                    'referred_user_id' => $newUser->id,
                    'level' => $level,
                ],
                [
                    'status' => 'completed',
                    'points_awarded' => $pointsToAward,
                    'link_code' => $referralCode,
                    'visited_at' => now(),
                ]
            );

            if ($pointsToAward > 0) {
                $score = UserReferralScore::firstOrCreate(
                    ['user_id' => $current->id],
                    [
                        'total_earned' => 0,
                        'total_withdrawn' => 0,
                        'available' => 0,
                        'pending' => 0,
                        'status' => 'active',
                    ]
                );
                $score->addEarnedPoints($pointsToAward);
            }

            Log::info("Level {$level}: User {$current->id} awarded {$pointsToAward} points (balance: {$current->points_balance}, previous awarded: {$previousAwardedPoints})");

            // Move up to code assigner
            $nextCurrent = $current->codeAssigner;
            
            // Prevent infinite loop
            if ($nextCurrent && in_array($nextCurrent->id, $visitedIds)) {
                Log::warning("Circular reference detected! User {$nextCurrent->id} already processed. Breaking chain.");
                break;
            }
            
            if ($nextCurrent) {
                $visitedIds[] = $nextCurrent->id;
            }
            
            $current = $nextCurrent;
            $level++;
        }
    }

    /**
     * Get the referral code used by the user during registration
     */
    private function getReferralCodeForUser(User $user): ?string
    {
        // Check if user has a referral record
        $referral = Referral::where('referred_user_id', $user->id)
            ->where('status', 'registered')
            ->first();
        
        if ($referral) {
            return $referral->link_code;
        }
        
        // If no referral record, check if user was referred by someone
        if ($user->referred_by) {
            $referrer = User::find($user->referred_by);
            if ($referrer && $referrer->referral_code) {
                return $referrer->referral_code;
            }
        }
        
        return null;
    }
  
}