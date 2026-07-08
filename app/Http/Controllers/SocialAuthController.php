<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\Referral;
use App\Mail\VerificationCodeMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class SocialAuthController extends Controller
{
    public function redirect($provider)
    {
        // Store referral code in session if present in URL
        if (request()->has('ref')) {
            $referralCode = request()->ref;
            // Log::info('Referral code in URL: ' . $referralCode);
            
            // Verify the referral code exists
            $referrer = User::where('referral_code', $referralCode)->first();
            
            if ($referrer) {
                // Store in session for backup
                session(['referral_code' => $referralCode]);
                
                // Track the visit
                Referral::create([
                    'referrer_id' => $referrer->id,
                    'referred_user_id' => null,
                    'status' => 'visited',
                    'points_awarded' => 0,
                    'link_code' => $referralCode,
                    'visited_at' => now(),
                ]);
                
                // Use Socialite's state parameter to pass the referral code
                return Socialite::driver($provider)
                    ->with(['state' => $referralCode])
                    ->redirect();
            }
        }

        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider, Request $request)
    {
        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
            
            // Get referral code from multiple sources (in order of priority)
            $referralCode = $this->getReferralCode($request);
            $referrer = null;

            // Log::info('Referral code found: ' . ($referralCode ?? 'none'));
            // Log::info('Social user email: ' . $socialUser->getEmail());
            // Log::info('Social user name: ' . $socialUser->getName());
            // Log::info('Session ID callback: ' . session()->getId());

            if ($referralCode) {
                $referrer = User::where('referral_code', $referralCode)->first();
                // Log::info('Referrer found: ' . ($referrer ? $referrer->name : 'none'));
            }
            
            // Check if user exists
            $user = User::where('email', $socialUser->getEmail())->first();

            if ($user) {
                // User exists - handle referral if not already referred
                if (!$user->referred_by && $referrer) {
                    DB::transaction(function () use ($user, $referrer, $referralCode) {
                        $user->update(['referred_by' => $referrer->id]);
                        $this->processReferral($referrer, $user, $referralCode);
                    });
                }

                // Check if email is verified
                if ($user->hasVerifiedEmail()) {
                    // Email verified - log them in
                    Auth::login($user);
                    
                    // Clear referral from all sources
                    $this->clearReferralCode($request);
                    
                    return $this->redirectBasedOnRole($user);
                } else {
                    // Email not verified - send new verification code
                    $code = $user->generateVerificationCode();
                    Mail::to($user->email)->send(new VerificationCodeMail($user, $code, 2));
                    
                    // Store user ID in session for verification
                    session()->put('pending_verification_user_id', $user->id);
                    session()->put('verification_started_at', now());
                    
                    return redirect()->route('verification.show')
                        ->with('info', 'Please verify your email to continue. A new code has been sent.');
                }
            } else {
                // New user - create with referral info
                $user = User::create([
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'password' => Hash::make(uniqid()), // Random password
                    'email_verified_at' => null, // Not verified yet
                    'referral_code' => null, // Admin assigns this later
                    'referred_by' => $referrer?->id, // Set who referred them
                    'points_balance' => 0, // Start with 0, points added after referral processing
                ]);

                // Process referral if user came through referral link
                if ($referrer) {
                    DB::transaction(function () use ($referrer, $user, $referralCode) {
                        $this->processReferral($referrer, $user, $referralCode);
                    });
                }

                // Generate and send verification code
                $code = $user->generateVerificationCode();
                Mail::to($user->email)->send(new VerificationCodeMail($user, $code, 2));

                // Store user ID in session for verification
                session()->put('pending_verification_user_id', $user->id);
                session()->put('verification_started_at', now());

                // Clear referral from all sources
                $this->clearReferralCode($request);

                return redirect()->route('verification.show')
                    ->with('info', 'Please verify your email to complete registration. A verification code has been sent.');
            }
            
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Social login error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->route('login')
                ->withErrors(['error' => 'Unable to login with ' . $provider . '. Please try again.']);
        }
    }

    /**
     * Get referral code from multiple possible sources
     */
    private function getReferralCode(Request $request): ?string
    {
        // 1. Check if state parameter contains the referral code (from Socialite state)
        $state = $request->input('state');
        if ($state) {
            // Socialite encodes state as a base64 JSON string
            $decodedState = json_decode(base64_decode($state), true);
            
            // If the state is just our referral code (not the full Socialite state)
            if ($decodedState === null && preg_match('/^REF[A-Z0-9]+$/', $state)) {
                // Log::info('Found referral code in raw state: ' . $state);
                return $state;
            }
        }
        
        // 2. Check if there's a 'ref' parameter in the callback URL
        if ($request->has('ref')) {
            // Log::info('Found referral code in ref parameter: ' . $request->ref);
            return $request->ref;
        }
        
        // 3. Check session as fallback
        if (session()->has('referral_code')) {
            // Log::info('Found referral code in session: ' . session('referral_code'));
            return session('referral_code');
        }
        
        // 4. Check cookie as last resort
        if ($request->cookie('referral_code')) {
            // Log::info('Found referral code in cookie: ' . $request->cookie('referral_code'));
            return $request->cookie('referral_code');
        }
        
        return null;
    }

    /**
     * Clear referral code from all sources
     */
    private function clearReferralCode(Request $request): void
    {
        session()->forget('referral_code');
    }

    /**
     * Process referral when a new user registers through a referral link.
     * Awards points to both the referrer and the new user.
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
     * Redirect user based on their role
     */
    private function redirectBasedOnRole($user)
    {
        if ($user->hasRole('super_admin')) {
            return redirect()->route('dashboard');
        }
        return redirect()->intended(route('home'));
    }
}