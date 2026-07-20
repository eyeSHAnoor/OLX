<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Referral;
use App\Data\ReferralData;
use App\Models\UserReferralScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;


class UserReferralController extends Controller
{
    public function index()
    {
        $columns = [
            'id',
            'name',
            'email',
            'referral_code',
            'points_balance',
            'can_assign_code',
            'created_at',
        ];

        $globalSearch = getGlobalSearchFilter([...$columns]);

        // Get users who have referral codes or have referred others
        $referrers = QueryBuilder::for(User::class)
            ->where(function ($query) {
                $query->whereNotNull('referral_code')
                    ->orWhereHas('referralsMade');
            })
            ->withCount(['referralsMade as total_referrals_count' => function ($query) {
                $query->where('status', 'completed');
            }])
            ->withSum(['referralsMade as total_points_earned' => function ($query) {
                $query->where('status', 'completed');
            }], 'points_awarded')
            ->with('referrer')
            ->with(['referralScore']) // Load referral score
            ->defaultSort('-total_points_earned')
            ->allowedSorts([
                ...$columns,
                'total_referrals_count',
                'total_points_earned',
            ])
            ->allowedFilters([
                $globalSearch,
                AllowedFilter::exact('referral_code'),
                AllowedFilter::exact('can_assign_code'),
            ])
            ->paginate(getPaginate())
            ->withQueryString();

        // Get withdrawal requests with user data
        $withdrawalRequests = UserReferralScore::whereIn('status', ['pending', 'approved', 'completed', 'rejected'])
            // ->whereNotNull('requested_amount')
            ->with('user')
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'completed', 'rejected')")
            ->orderBy('created_at', 'desc')
            ->paginate(20, ['*'], 'withdrawals_page')
            ->withQueryString();

        // Get summary stats
        $stats = [
            'total_users_with_codes' => User::whereNotNull('referral_code')->count(),
            'total_referrals' => Referral::where('status', 'completed')->count(),
            'total_points_earned' => Referral::where('status', 'completed')->sum('points_awarded'),
            'total_points_balance' => User::sum('points_balance'),
            'total_withdrawn' => UserReferralScore::sum('total_withdrawn'),
            'total_pending_points' => UserReferralScore::where('status', 'pending')->sum('pending'),
            'pending_withdrawals' => UserReferralScore::where('status', 'pending')->count(),
            'approved_withdrawals' => UserReferralScore::where('status', 'approved')->count(),
            'completed_withdrawals' => UserReferralScore::where('status', 'completed')->count(),
            'rejected_withdrawals' => UserReferralScore::where('status', 'rejected')->count(),
        ];

