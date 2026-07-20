<?php

namespace App\Http\Controllers;

use App\Models\UserReferralScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class WithdrawalController extends Controller
{
    /**
     * Show withdrawal page with user's score and history
     */
    public function index()
    {
        $user = auth()->user();
        $score = UserReferralScore::where('user_id', $user->id)->first();

        if (!$score) {
            // Create if doesn't exist
            $score = UserReferralScore::create([
                'user_id' => $user->id,
                'total_earned' => 0,
                'total_withdrawn' => 0,
                'available' => 0,
                'pending' => 0,
                'status' => 'active',
            ]);
        }

        // Get withdrawal history (all status changes)
        $history = UserReferralScore::where('user_id', $user->id)
            ->whereNotNull('requested_amount')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('withdrawal/Index', [
            'score' => $score,
            'history' => $history,
            'availablePoints' => $score->available,
            'totalEarned' => $score->total_earned,
            'totalWithdrawn' => $score->total_withdrawn,
            'hasPending' => $score->hasPendingWithdrawal(),
        ]);
    }

    /**
     * Store a new withdrawal request
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Get user's score
        $score = UserReferralScore::where('user_id', $user->id)->first();
        
        if (!$score) {
            return back()->with('error', 'Withdrawal system not initialized.');
        }

        // Check if user already has a pending withdrawal
        if ($score->hasPendingWithdrawal()) {
            return back()->with('error', 'You already have a pending withdrawal request.');
        }

        // Validate request
        $validated = $request->validate([
            'points' => 'required|integer',
            'payment_method' => 'required|string|in:bank_transfer,easypaisa,jazzcash',
            'payment_details' => 'required|array',
            'payment_details.account_number' => 'required_if:payment_method,bank_transfer|string|nullable',
            'payment_details.account_holder' => 'required_if:payment_method,bank_transfer|string|nullable',
            'payment_details.bank_name' => 'required_if:payment_method,bank_transfer|string|nullable',
            'payment_details.phone_number' => 'required_if:payment_method,easypaisa,jazzcash|string|nullable',
        ]);

        // Check if user has enough points
        if ($validated['points'] > $score->available) {
            return back()->with('error', 'Insufficient points available.');
        }

        // Calculate amount (1 point = 1 unit currency)
        $amount = $validated['points'];

        DB::transaction(function () use ($score, $validated, $amount) {
            $score->requestWithdrawal(
                $validated['points'],
                $amount,
                $validated['payment_method'],
                $validated['payment_details']
            );
        });

        return redirect()->route('withdrawals.index')
            ->with('success', 'Withdrawal request submitted successfully.');
    }

    /**
     * Admin: Show all withdrawal requests
     */
    public function adminIndex()
    {
        // $this->authorize('manage_withdrawals');

        $pending = UserReferralScore::where('status', 'pending')
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();

        $history = UserReferralScore::whereIn('status', ['approved', 'completed', 'rejected'])
            ->whereNotNull('requested_amount')
            ->with('user')
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        $stats = [
            'pending_count' => $pending->count(),
            'total_approved' => UserReferralScore::where('status', 'approved')->count(),
            'total_completed' => UserReferralScore::where('status', 'completed')->count(),
            'total_rejected' => UserReferralScore::where('status', 'rejected')->count(),
            'total_points_withdrawn' => UserReferralScore::sum('total_withdrawn'),
        ];

        return Inertia::render('admin/Withdrawals/Index', [
            'pending' => $pending,
            'history' => $history,
            'stats' => $stats,
        ]);
    }

    /**
     * Admin: Show specific withdrawal
     */
    public function show(UserReferralScore $withdrawal)
    {
        // $this->authorize('manage_withdrawals');

        return Inertia::render('admin/Withdrawals/Show', [
            'withdrawal' => $withdrawal->load('user'),
        ]);
    }

    /**
     * Admin: Approve a withdrawal
     */
    public function approve(Request $request, UserReferralScore $withdrawal)
    {
        // // // $this->authorize('manage_withdrawals');

        $validated = $request->validate([
            'transaction_id' => 'required|string|max:255',
            'admin_notes' => 'nullable|string|max:500',
            'proof_images' => 'nullable|array',
            'proof_images.*' => 'image|max:5120', // 5MB each
        ]);

        DB::transaction(function () use ($request, $withdrawal, $validated) {
            // ✅ Get existing proofs first
            $proofs = $withdrawal->proof_images ?? [];
            
            // ✅ Append new proof images
            if ($request->hasFile('proof_images')) {
                foreach ($request->file('proof_images') as $image) {
                    $path = $image->store('withdrawals/proofs/' . $withdrawal->id, 'public');
                    $proofs[] = $path; // ✅ Append, not replace
                }
            }

            $withdrawal->approveWithdrawal(
                $validated['transaction_id'],
                $proofs, // ✅ Pass all proofs (existing + new)
                $validated['admin_notes'] ?? null
            );
        });

        return back()->with('success', 'Withdrawal approved successfully.');
    }

    /**
     * Admin: Complete a withdrawal (payment sent)
     */
    public function complete(Request $request, UserReferralScore $withdrawal)
    {
        // $this->authorize('manage_withdrawals');

        $validated = $request->validate([
            'proof_images' => 'nullable|array',
            'proof_images.*' => 'image|max:5120',
            'admin_notes' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($request, $withdrawal, $validated) {
            // Upload additional proof images
            $proofs = $withdrawal->proof_images ?? [];
            if ($request->hasFile('proof_images')) {
                foreach ($request->file('proof_images') as $image) {
                    $path = $image->store('withdrawals/proofs/' . $withdrawal->id, 'public');
                    $proofs[] = $path;
                }
            }

            $withdrawal->proof_images = $proofs;
            if ($validated['admin_notes']) {
                $withdrawal->admin_notes = $validated['admin_notes'];
            }
            $withdrawal->completeWithdrawal();
        });

        return back()->with('success', 'Withdrawal completed successfully.');
    }

    /**
     * Admin: Reject a withdrawal
     */
    public function reject(Request $request, UserReferralScore $withdrawal)
    {
        // $this->authorize('manage_withdrawals');

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        DB::transaction(function () use ($withdrawal, $validated) {
            $withdrawal->rejectWithdrawal($validated['reason']);
        });

        return back()->with('success', 'Withdrawal rejected successfully.');
    }

    /**
     * User: Confirm receipt of withdrawal
     */
    public function confirm(UserReferralScore $withdrawal)
    {
        // Ensure user owns this withdrawal
        if ($withdrawal->user_id !== auth()->id()) {
            abort(403);
        }

        if ($withdrawal->status !== 'completed') {
            return back()->with('error', 'This withdrawal is not completed yet.');
        }

        $withdrawal->confirmReceipt();

        return back()->with('success', 'Withdrawal confirmed successfully.');
    }

    /**
     * Get withdrawal status for API
     */
    public function status()
    {
        $user = auth()->user();
        $score = UserReferralScore::where('user_id', $user->id)->first();

        if (!$score) {
            return response()->json([
                'available' => 0,
                'pending' => 0,
                'total_earned' => 0,
                'total_withdrawn' => 0,
                'has_pending' => false,
                'status' => 'active',
            ]);
        }

        return response()->json([
            'available' => $score->available,
            'pending' => $score->pending,
            'total_earned' => $score->total_earned,
            'total_withdrawn' => $score->total_withdrawn,
            'has_pending' => $score->hasPendingWithdrawal(),
            'status' => $score->status,
            'requested_amount' => $score->requested_amount,
            'requested_amount' => $score->requested_amount,
        ]);
    }
}