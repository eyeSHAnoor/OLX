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
                
                // Track the visit - ONLY create if not exists
                $existingVisit = Referral::where('link_code', $referralCode)
                    ->where('status', 'visited')
                    ->whereNull('referred_user_id')
                    ->exists();
                
                if (!$existingVisit) {
                    Referral::create([
                        'referrer_id' => $referrer->id,
                        'referred_user_id' => null,
                        'status' => 'visited',
                        'points_awarded' => 0,
                        'link_code' => $referralCode,
                        'visited_at' => now(),
                    ]);
                }
                
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
        \Log::info([
            'session_id' => session()->getId(),
            'referral_code' => session('referral_code'),
        ]);
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

        // Generate a unique referral code for the new user
        $newReferralCode = $this->generateUniqueReferralCode();

        // Create user with auto-generated referral code
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'email_verified_at' => null,
            'referral_code' => $newReferralCode,
            'referred_by' => $referrer?->id,
            'points_balance' => 0,
            // 'code_assigned_by' => $referrer?->id ?? null,
        ]);

        // Process referral if user was referred
        if ($referrer) {
            $this->trackReferral($referrer, $user, $referralCode);
        }

        // Generate and save verification code
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
     * Track referral when a new user registers through a referral link.
     * No points awarded here - points will be awarded on subscription completion.
     * Only ONE referral record per referrer per referred user.
     */
    private function trackReferral(User $immediateReferrer, User $newUser, string $referralCode): void
    {
        // 1. Find and update the visited record for the immediate referrer
        $visitedReferral = Referral::where('link_code', $referralCode)
            ->where('status', 'visited')
            ->whereNull('referred_user_id')
            ->latest()
            ->first();

        if ($visitedReferral) {
            $visitedReferral->update([
                'referred_user_id' => $newUser->id,
                'status' => 'registered',
                'points_awarded' => 0,
                'level' => 1,
            ]);
        } else {
            Referral::create([
                'referrer_id' => $immediateReferrer->id,
                'referred_user_id' => $newUser->id,
                'status' => 'registered',
                'points_awarded' => 0,
                'link_code' => $referralCode,
                'visited_at' => now(),
                'level' => 1,
            ]);
        }

        // 2. Walk UP the code‑assignment chain with safety limits
        $level = 2;
        $current = $immediateReferrer->codeAssigner;
        $visitedIds = [$immediateReferrer->id]; // Track visited IDs to prevent circular loop
        $maxLevel = 50; // Safety limit

        while ($current && $level <= $maxLevel) {
            // Prevent infinite loop from circular references
            if (in_array($current->id, $visitedIds)) {
                \Log::warning("Circular reference detected in referral chain! User ID: {$current->id}");
                break;
            }
            
            $visitedIds[] = $current->id;

            if ($current->referral_code) {
                // Check if a referral record already exists
                $existingUplineReferral = Referral::where('referrer_id', $current->id)
                    ->where('referred_user_id', $newUser->id)
                    ->where('link_code', $referralCode)
                    ->where('level', $level)
                    ->first();

                if (!$existingUplineReferral) {
                    Referral::create([
                        'referrer_id' => $current->id,
                        'referred_user_id' => $newUser->id,
                        'status' => 'registered',
                        'points_awarded' => 0,
                        'link_code' => $referralCode,
                        'visited_at' => now(),
                        'level' => $level,
                    ]);
                }
            }

            $current = $current->codeAssigner;
            $level++;
        }
        
        if ($level > $maxLevel) {
            \Log::error("Referral chain exceeded max level! Stopped at level {$level}");
        }
    }

    /**
     * Generate a unique referral code for new users
     */
    private function generateUniqueReferralCode(): string
    {
        $prefix = 'REF';
        $length = 8;
        
        do {
            $randomString = Str::upper(Str::random($length));
            $code = $prefix . $randomString;
            $exists = User::where('referral_code', $code)->exists();
        } while ($exists);
        
        return $code;
    }

    /**
     * Alternative: Generate referral code with user name and random numbers
     */
    private function generateReferralCodeFromName(string $name): string
    {
        $namePart = Str::upper(Str::substr($name, 0, 3));
        $randomPart = Str::upper(Str::random(5));
        $code = $namePart . $randomPart;
        
        $counter = 1;
        $originalCode = $code;
        
        while (User::where('referral_code', $code)->exists()) {
            $code = $originalCode . $counter;
            $counter++;
        }
        
        return $code;
    }
}