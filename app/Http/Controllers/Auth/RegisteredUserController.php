<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ReferralController;
use App\Mail\VerificationCodeMail;
use App\Models\User;
use App\Models\Referral;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        // Check for referral code in URL
        $referralCode = request()->query('ref');
        
        if ($referralCode) {
            // Find the referrer by their referral code
            $referrer = User::where('referral_code', $referralCode)
                ->select('id', 'name', 'referral_code')
                ->first();
            
            if ($referrer) {
                // Store referral code in session
                session(['referral_code' => $referralCode]);
                
                // Track the visit
                Referral::create([
                    'referrer_id' => $referrer->id,
                    'referred_user_id' => null, // Will be updated when user registers
                    'status' => 'visited',
                    'points_awarded' => 0, // No points yet, just tracking visit
                    'link_code' => $referralCode,
                    'visited_at' => now(),
                ]);
                
                return Inertia::render('auth/Register', [
                    'referral_code' => $referralCode,
                    'referrer' => [
                        'name' => $referrer->name,
                    ],
                ]);
            }
        }
        
        // Clear any old referral code from session
        session()->forget('referral_code');
        
        return Inertia::render('auth/Register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => 'nullable|string|max:20',
        ]);

        // Get referral code from session (set when visiting referral link)
        $referralCode = session('referral_code');
        $referrer = null;
        
        if ($referralCode) {
            $referrer = User::where('referral_code', $referralCode)->first();
        }

        // Create user
        // Note: We don't auto-generate a referral code here
        // Only Super Admin assigns referral codes and points
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'email_verified_at' => null, // Not verified yet
            'referral_code' => null, // No referral code by default - Admin assigns it
            'referred_by' => $referrer?->id, // Set who referred this user
            'points_balance' => 0, // Start with 0 points - Admin assigns initial points
        ]);

        // Process referral if user was referred
        if ($referrer) {
            $this->processReferral($referrer, $user, $referralCode);
        }

        // Generate and save verification code (expires in 2 minutes)
        $code = $user->generateVerificationCode();
        
        // Send verification email
        Mail::to($user->email)->send(new VerificationCodeMail($user, $code, 2));

        // Store user ID in session for verification
        $request->session()->put('pending_verification_user_id', $user->id);
        $request->session()->put('verification_started_at', now());
        
        // Clear referral code from session
        $request->session()->forget('referral_code');

        // Redirect to verification page
        return redirect()->route('verification.show');
    }

    /**
     * Process referral when a new user registers through a referral link.
     * This awards points to both the referrer and the new user.
     */
    private function processReferral(User $referrer, User $newUser, string $referralCode): void
    {
        // Default referral points
        $referrerPoints = 100;
        $newUserPoints = 50;
        
        // Update the visit record or create a completed referral
        $referral = Referral::where('link_code', $referralCode)
            ->where('status', 'visited')
            ->whereNull('referred_user_id')
            ->latest()
            ->first();
        
        if ($referral) {
            // Update the existing visit record
            $referral->update([
                'referred_user_id' => $newUser->id,
                'status' => 'completed',
                'points_awarded' => $referrerPoints,
            ]);
        } else {
            // Create new referral record
            Referral::create([
                'referrer_id' => $referrer->id,
                'referred_user_id' => $newUser->id,
                'status' => 'completed',
                'points_awarded' => $referrerPoints,
                'link_code' => $referralCode,
                'visited_at' => now(),
            ]);
        }
        
        // Award points to referrer
        $referrer->increment('points_balance', $referrerPoints);
        
        // Award welcome points to new user
        $newUser->increment('points_balance', $newUserPoints);
    }

    /**
     * Generate unique referral code.
     * Only used if you want to auto-generate codes (currently not used).
     */
    private function generateUniqueCode($name): string
    {
        do {
            $code = Str::upper(Str::substr($name, 0, 3) . Str::random(5));
        } while (User::where('referral_code', $code)->exists());

        return $code;
    }
}