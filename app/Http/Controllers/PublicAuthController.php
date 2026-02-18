<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PublicLoginRequest;
use App\Http\Requests\Auth\PublicRegisterRequest;
use App\Http\Requests\Auth\PublicResetPasswordRequest;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\JazaCashService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PublicAuthController extends Controller
{
    private $jazaCashService;

    public function __construct(JazaCashService $jazaCashService)
    {
        $this->jazaCashService = $jazaCashService;
    }

    /**
     * Show AMO login form
     */
    public function create(Request $request): Response
    {
        return Inertia::render('public_auth/Login', [
            'canResetPassword' => Route::has('amo.password.request'),
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle AMO login
     */
    public function login(PublicLoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        // Redirect to appropriate dashboard based on user type
        if ($request->user()->hasActiveSubscription()) {
            return redirect()->intended(route('home'));
        }

        // If user has no active subscription, redirect to plans page
        return redirect()->route('amo.plans.show')
            ->with('info', 'Please subscribe to a plan to access all features.');
    }

    /**
     * Show AMO registration form with plans
     */
    public function register(Request $request): Response
    {
        $selectedPlanId = $request->session()->get('selected_plan');
        $selectedPlan = null;
        
        if ($selectedPlanId) {
            $selectedPlan = Plan::find($selectedPlanId);
        }

        return Inertia::render('public_auth/Register', [
            'plans' => Plan::orderBy('sort_order')
                ->orderBy('price')
                ->get(),
            'selectedPlan' => $selectedPlan,
        ]);
    }

    /**
     * Show all available plans
     */
    public function showPlans(): Response
    {
        return Inertia::render('public_auth/Plans', [
            'plans' => Plan::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('price')
                ->get(),
        ]);
    }

    /**
     * Select a plan and store in session
     */
    public function selectPlan(Request $request, Plan $plan): RedirectResponse
    {
        $request->session()->put('selected_plan', $plan->id);
        $request->session()->put('plan_amount', $plan->price);

        return redirect()->route('amo.register');
    }

    /**
     * Handle AMO registration with payment
     */
    public function store(PublicRegisterRequest $request)
    {
        // dd($request);
        $plan = Plan::find($request->plan_id);
        
        if (!$plan) {
            return redirect()->route('amo.plans.show')
                ->with('error', 'Please select a plan to continue.');
        }

        // Step 1: Create user (temporarily without subscription)
        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'user_type' => 'amo', // Mark as AMO user
            ]);

            return $user;
        });

        // Step 2: Initiate payment with JazaCash
        $paymentData = [
            'amount' => $plan->price,
            'email' => $user->email,
            'phone' => $user->phone,
            'order_id' => 'SUB-' . $user->id . '-' . time(),
            'description' => 'Subscription to ' . $plan->name . ' Plan',
        ];

        $paymentResponse = $this->jazaCashService->initiatePayment($paymentData);

        if ($paymentResponse['success']) {
            // Create pending subscription
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'payment_status' => 'pending',
                'transaction_id' => $paymentResponse['transaction_id'],
                'payment_gateway' => 'jazacash',
                'amount_paid' => $plan->price,
                'payment_data' => $paymentResponse,
            ]);

            // Store subscription ID in session for callback
            $request->session()->put('pending_subscription_id', $subscription->id);

            // Redirect to payment gateway
            return Inertia::location($paymentResponse['payment_url']);
        }

        // If payment initiation fails, delete the user and show error
        $user->delete();
        
        return back()->withErrors([
            'payment' => 'Payment initiation failed: ' . ($paymentResponse['error'] ?? 'Unknown error'),
        ]);
    }

    /**
     * Handle free registration (without plan)
     */
    public function storeFree(PublicRegisterRequest $request)
    {
        // Create user with free plan
        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'user_type' => 'amo',
            ]);

            // Create free subscription
            $freePlan = Plan::where('price', 0)->first();
            if ($freePlan) {
                Subscription::create([
                    'user_id' => $user->id,
                    'plan_id' => $freePlan->id,
                    'payment_status' => 'completed',
                    'starts_at' => now(),
                    'ends_at' => now()->addDays($freePlan->duration_days),
                ]);
            }

            return $user;
        });

        // Auto login after registration
        Auth::login($user);

        return redirect()->route('home')
            ->with('success', 'Registration successful! Welcome to your dashboard.');
    }

    /**
     * Payment callback from JazaCash
     */
    public function paymentCallback(Request $request)
    {
        $subscriptionId = $request->session()->get('pending_subscription_id');
        $transactionId = $request->input('transaction_id');
        
        if (!$subscriptionId) {
            return redirect()->route('amo.register')
                ->with('error', 'Invalid payment session.');
        }

        $subscription = Subscription::findOrFail($subscriptionId);
        
        // Verify payment with JazaCash
        $verification = $this->jazaCashService->verifyPayment($transactionId);

        DB::transaction(function () use ($subscription, $verification) {
            if ($verification['success'] && $verification['status'] === 'completed') {
                // Update subscription status
                $subscription->update([
                    'payment_status' => 'completed',
                    'starts_at' => now(),
                    'ends_at' => now()->addDays($subscription->plan->duration_days),
                    'payment_data' => array_merge(
                        $subscription->payment_data ?? [],
                        ['verification_response' => $verification]
                    ),
                ]);

                // Login the user
                Auth::login($subscription->user);
            } else {
                // Payment failed
                $subscription->update([
                    'payment_status' => 'failed',
                    'payment_data' => array_merge(
                        $subscription->payment_data ?? [],
                        ['verification_response' => $verification]
                    ),
                ]);
            }
        });

        // Clear session
        $request->session()->forget(['selected_plan', 'plan_amount', 'pending_subscription_id']);

        if ($subscription->payment_status === 'completed') {
            return redirect()->route('home')
                ->with('success', 'Registration successful! Your subscription is now active.');
        }

        return redirect()->route('amo.login')
            ->with('error', 'Payment failed. Please try again or contact support.');
    }

    /**
     * Show payment success page
     */
    public function paymentSuccess(Request $request)
    {
        return Inertia::render('public_auth/PaymentSuccess');
    }

    /**
     * Handle payment cancellation
     */
    public function paymentCancel(Request $request)
    {
        $subscriptionId = $request->session()->get('pending_subscription_id');
        
        if ($subscriptionId) {
            Subscription::find($subscriptionId)->update(['payment_status' => 'cancelled']);
            $request->session()->forget('pending_subscription_id');
        }

        return redirect()->route('amo.register')
            ->with('info', 'Payment was cancelled. Please try again.');
    }

    /**
     * Show forgot password form for AMO users
     */
    public function showForgotPassword(): Response
    {
        return Inertia::render('public_auth/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    /**
     * Send password reset link for AMO users
     */
    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

    /**
     * Show reset password form
     */
    public function showResetPassword(Request $request): Response
    {
        return Inertia::render('public_auth/ResetPassword', [
            'email' => $request->email,
            'token' => $request->route('token'),
        ]);
    }

    /**
     * Reset password for AMO users
     */
    public function resetPassword(PublicResetPasswordRequest $request): RedirectResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('amo.login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}