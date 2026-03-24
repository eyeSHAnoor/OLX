<?php
// app/Http/Controllers/Auth/RegisteredUserController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\VerificationCodeMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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

        // Create user but don't mark email as verified
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'email_verified_at' => null, // Not verified yet
        ]);

        // Generate and save verification code (expires in 2 minutes)
        $code = $user->generateVerificationCode();
        
        // Send verification email
        Mail::to($user->email)->send(new VerificationCodeMail($user, $code, 2)); // Pass 2 minutes to email

        // Store user ID in session for verification
        $request->session()->put('pending_verification_user_id', $user->id);
        $request->session()->put('verification_started_at', now());

        // Redirect to verification page
        return redirect()->route('verification.show');
    }
}