<?php
// app/Http/Controllers/Auth/VerificationCodeController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\VerificationCodeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class VerificationCodeController extends Controller
{
    public function show(Request $request)
    {
        $userId = $request->session()->get('pending_verification_user_id');
        
        if (!$userId) {
            return redirect()->route('register')
                ->with('error', 'Verification session expired. Please register again.');
        }
        
        $user = User::find($userId);
        
        if (!$user) {
            return redirect()->route('register')
                ->with('error', 'User not found. Please register again.');
        }
        
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('login')
                ->with('success', 'Your email is already verified. Please login.');
        }
        
        // Check if code is expired
        $isExpired = $user->verification_code_expires_at && $user->verification_code_expires_at->isPast();
        
        return Inertia::render('auth/VerifyCode', [
            'email' => $user->email,
            'isExpired' => $isExpired,
            'expiresAt' => $user->verification_code_expires_at?->format('Y-m-d H:i:s')
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $userId = $request->session()->get('pending_verification_user_id');
        
        if (!$userId) {
            return back()->withErrors(['code' => 'Verification session expired. Please register again.']);
        }
        
        $user = User::find($userId);
        
        if (!$user) {
            return back()->withErrors(['code' => 'User not found.']);
        }

        if ($user->hasVerifiedEmail()) {
            $request->session()->forget('pending_verification_user_id');
            return redirect()->route('login')
                ->with('success', 'Email already verified. Please login.');
        }

        // Check if code is expired
        if ($user->verification_code_expires_at && $user->verification_code_expires_at->isPast()) {
            return back()->withErrors(['code' => 'Verification code has expired. Please request a new one.']);
        }

        // Verify the code
        if (!$user->verifyCode($request->code)) {
            return back()->withErrors(['code' => 'Invalid verification code.']);
        }

        // Mark email as verified and clear verification data
        $user->markEmailAsVerified();
        
        // Clear session
        $request->session()->forget('pending_verification_user_id');
        $request->session()->forget('verification_started_at');
        
        // Log the user in
        auth()->login($user);

        // Redirect based on role
        if ($user->hasRole('super_admin')) {
            return redirect()->route('dashboard');
        }
        
        return redirect()->intended(route('home'))
            ->with('success', 'Email verified successfully! Your account is now active.');
    }

    public function resend(Request $request)
    {
        $userId = $request->session()->get('pending_verification_user_id');
        
        if (!$userId) {
            return back()->withErrors(['error' => 'Verification session expired. Please register again.']);
        }
        
        $user = User::find($userId);
        
        if (!$user) {
            return back()->withErrors(['error' => 'User not found.']);
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('login')
                ->with('success', 'Email already verified. Please login.');
        }

        // Generate new code (expires in 2 minutes)
        $code = $user->generateVerificationCode(); // This will reset the expiration to 2 minutes from now
        
        // Send email
        Mail::to($user->email)->send(new VerificationCodeMail($user, $code, 2));

        return back()->with('success', 'A new verification code has been sent. It will expire in 2 minutes.');
    }
}