<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserReferralScore;
use App\Models\Referral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class DownlineReferralController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->referral_code) {
                return redirect()->route('dashboard')
                    ->with('error', 'You do not have a referral code yet.');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $currentUser = auth()->user();

        // Tab 1: Code Assignments
        $codeAssignments = User::where('code_assigned_by', $currentUser->id)
            ->withCount(['referralsMade as referrals_count' => function ($q) {
                $q->where('status', 'completed');
            }])
            ->select('id', 'name', 'email', 'referral_code', 'points_balance', 'code_assigned_by', 'created_at')
            ->orderBy('created_at', 'desc')
            ->paginate(20, ['*'], 'assignments_page')
            ->withQueryString();

        // Tab 2: Direct Referrals
        $directReferrals = User::where('referred_by', $currentUser->id)
            ->withCount(['referralsMade as referrals_count' => function ($q) {
                $q->where('status', 'completed');
            }])
            ->select('id', 'name', 'email', 'referral_code', 'points_balance', 'referred_by', 'created_at')
            ->orderBy('created_at', 'desc')
            ->paginate(20, ['*'], 'referrals_page')
            ->withQueryString();

        // Get user's referral score
        $userScore = UserReferralScore::where('user_id', $currentUser->id)->first();

        // ✅ Get withdrawal history with proof images
        $withdrawalHistory = UserReferralScore::where('user_id', $currentUser->id)
            // ->whereNotNull('requested_amount') // Only show withdrawals
            ->orderBy('created_at', 'desc')
            ->paginate(20, ['*'], 'history_page')
            ->withQueryString();

        // Get all downline IDs for stats
        $downlineIds = User::where('code_assigned_by', $currentUser->id)->pluck('id');
        $referralIds = User::where('referred_by', $currentUser->id)->pluck('id');

        $stats = [
            'total_assignments' => $downlineIds->count(),
            'total_referrals' => $referralIds->count(),
            'total_points_given' => Referral::where('referrer_id', $currentUser->id)
                ->where('status', 'completed')
                ->sum('points_awarded'),
            'total_referrals_by_downline' => Referral::whereIn('referrer_id', $downlineIds)
                ->where('status', 'completed')
                ->count(),
            'total_earned' => $userScore?->total_earned ?? 0,
            'total_withdrawn' => $userScore?->total_withdrawn ?? 0,
            'available_points' => $userScore?->available ?? 0,
            'pending_points' => $userScore?->pending ?? 0,
            'has_pending_withdrawal' => $userScore?->hasPendingWithdrawal() ?? false,
            'withdrawal_status' => $userScore?->status ?? 'active',
        ];

        $canAssignCodes = $currentUser->can_assign_code;

        return Inertia::render('referral/Client/Index', [
            'codeAssignments' => $codeAssignments,
            'directReferrals' => $directReferrals,
            'stats' => $stats,
            'currentUserPoints' => $userScore?->available ?? 0,
            'canAssignCodes' => $canAssignCodes,
            'userScore' => $userScore,
            'withdrawalHistory' => $withdrawalHistory, // ✅ Pass to frontend
        ]);
    }

    public function create(Request $request)
    {
        $eligibleUsers = User::where('referred_by', auth()->id())
            ->whereNull('referral_code')
            ->select('id', 'name', 'email', 'points_balance')
            ->orderBy('name')
            ->get();

        $selectedUser = null;
        if ($request->has('user_id')) {
            $selectedUser = User::where('referred_by', auth()->id())
                ->whereNull('referral_code')
                ->select('id', 'name', 'email', 'points_balance')
                ->find($request->user_id);
        }

        return Inertia::render('referral/Client/RecordForm', [
            'eligibleUsers' => $eligibleUsers,
            'selectedUser' => $selectedUser,
            'currentUserPoints' => auth()->user()->points_balance,
            'editing' => false,
        ]);
    }

    /**
     * Assign referral code + points + set code_assigned_by.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $currentUser = auth()->user();

        $validated = $request->validate([
            'user_id' => 
                'required',
                'exists:users,id',
            'referral_code' => 'required|string|max:255',
            'points' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($validated, $currentUser) {
            $downlineUser = User::findOrFail($validated['user_id']);

            // Assign referral code AND mark who assigned it
            $downlineUser->update([
                'referral_code' => $validated['referral_code'],
                'code_assigned_by' => $currentUser->id,
                'points_balance' => $validated['points']
            ]);
        });

        return redirect()->route('downline-referrals.index')
            ->with('success', 'Referral code and points assigned successfully.');
    }

    public function edit(User $user)
    {
        if ($user->code_assigned_by !== auth()->id()) {
            abort(403);
        }

        return Inertia::render('referral/Client/RecordForm', [
            'selectedUser' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'referral_code' => $user->referral_code,
                'points_balance' => $user->points_balance,
            ],
            'eligibleUsers' => [],
            'currentUserPoints' => auth()->user()->points_balance,
            'editing' => true,
        ]);
    }

    /**
     * Search a downline member by email (AJAX).
     * Only returns a user that belongs to the authenticated user's direct downline
     * and does NOT yet have a referral code.
     */
    public function searchByEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)
            ->select('id', 'name', 'email', 'points_balance', 'referral_code')
            ->first();

        if ($user) {
            return response()->json([
                'found' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'points_balance' => $user->points_balance,
                    'referral_code' => $user->referral_code, // This will auto-fill in the form
                    'has_code' => !is_null($user->referral_code) // Add this flag
                ]
            ]);
        }

        return response()->json([
            'found' => false,
            'message' => 'No eligible downline member found with this email. They may already have a code or are not in your direct downline.',
        ]);
    }

    public function update(Request $request, User $user)
    {

        $validated = $request->validate([
            'referral_code' => ['nullable', 'string', 'max:255', Rule::unique('users', 'referral_code')->ignore($user->id)],
            'points' => ['nullable', 'integer', 'min:1', 'max:' . auth()->user()->points_balance],
        ]);

        DB::transaction(function () use ($validated, $user) {
            if (!empty($validated['referral_code'])) {
                $user->update([
                    'referral_code' => $validated['referral_code'],
                    ]);
            }
            if (!empty($validated['points'])) {
                  $user->update([
                    'points_balance' => $validated['points']
                    ]);
            }
        });

        return redirect()->route('downline-referrals.index')
            ->with('success', 'Downline referral updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->referred_by !== auth()->id()) {
            abort(403);
        }

        DB::transaction(function () use ($user) {
            Referral::where('referrer_id', $user->id)
                ->where('status', 'completed')
                ->update(['status' => 'cancelled']);

            $user->update([
                'referral_code' => null,
                'code_assigned_by' => null,
            ]);
        });

        return redirect()->back()
            ->with('success', 'Referral code revoked successfully.');
    }

    public function generateCode(User $user)
    {
        if ($user->referred_by !== auth()->id()) {
            abort(403);
        }

        $code = 'REF' . strtoupper(substr(md5(uniqid($user->id, true)), 0, 8));
        while (User::where('referral_code', $code)->exists()) {
            $code = 'REF' . strtoupper(substr(md5(uniqid($user->id, true)), 0, 8));
        }

        return response()->json(['success' => true, 'referral_code' => $code]);
    }
}