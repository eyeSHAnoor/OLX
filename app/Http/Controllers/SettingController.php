<?php
// app/Http/Controllers/Auth/ChangePasswordController.php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index()
    {
        return Inertia::render('home/Settings');
    }

    public function update(ChangePasswordRequest $request)
    {
        try {
            $user = Auth::user();
            
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return redirect()->route('amo.settings');

        } catch (\Exception $e) {
            return redirect()->route('amo.settings')->with('Error','There is err in changing password');
        }
    }
}