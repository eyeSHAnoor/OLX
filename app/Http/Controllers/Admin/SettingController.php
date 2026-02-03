<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SettingRequest;
use App\Models\Setting;
use Illuminate\Support\Facades\Gate;

class SettingController extends Controller
{
    public function edit()
    {
        if (Gate::denies('viewAny', Setting::class)) {
            abort(403);
        }
        //        dd(Setting::getAll());

        return inertia('settings/Settings', [
            'settings' => Setting::getAll(),
        ]);
    }

    public function update(SettingRequest $request)
    {
        $request->updateRecord();

        return redirect()->back()->with('success', __('controllers.setting_saved'));
    }

}
