<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $userProfile = $user->profile ?? new UserProfile(['user_id' => $user->id]);
        
        return inertia('home/EditProfile', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'userProfile' => [
                'username' => $userProfile->username,
                'profile_image' => $userProfile->profile_image ? Storage::url($userProfile->profile_image) : null,
                'cover_image' => $userProfile->cover_image ? Storage::url($userProfile->cover_image) : null,
                'bio' => $userProfile->bio,
                'location' => $userProfile->location,
                'website' => $userProfile->website,
                'is_public' => $userProfile->is_public ?? true,
            ]
        ]);
    }

    public function update(Request $request)
    {
        // dd($request->all());
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255|unique:user_profiles,username,' . $user->id . ',user_id',
            'phone' => 'nullable|string|max:20|unique:users,phone',
            'bio' => 'nullable|string|max:500',
            'location' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'is_public' => 'boolean',
            'profile_image' => 'nullable|image|max:2048',
            'cover_image' => 'nullable|image|max:5120',
        ]);

        if (isset($validated['phone'])) {
                $user->update([
                    'name' => $validated['name'] ?? $user->name,
                    'phone' => $validated['phone']
                ]);
            }

        $profile = UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'username' => $validated['username'],
                'bio' => $validated['bio'],
                'location' => $validated['location'],
                'website' => $validated['website'],
                'is_public' => $validated['is_public'],
            ]
        );

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            // Delete old image
            if ($profile->profile_image) {
                Storage::delete($profile->profile_image);
            }
            
            $path = $request->file('profile_image')->store('profile-images', 'public');
            $profile->profile_image = $path;
        }

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            // Delete old image
            if ($profile->cover_image) {
                Storage::delete($profile->cover_image);
            }
            
            $path = $request->file('cover_image')->store('cover-images', 'public');
            $profile->cover_image = $path;
        }

        $profile->save();

        return redirect()->route('user.profile', ['id' => $user->id])
            ->with('success', 'Profile updated successfully!');
    }

    public function destroy(Request $request)
    {
        $user = Auth::user();
        
        // Delete profile images
        if ($user->profile) {
            if ($user->profile->profile_image) {
                Storage::delete($user->profile->profile_image);
            }
            if ($user->profile->cover_image) {
                Storage::delete($user->profile->cover_image);
            }
            $user->profile->delete();
        }
        
        // Delete user's ads
        $user->ads()->delete();
        
        // Delete user
        $user->delete();
        
        Auth::logout();
        
        return redirect()->route('home')
            ->with('success', 'Your account has been deleted successfully.');
    }

    public function checkUsername(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|regex:/^[a-zA-Z0-9_]+$/'
        ]);

        $userId = Auth::id();
        
        $exists = UserProfile::where('username', $request->username)
            ->when($userId, function ($query) use ($userId) {
                return $query->where('user_id', '!=', $userId);
            })
            ->exists();

        return response()->json([
            'available' => !$exists
        ]);
    }
}