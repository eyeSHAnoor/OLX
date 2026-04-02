<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Authenticate the user
        $request->authenticate();

        // Regenerate session to prevent session fixation
        $request->session()->regenerate();

        $user = auth()->user();

        // If super admin, redirect to dashboard
        if ($user && $user->hasRole('super_admin')) {
            return redirect()->route('dashboard');
        }

        // Check if banned
        if ($user->status === 'banned') {
            // Log them out immediately
            auth()->logout();
            return back()->withErrors([
                'email' => 'Your account is banned.'
            ]);
        }

        // Check if suspended
        if ($user->status === 'suspended') {
            // Check if suspension is still active
            if ($user->suspended_until && $user->suspended_until->isFuture()) {
                auth()->logout();
                return back()->withErrors([
                    'email' => 'Your account is suspended until ' . $user->suspended_until->format('d M Y H:i')
                ]);
            } else {
                // If suspension has expired, reset status
                $user->update([
                    'status' => 'active',
                    'suspended_until' => null
                ]);
            }
        }

        // If everything is fine, proceed to intended page
        return redirect()->intended(route('home'));
    }


    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // return redirect('');
        return redirect()->route('home');
        
    }
}
