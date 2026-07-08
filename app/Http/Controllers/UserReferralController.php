<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Referral;
use App\Data\ReferralData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

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
            ->defaultSort('-total_points_earned')
            ->allowedSorts([
                ...$columns,
                'total_referrals_count',
                'total_points_earned',
            ])
            ->allowedFilters([
                $globalSearch,
                AllowedFilter::exact('referral_code'),
            ])
            ->paginate(getPaginate())
            ->withQueryString();

        return Inertia::render('referral/Index', [
            'referrers' => ReferralData::collect($referrers),
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
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'referral_code' => 'required|string|max:255|unique:users,referral_code',
            'points_to_award' => 'required|integer|min:0|max:10000',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::findOrFail($validated['user_id']);

            // Assign referral code
            $user->update([
                'referral_code' => $validated['referral_code'],
            ]);

            // Add initial points to user's balance
            if ($validated['points_to_award'] > 0) {
                $user->increment('points_balance', $validated['points_to_award']);
            }
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
        $validated = $request->validate([
            'referral_code' => 'nullable|string|max:255|unique:users,referral_code,' . $user->id,
            'points_to_adjust' => 'nullable|integer|min:-10000|max:10000', // Can be positive (add) or negative (deduct)
        ]);

        DB::transaction(function () use ($validated, $user) {
            // Update referral code if provided
            if (isset($validated['referral_code']) && !empty($validated['referral_code'])) {
                $user->update(['referral_code' => $validated['referral_code']]);
            }

            // Adjust points if provided
            if (isset($validated['points_to_adjust']) && $validated['points_to_adjust'] != 0) {
                $user->increment('points_balance', $validated['points_to_adjust']);
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
     * Process referral when someone uses a referral link
     * This should be called from your registration/login controller
     */
    public function processReferralLink($referralCode, User $newUser)
    {
        // Find the referrer by their referral code
        $referrer = User::where('referral_code', $referralCode)->first();
        
        if (!$referrer) {
            return false;
        }

        // Check if user already has a referrer
        if ($newUser->referred_by) {
            return false;
        }

        // Don't allow self-referral
        if ($referrer->id === $newUser->id) {
            return false;
        }

        DB::transaction(function () use ($referrer, $newUser) {
            // Set who referred this user
            $newUser->update(['referred_by' => $referrer->id]);

            // Create referral record
            Referral::create([
                'referrer_id' => $referrer->id,
                'referred_user_id' => $newUser->id,
                'status' => 'completed',
                'points_awarded' => 100, // Or whatever your default referral points are
                'link_code' => $referrer->referral_code,
                'visited_at' => now(),
            ]);

            // Award points - referrer gets full points, new user gets half
            $referrer->increment('points_balance', 100);
            $newUser->increment('points_balance', 50); // Half for the referred user
        });

        return true;
    }

    /**
     * Show all referrals made by a specific user
     */
    public function userReferrals(User $user)
    {
        $referrals = Referral::where('referrer_id', $user->id)
            ->with(['referredUser:id,name,email,points_balance,created_at'])
            ->where('status','completed')
            ->orderBy('created_at', 'desc')
            ->paginate(getPaginate())
            ->withQueryString();

        // Get stats for this user
        $stats = [
            'total_referrals' => Referral::where('referrer_id', $user->id)->where('status', 'completed')->count(),
            'total_visited' => Referral::where('referrer_id', $user->id)->where('status', 'visited')->count(),
            'total_points_earned' => Referral::where('referrer_id', $user->id)
                ->where('status', 'completed')
                ->sum('points_awarded'),
            'conversion_rate' => $this->calculateConversionRate($user->id),
        ];

        return Inertia::render('referral/UserReferrals', [
            'referrer' => $user->only(['id', 'name', 'email', 'referral_code', 'points_balance']),
            'referrals' => $referrals,
            'stats' => $stats,
        ]);
    }

    /**
     * Calculate conversion rate (visited vs completed)
     */
    private function calculateConversionRate($userId): float
    {
        $totalVisited = Referral::where('referrer_id', $userId)
            ->whereIn('status', ['visited', 'completed'])
            ->count();
        
        if ($totalVisited === 0) {
            return 0;
        }
        
        $totalCompleted = Referral::where('referrer_id', $userId)
            ->where('status', 'completed')
            ->count();
        
        return round(($totalCompleted / $totalVisited) * 100, 1);
    }
}