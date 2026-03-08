<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\UserReport;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReportResponseMail;
use App\Notifications\UserActionNotification; 

class ReportController extends Controller
{
    /**
     * Display a listing of reports for super_admin
     */
    public function index(Request $request)
    {
        $reports = UserReport::with([
            'reportedUser' => function ($q) {
                $q->select('id', 'name', 'email', 'avatar');
            },
            'reporter' => function ($q) {
                $q->select('id', 'name', 'email', 'avatar');
            },
            'ad' => function ($q) {
                $q->select('id', 'ad_title', 'price', 'status', 'user_id');
            },
            'ad.user' => function ($q) {
                $q->select('id', 'name');
            }
        ])
            ->when($request->search, function ($query, $search) {
                $query->whereHas('reportedUser', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('reporter', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('reason', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                if ($status !== 'all') {
                    $query->where('status', $status);
                }
            })
            ->when($request->reason, function ($query, $reason) {
                if ($reason !== 'all') {
                    $query->where('reason', $reason);
                }
            })
            ->when($request->date_from, function ($query, $date) {
                $query->whereDate('created_at', '>=', $date);
            })
            ->when($request->date_to, function ($query, $date) {
                $query->whereDate('created_at', '<=', $date);
            })
            ->latest()
            ->paginate($request->perPage ?? 15)
            ->withQueryString();

        return Inertia::render('reports/Index', [
            'reports' => $reports,
            'filters' => $request->only(['search', 'status', 'reason', 'date_from', 'date_to', 'perPage']),
            'statuses' => UserReport::getStatuses(),
            'reasons' => UserReport::getReasons(),
        ]);
    }

    /**
     * Get single report details for modal
     */
    public function show(UserReport $report)
    {
        $report->load([
            'reportedUser' => function ($q) {
                $q->select('id', 'name', 'email', 'avatar', 'phone', 'created_at', 'status');
            },
            'reporter' => function ($q) {
                $q->select('id', 'name', 'email', 'avatar', 'phone', 'created_at');
            },
            'ad' => function ($q) {
                $q->select('id', 'ad_title', 'description', 'price', 'status', 'user_id', 'created_at');
            },
            'ad.user' => function ($q) {
                $q->select('id', 'name', 'email', 'phone');
            }
        ]);

        return response()->json([
            'report' => $report,
            'statuses' => UserReport::getStatuses(),
            'reasons' => UserReport::getReasons(),
        ]);
    }

    /**
     * Update report status and send response email
     */
    public function respond(Request $request, UserReport $report)
    {
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(UserReport::getStatuses())),
            'response_message' => 'required|string|max:2000',
            'notify_reporter' => 'boolean',
            'take_action_against_user' => 'boolean',
            'action_type' => 'required_if:take_action_against_user,true|in:warn,suspend,ban,scam,fake_listing|nullable',
        ]);

        // Update report status
        $report->update([
            'status' => $validated['status'],
            'admin_response' => $validated['response_message'],
            'responded_at' => now(),
            'responded_by' => auth()->id(),
        ]);

        // Take action against reported user if needed
        if ($validated['take_action_against_user'] && $validated['action_type']) {
            $reportedUser = $report->reportedUser;
            
            switch ($validated['action_type']) {
                case 'warn':
                    // Add warning count or send warning email
                    $reportedUser->update(['warning_count' => ($reportedUser->warning_count ?? 0) + 1]);
                    $reportedUser->notify(new UserActionNotification(
                        'warn',
                        $report->reason,
                        $validated['response_message'],
                        $report->id
                    ));
                    break;
                case 'suspend':
                     $expiresAt = now()->addDays(7);
                    $reportedUser->update(['status' => 'suspended', 'suspended_until' => now()->addDays(7)]);
                    $reportedUser->notify(new UserActionNotification(
                        'suspend',
                        $report->reason,
                        $validated['response_message'],
                        $report->id,
                        $expiresAt
                    ));
                    break;
                case 'ban':
                    $reportedUser->update(['status' => 'banned']);
                    $reportedUser->notify(new UserActionNotification(
                        'ban',
                        $report->reason,
                        $validated['response_message'],
                        $report->id
                    ));
                    break;
            }
        }

        // Send email notification to reporter if requested
        if ($validated['notify_reporter']) {
            Mail::to($report->reporter->email)->send(new ReportResponseMail($report, $validated['response_message']));
        }

        // If ad is fake/scam, maybe take action on ad
        if (in_array($report->reason, ['scam', 'fake_listing']) && $validated['status'] === 'resolved') {
            if ($report->ad) {
                $report->ad->update(['status' => 'flagged']);
            }
        }

        return back()->with('success', 'Response sent and report updated successfully.');
    }

    /**
     * Delete a report
     */
    public function destroy(UserReport $report)
    {
        $report->delete();

        return redirect()->route('reports.index')
            ->with('success', 'Report deleted successfully.');
    }

    /**
     * Bulk update reports
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:user_reports,id',
            'status' => 'required|in:' . implode(',', array_keys(UserReport::getStatuses())),
        ]);

        UserReport::whereIn('id', $validated['ids'])->update([
            'status' => $validated['status'],
            'responded_at' => now(),
            'responded_by' => auth()->id(),
        ]);

        return back()->with('success', count($validated['ids']) . ' reports updated successfully.');
    }
}