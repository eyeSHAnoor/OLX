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
     * Display a listing of reports (admin only)
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', UserReport::class);

        $reports = UserReport::with(['reportedUser', 'reporter', 'ad'])
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->search, function ($query, $search) {
                return $query->whereHas('reportedUser', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Reports/Index', [
            'reports' => $reports,
            'filters' => $request->only(['status', 'search']),
            'statuses' => UserReport::getStatuses(),
        ]);
    }

    /**
     * Show form to create a new report
     */
    public function create(Request $request)
    {
        $reportedUser = null;
        $ad = null;

        if ($request->user_id) {
            $reportedUser = User::findOrFail($request->user_id);
        }

        if ($request->ad_id) {
            $ad = Ad::with('user')->findOrFail($request->ad_id);
            $reportedUser = $ad->user;
        }

        return Inertia::render('Reports/Create', [
            'reportedUser' => $reportedUser,
            'ad' => $ad,
            'reasons' => UserReport::getReasons(),
        ]);
    }

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

    /**
     * Display the specified report
     */
    public function show(UserReport $userReport)
    {
        $this->authorize('view', $userReport);

        $userReport->load(['reportedUser', 'reporter', 'ad']);

        return Inertia::render('Admin/Reports/Show', [
            'report' => $userReport,
            'statuses' => UserReport::getStatuses(),
        ]);
    }

    /**
     * Update the report status (admin only)
     */
    public function updateStatus(Request $request, UserReport $userReport)
    {
        $this->authorize('update', $userReport);

        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(UserReport::getStatuses())),
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $userReport->update([
            'status' => $validated['status'],
        ]);

        // You might want to add admin notes to a separate table or JSON field
        // For now, we'll just update the status

        return back()->with('success', 'Report status updated successfully.');
    }

    /**
     * Remove the specified report (admin only)
     */
    public function destroy(UserReport $userReport)
    {
        $this->authorize('delete', $userReport);

        $userReport->delete();

        return redirect()->route('admin.reports.index')
            ->with('success', 'Report deleted successfully.');
    }
}