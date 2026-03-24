<?php
// app/Http/Controllers/SocialAuthController.php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Mail\VerificationCodeMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;

class SocialAuthController extends Controller
{
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
            
            // Check if user exists
            $user = User::where('email', $socialUser->getEmail())->first();

            if ($user) {
                // User exists - check if email is verified
                if ($user->hasVerifiedEmail()) {
                    // Email verified - log them in
                    Auth::login($user);
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
                // New user - create but don't verify email
                $user = User::create([
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'password' => Hash::make(uniqid()), // Random password
                    'email_verified_at' => null, // Not verified yet
                ]);

                // Generate and send verification code
                $code = $user->generateVerificationCode();
                Mail::to($user->email)->send(new VerificationCodeMail($user, $code, 2));

                // Store user ID in session for verification
                session()->put('pending_verification_user_id', $user->id);
                session()->put('verification_started_at', now());

                return redirect()->route('verification.show')
                    ->with('info', 'Please verify your email to complete registration. A verification code has been sent.');
            }
            
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Social login error: ' . $e->getMessage());
            
            return redirect()->route('login')
                ->withErrors(['error' => 'Unable to login with ' . $provider . '. Please try again.']);
        }
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