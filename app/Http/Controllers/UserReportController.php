<?php

namespace App\Http\Controllers;

use App\Models\UserReport;
use App\Models\User;
use App\Models\Ad;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class UserReportController extends Controller
{

    /**
     * Store a newly created report
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reported_user_id' => 'required|exists:users,id',
            'ad_id' => 'nullable|exists:ads,id',
            'reason' => 'required|string|in:' . implode(',', array_keys(UserReport::getReasons())),
            'message' => 'nullable|string|max:1000',
        ]);

        // Check if user already reported this user/ad
        $existingReport = UserReport::where('reported_by', Auth::id())
            ->where('reported_user_id', $validated['reported_user_id'])
            ->when($validated['ad_id'], function ($query) use ($validated) {
                return $query->where('ad_id', $validated['ad_id']);
            })
            ->whereIn('status', ['pending', 'reviewed'])
            ->first();

        if ($existingReport) {
            return back()->with('error', 'You have already reported this user.');
        }

        $report = UserReport::create([
            'reported_user_id' => $validated['reported_user_id'],
            'reported_by' => Auth::id(),
            'ad_id' => $validated['ad_id'] ?? null,
            'reason' => $validated['reason'],
            'message' => $validated['message'] ?? null,
            'status' => UserReport::STATUS_PENDING,
        ]);

        return redirect()->back()->with('success', 'Report submitted successfully.');
    }
}