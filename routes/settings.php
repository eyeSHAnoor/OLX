<?php

use App\Http\Controllers\Settings\AccountController;
use App\Http\Controllers\Settings\NotificationSettingController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\PreferencesController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\UserRoleController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::redirect('settings', '/settings/profile');

    /*Profile Settings */
    // Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    // Route::post('settings/profile', [ProfileController::class, 'update'])->name('profile.update');


    /*Account Settings */
    Route::get('settings/account', [AccountController::class, 'edit'])->name('account.edit');
    Route::patch('settings/account', [AccountController::class, 'update'])->name('account.update');
    Route::delete('settings/account', [AccountController::class, 'destroy'])->name('account.destroy');

    Route::get('settings/roles-permissions', [RolePermissionController::class, 'index'])->name('roles-permissions.index');
    Route::post('settings/roles-permissions', [RolePermissionController::class, 'store'])->name('roles-permissions.store');
    Route::post('settings/roles-permissions/{role}', [RolePermissionController::class, 'update'])->name('roles-permissions.update');
    Route::delete('settings/roles-permissions/{role}', [RolePermissionController::class, 'destroy'])->name('roles-permissions.destroy');

    Route::get('settings/user-roles', [UserRoleController::class, 'index'])->name('user-roles.index');
    Route::post('settings/user-roles', [UserRoleController::class, 'store'])->name('user-roles.create');
    Route::post('settings/user-roles/{user}', [UserRoleController::class, 'update'])->name('user-roles.update');
    Route::delete('settings/user-roles/{user}', [UserRoleController::class, 'destroy'])->name('user-roles.destroy');


    /*Preferences Settings */
    Route::get('settings/preferences', [PreferencesController::class, 'edit'])->name('preferences.edit');
    Route::post('settings/preferences', [PreferencesController::class, 'update'])->name('preferences.update');
    Route::post('settings/switch-language/{lang}', [PreferencesController::class, 'updateLanguage'])->name('preferences.update-language');
    //    Route::get('locale/{lang}', function ($lang) {
//        if (in_array($lang, ['en', 'ur'])) {
//            session(['locale' => $lang]);
//            app()->setLocale($lang);
//        }
//        return back();
//    });


    /*Preferences Settings */
    Route::get('settings/notifications', [NotificationSettingController::class, 'edit'])->name('notification-settings.edit');
    Route::post('settings/notifications', [NotificationSettingController::class, 'update'])->name('notification-settings.update');


    Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('settings/password', [PasswordController::class, 'update'])->name('password.update');

    Route::get('settings/appearance', function () {
        return Inertia::render('settings/Appearance');
    })->name('appearance');
});
