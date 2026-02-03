<?php

namespace App\Http\Controllers\Settings;

use App\Data\UserData;
use App\Http\Controllers\Controller;
use App\Models\UserPreference;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PreferencesController extends Controller
{
    /**
     * Show the user's preference settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Preference', [
            'user' => UserData::from(auth()->user()->load([
                'preferences',
                'files' => fn($q) => $q->where('collection', 'business_license')
            ])),
        ]);
    }

    /**
     * Update the user's preferences.
     */
    public function update(Request $request)
    {
        UserPreference::updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'language' => $request->language,
                'timezone' => $request->timezone,
                'date_format' => $request->date_format,
                'currency' => $request->currency,
            ]
        );

        return redirect()->back()->with('success', __('controllers.preference_settings_updated'));
    }

    /**
     * Switch the user's language.
     */
    public function updateLanguage(Request $request, $lang)
    {
        UserPreference::updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'language' => $lang,
            ]
        );

        app()->setLocale($lang);
    }
}
