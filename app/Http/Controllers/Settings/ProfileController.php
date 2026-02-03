<?php

namespace App\Http\Controllers\Settings;

use App\Data\UserData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Models\UserProfile;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'user' => UserData::from(auth()->user()->load([
                'profile',
                'files' => fn($q) => $q->where('collection', 'business_license')
            ])),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        //        dd($request->all());


        UserProfile::updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'company_name' => $request->company_name,
                'address' => $request->address,
                'phone_1' => $request->phone_1,
                'phone_2' => $request->phone_2,
                'contact_person' => $request->contact_person,
                'company_email' => $request->company_email,
                //                'verified_at' => $request->verified_at,
//                'verified_by' => $request->verified_by,
            ]
        );

        $user = auth()->user();
        if ($request->hasFile('business_license')) {
            $user->files?->where('collection', 'business_license')?->each->delete();
            $user->addFiles(input: 'business_license', collection: 'business_license');
        }


        return redirect()->back()->with('success', __("controllers.profile_settings_updated"));
    }
}
