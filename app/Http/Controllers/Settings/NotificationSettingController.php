<?php

namespace App\Http\Controllers\Settings;

use App\Data\UserData;
use App\Http\Controllers\Controller;
use App\Models\UserNotificationSetting;
use App\Models\UserPreference;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationSettingController extends Controller
{
    /**
     * Show the user's notification settings page.
     */
    public function edit(Request $request): Response
    {
        // full configuration options
        $config = [
            'types' => [
                ['key' => 'order', 'label' => 'Order Notifications'],
                ['key' => 'inventory', 'label' => 'Inventory Alerts'],
                ['key' => 'system', 'label' => 'System Announcements'],
            ],
            'methods' => [
                ['key' => 'email', 'label' => 'Email'],
                ['key' => 'sms', 'label' => 'SMS'],
                ['key' => 'push', 'label' => 'App Push'],
            ],
            'timings' => [
                ['key' => 'anytime', 'label' => 'Anytime'],
                ['key' => 'business_hours', 'label' => 'Business Hours'],
            ],
            'frequencies' => [
                ['key' => 'instant', 'label' => 'Instant'],
                ['key' => 'hourly', 'label' => 'Hourly'],
                ['key' => 'daily', 'label' => 'Daily'],
            ],
        ];

        return Inertia::render('settings/NotificationSetting', [
            'user' => UserData::from(auth()->user()->load(['notificationSettings'])),
            'config' => $config,
        ]);
    }

    /**
     * Update the user's notification preferences.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        foreach ($request->settings as $item) {
            UserNotificationSetting::updateOrCreate(
                ['user_id' => $user->id, 'type' => $item['type']],
                [
                    'methods' => $item['methods'],
                    'timing' => $item['timing'],
                    'frequency' => $item['frequency'],
                ]
            );
        }

        return redirect()->back()->with('success', __('controllers.notification_settings_updated'));
    }
}