        return Inertia::render('referral/Index', [
            'referrers' => ReferralData::collect($referrers),
            'withdrawalRequests' => $withdrawalRequests,
            'stats' => $stats,
        ]);
    }

    /**
     * Show form to assign referral code and points to a user
     */
    public function create(Request $request)
    {
        $users = User::select('id', 'name', 'email', 'referral_code', 'points_balance')
            ->orderBy('name')
            ->get();

        $selectedUser = null;
        if ($request->has('user_id')) {
            $selectedUser = User::select('id', 'name', 'email', 'referral_code', 'points_balance')
                ->find($request->user_id);
        }

        return Inertia::render('referral/RecordForm', [
            'users' => $users,
            'selectedUser' => $selectedUser,
            'user' => null,
        ]);
    }

    /**
     * Super Admin: Assign referral code and initial points to a user
     * This does NOT set referred_by - it just gives the user a referral code and points
     */


    public function store(Request $request)
    {
        $currentUser = auth()->user();
        
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'referral_code' => 'required|string|max:255',
            'points_to_award' => 'required|integer|min:0|max:10000',
            'can_assign_code' => 'boolean',  // Add validation
        ]);

        DB::transaction(function () use ($validated, $currentUser) {
            $user = User::findOrFail($validated['user_id']);

            // Check if referral code is already taken
            $existingCode = User::where('referral_code', $validated['referral_code'])
                ->where('id', '!=', $user->id)
                ->exists();
                
            if ($existingCode) {
                throw new \Exception('This referral code is already in use.');
            }

            // Assign referral code
            $user->update([
                'referral_code' => $validated['referral_code'],
                'code_assigned_by' => $currentUser->id,
                'points_balance' => $validated['points_to_award'],
                'can_assign_code' => $validated['can_assign_code'] ?? false,  // Save permission
            ]);
        });

        return redirect()->route('referrals.index')
            ->with('success', 'Referral code and points assigned successfully.');
    }

    /**
     * Show form to edit user's referral code and points
     */
    public function edit(User $user)
    {
        $users = User::select('id', 'name', 'email', 'referral_code', 'points_balance')
            ->orderBy('name')
            ->get();

        return Inertia::render('referral/RecordForm', [
            'users' => $users,
            'user' => $user->load('referrer'),
        ]);
    }

    /**
     * Update user's referral code and/or points balance
     */
    public function update(Request $request, User $user)
    {
        // dd($request->all());
        $validated = $request->validate([
            'referral_code' => 'nullable|string|max:255|unique:users,referral_code,' . $user->id,
            'points_to_adjust' => 'nullable|integer|min:-10000|max:10000', // Can be positive (add) or negative (deduct)
            'can_assign_code' => 'boolean', 
        ]);

        DB::transaction(function () use ($validated, $user) {
            // Update referral code if provided
            if (isset($validated['referral_code']) && !empty($validated['referral_code'])) {
                $user->update(['referral_code' => $validated['referral_code']]);
            }

            // Adjust points if provided
            if (isset($validated['points_to_adjust']) && $validated['points_to_adjust'] != 0) {
                $user->update(['points_balance' => $validated['points_to_adjust']]);
            }

            if (isset($validated['can_assign_code'])) {
                 $user->update(['can_assign_code' => $validated['can_assign_code']]);
            }
        });

        return redirect()->route('referrals.index')
            ->with('success', 'Referral settings updated successfully.');
    }

    /**
     * Remove referral code from a user
     */
    public function destroy(User $user)
    {
        DB::transaction(function () use ($user) {
            // Cancel all referrals made by this user
            Referral::where('referrer_id', $user->id)
                ->where('status', 'completed')
                ->update(['status' => 'cancelled']);

            // Remove referral code
            $user->update(['referral_code' => null]);
        });

        return redirect()->back()
            ->with('success', 'Referral code removed successfully.');
    }

    /**
     * Generate a unique referral code for a user
     */
    public function generateCode(User $user)
    {
        $code = 'REF' . strtoupper(substr(md5(uniqid($user->id, true)), 0, 8));
        
        while (User::where('referral_code', $code)->exists()) {
            $code = 'REF' . strtoupper(substr(md5(uniqid($user->id, true)), 0, 8));
        }

        $user->update(['referral_code' => $code]);

        return response()->json([
            'success' => true,
            'referral_code' => $code,
            'message' => 'Referral code generated successfully.',
        ]);
    }

    /**
     * Display the complete referral tree for a user
     * Shows all direct and indirect referrals in hierarchical structure
     */
    public function referralTree(Request $request, $userId = null)
    {
        // If no user ID provided, use the authenticated user
        if (!$userId) {
            $userId = auth()->id();
        }
        
        $user = User::findOrFail($userId);
        
        // Build the complete referral tree
        $tree = $this->buildReferralTree($user->id);
        
        // Get comprehensive statistics
        $stats = $this->getReferralStats($user->id);
        
        // Check if user is viewing their own tree
        $isOwnTree = auth()->id() == $userId;
        
        return Inertia::render('referral/Client/ReferralTree', [
            'referrer' => $user->only([
                'id',
                'name',
                'email',
                'referral_code',
                'points_balance',
            ]),
            'tree' => $tree,
            'stats' => $stats,
            'isOwnTree' => $isOwnTree,
        ]);
    }

    /**
     * Build the referral tree recursively
     */
    private function buildReferralTree($userId, $level = 0, $maxLevel = 10)
    {
        if ($level >= $maxLevel) {
            return [
                'user' => $this->formatUserData(User::find($userId)),
                'assignees' => [],
                'total_downline' => 0,
                'has_more' => true,
                'level' => $level,
            ];
        }

        $user = User::find($userId);
        if (!$user) {
            return null;
        }

        // Get direct referrals (users who used this user's referral code)
        $directReferrals = User::where('referred_by', $userId)->get();
        
        // Get code assignees (users who received code from this user)
        $codeAssignees = User::where('code_assigned_by', $userId)->get();
        
        // Merge and unique
        $allChildren = $directReferrals->merge($codeAssignees)->unique('id');
        
        $children = [];
        foreach ($allChildren as $child) {
            $childTree = $this->buildReferralTree($child->id, $level + 1, $maxLevel);
            if ($childTree) {
                $children[] = $childTree;
            }
        }

        return [
            'user' => $this->formatUserData($user),
            'assignees' => $children,
            'total_downline' => $this->countAllDescendants($userId),
            'has_more' => false,
            'level' => $level,
        ];
    }

    /**
     * Format user data for tree display
     */
    private function formatUserData(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'points_balance' => $user->points_balance,
            'referral_code' => $user->referral_code,
            'created_at' => $user->created_at,
            'referred_by' => $user->referred_by,
            'code_assigned_by' => $user->code_assigned_by,
            'status' => $user->status,
            'profile_image'=> $user->profile?->profile_image
        ];
    }

    /**
     * Get comprehensive referral statistics for a user
     */
    private function getReferralStats($userId): array
    {
        // Direct referrals count (users who used this user's code)
        $directReferrals = User::where('referred_by', $userId)->count();
        
        // Code assignees count (users who received code from this user)
        $codeAssignees = User::where('code_assigned_by', $userId)->count();
        
        // Total downline (all descendants)
        $totalDownline = $this->countAllDescendants($userId);
        
        // Referral stats from Referral model
        $referralStats = Referral::where('referrer_id', $userId)
            ->select(
                DB::raw('COUNT(*) as total_referrals'),
                DB::raw('SUM(points_awarded) as total_points_earned'),
                DB::raw('COUNT(CASE WHEN status = "completed" THEN 1 END) as completed_count'),
                DB::raw('COUNT(CASE WHEN status = "visited" THEN 1 END) as visited_count'),
                DB::raw('COUNT(CASE WHEN status = "cancelled" THEN 1 END) as cancelled_count')
            )
            ->first();
        
        $totalReferrals = $referralStats->total_referrals ?? 0;
        $completedCount = $referralStats->completed_count ?? 0;
        $visitedCount = $referralStats->visited_count ?? 0;
        $totalVisited = $visitedCount + $completedCount;
        
        $conversionRate = $totalVisited > 0 
            ? round(($completedCount / $totalVisited) * 100, 2) 
            : 0;
        
        // Get points awarded by this user
        $pointsAwarded = Referral::where('referrer_id', $userId)
            ->where('status', 'completed')
            ->sum('points_awarded') ?? 0;
        
        return [
            'direct_referrals' => $directReferrals,
            'code_assignees' => $codeAssignees,
            'total_downline' => $totalDownline,
            'total_referrals' => $totalReferrals,
            'completed_referrals' => $completedCount,
            'visited_referrals' => $visitedCount,
            'cancelled_referrals' => $referralStats->cancelled_count ?? 0,
            'total_points_earned' => $pointsAwarded,
            'conversion_rate' => $conversionRate,
            'total_visited' => $totalVisited,
        ];
    }

    /**
     * Count all descendants (direct and indirect)
     */
    private function countAllDescendants($userId): int
    {
        $count = 0;
        
        // Get direct referrals
        $directReferrals = User::where('referred_by', $userId)->pluck('id');
        
        // Get code assignees
        $codeAssignees = User::where('code_assigned_by', $userId)->pluck('id');
        
        // Merge and unique
        $allChildren = $directReferrals->merge($codeAssignees)->unique();
        
        foreach ($allChildren as $childId) {
            $count++;
            $count += $this->countAllDescendants($childId);
        }
        
        return $count;
    }

    public function userReferrals(User $user)
    {
        // Build the complete code assignment tree
        $tree = $this->buildCodeAssignmentTree($user);

        // Get all referrals (users who used this user's referral code)
        $allReferrals = User::where('referred_by', $user->id)
            ->with(['referrer'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get stats for this user
        $stats = [
            'total_referrals' => Referral::where('referrer_id', $user->id)
                ->where('status', 'completed')
                ->count(),

            'total_visited' => Referral::where('referrer_id', $user->id)
                ->where('status', 'visited')
                ->count(),

            'total_points_earned' => Referral::where('referrer_id', $user->id)
                ->where('status', 'completed')
                ->sum('points_awarded'),

            'conversion_rate' => $this->calculateConversionRate($user->id),

            'total_assignees' => $this->countAllAssignees($user->id),

            'total_tree_assignees' => $this->countAllTreeAssignees($user->id),
        ];

        // Choose the view based on the authenticated user's role
        $view = Auth::user()->hasRole('super_admin')
            ? 'referral/UserReferrals'
            : 'referral/Client/UserReferrals';

        return Inertia::render($view, [
            'referrer' => $user->only([
                'id',
                'name',
                'email',
                'referral_code',
                'points_balance',
                'profile_image'
            ]),
            'tree' => $tree,
            'stats' => $stats,
            'allReferrals' => $allReferrals, // Add this for the referrals list
        ]);
    }

    /**
     * Recursively build the code assignment tree
     */
    private function buildCodeAssignmentTree(User $user, $depth = 0, $maxDepth = 20)
    {
        if ($depth >= $maxDepth) {
            return [
                'user' => $this->formatUserData($user),
                'assignees' => [],
                'has_more' => true,
            ];
        }

        // Get all users who received codes from this user
        $assignees = User::where('code_assigned_by', $user->id)
            ->with(['referralsMade.referredUser'])
            ->get();

        // Build tree for each assignee
        $assigneeTree = $assignees->map(function ($assignee) use ($depth, $maxDepth) {
            // Get referral stats for this assignee
            $referralStats = $this->getUserReferralStats($assignee->id);
            
            // Recursively get their assignees
            $children = $this->buildCodeAssignmentTree($assignee, $depth + 1, $maxDepth);
            
            return [
                'user' => $this->formatUserData($assignee),
                'referral_stats' => $referralStats,
                'assignees' => $children['assignees'] ?? [],
                'has_more' => $children['has_more'] ?? false,
                'total_downline' => $this->countAllTreeAssignees($assignee->id),
            ];
        });

        return [
            'user' => $this->formatUserData($user),
            'assignees' => $assigneeTree->toArray(),
            'has_more' => false,
        ];
    }

    /**
     * Get referral statistics for a user
     */
    private function getUserReferralStats(int $userId): array
    {
        return [
            'total_referrals' => Referral::where('referrer_id', $userId)
                ->where('status', 'completed')
                ->count(),
            'total_points_earned' => Referral::where('referrer_id', $userId)
                ->where('status', 'completed')
                ->sum('points_awarded'),
            'total_assignees' => User::where('code_assigned_by', $userId)->count(),
        ];
    }

    /**
     * Count all direct assignees (users who got code from this user)
     */
    private function countAllAssignees(int $userId): int
    {
        return User::where('code_assigned_by', $userId)->count();
    }

    /**
     * Recursively count all assignees in the tree
     */
    private function countAllTreeAssignees(int $userId): int
    {
        $count = 0;
        $assignees = User::where('code_assigned_by', $userId)->pluck('id');
        
        foreach ($assignees as $assigneeId) {
            $count++; // Count this assignee
            $count += $this->countAllTreeAssignees($assigneeId); // Count their assignees
        }
        
        return $count;
    }

    /**
     * Calculate conversion rate
     */
    private function calculateConversionRate($userId): float
    {
        $totalVisited = Referral::where('referrer_id', $userId)
            ->where('status', 'visited')
            ->count();
        
        $totalCompleted = Referral::where('referrer_id', $userId)
            ->where('status', 'completed')
            ->count();
        
        $total = $totalVisited + $totalCompleted;
        
        if ($total === 0) {
            return 0;
        }
        
        return round(($totalCompleted / $total) * 100, 1);
    }

    public function assignReferralCodes(Request $request)
    {
        $users = User::whereNull('referral_code')->get();
        $count = 0;

        foreach ($users as $user) {
            do {
                $code = 'REF' . Str::upper(Str::random(8));
            } while (User::where('referral_code', $code)->exists());

            $user->update([
                'referral_code' => $code,
                'points_balance' => $user->points_balance + 100,
                'code_assigned_by' =>auth()->user()->id,
            ]);
            
            $count++;
        }

        return redirect()->back()->with('A referral code is assigned to all users');
    }
}