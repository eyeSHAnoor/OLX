<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\PublicAuthController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

// Public/AMO Routes (for users with plans) - Separate from admin
Route::prefix('amo')->name('amo.')->middleware('guest')->group(function () {
    // Public registration with plans
    Route::get('register', [PublicAuthController::class, 'register'])
        ->name('register');
    
    Route::get('plans', [PublicAuthController::class, 'showPlans'])
        ->name('plans.show');
    
    Route::post('plans/{plan}/select', [PublicAuthController::class, 'selectPlan'])
        ->name('plans.select');
    
    Route::post('register', [PublicAuthController::class, 'store'])
        ->name('register.store');
    
    // Public login
    Route::get('login', [PublicAuthController::class, 'create'])
        ->name('login');
    
    Route::post('login', [PublicAuthController::class, 'login'])
        ->name('login.store');
    
    // Payment Routes
    Route::get('payment/callback', [PublicAuthController::class, 'paymentCallback'])
        ->name('payment.callback');
    
    Route::get('payment/success', [PublicAuthController::class, 'paymentSuccess'])
        ->name('payment.success');
    
    Route::get('payment/cancel', [PublicAuthController::class, 'paymentCancel'])
        ->name('payment.cancel');
    
    // Public password reset
    Route::get('forgot-password', [PublicAuthController::class, 'showForgotPassword'])
        ->name('password.request');
    
    Route::post('forgot-password', [PublicAuthController::class, 'sendResetLink'])
        ->name('password.email');
    
    Route::get('reset-password/{token}', [PublicAuthController::class, 'showResetPassword'])
        ->name('password.reset');
    
    Route::post('reset-password', [PublicAuthController::class, 'resetPassword'])
        ->name('password.update');
});

// Default Breeze Routes (for admin panel) - Keep as is
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');
    
    Route::post('register', [RegisteredUserController::class, 'store']);
    
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');
    
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');
    
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');
    
    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

// Authenticated routes (shared)
Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');
    
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
    
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');
    
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});