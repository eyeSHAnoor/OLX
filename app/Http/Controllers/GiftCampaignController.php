<?php

namespace App\Http\Controllers;

use App\Models\Gift;
use App\Models\User;
use App\Models\GiftPeriod;
use App\Models\GiftAssignment;
use App\Data\GiftPeriodData;
use App\Data\GiftData;
use App\Models\Message;
use App\Events\MessageSent;
use App\Notifications\NewMessageNotification;
use App\Models\Conversation;
use App\Models\Subscription;
use App\Notifications\GiftWonNotification;
use App\Notifications\GiftDeliveredNotification;
use App\Notifications\GiftCancelledNotification;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Carbon\Carbon;

class GiftCampaignController extends Controller
{
    /**
     * Display a listing of gift periods/campaigns.
     */
    public function index()
    {
        $columns = [
            'name',
            'start_date',
            'end_date',
            'is_active',
            'created_at',
        ];

        $globalSearch = getGlobalSearchFilter([...$columns]);

        $periods = QueryBuilder::for(GiftPeriod::class)
            ->withCount('assignments')
            ->with(['campaignGifts.gift']) 
            
            ->defaultSort('-created_at')
            ->allowedSorts($columns)
            ->allowedFilters([
                $globalSearch,
                AllowedFilter::exact('is_active'),
            ])
            ->paginate(getPaginate())
            ->withQueryString();

        // Get summary of gifts per campaign
        $periods->getCollection()->transform(function ($period) {
            $period->gift_summary = $period->campaignGifts->map(function ($campaignGift) {
                return [
                    'gift_id' => $campaignGift->gift_id,
                    'name' => $campaignGift->gift->name ?? 'Unknown',
                    'image' => $campaignGift->gift->image ?? null,
                    'allocated' => $campaignGift->allocated_quantity,
                    'remaining' => $campaignGift->remaining_quantity,
                    'assigned' => $campaignGift->allocated_quantity - $campaignGift->remaining_quantity,
                ];
            })->values();
            
            return $period;
        });

        return Inertia::render('gift-campaigns/Index', [
            'periods' => $periods,
            'gifts' => Gift::where('is_active', true)
                ->where('quantity', '>', 0)
                ->select('id', 'name', 'quantity')
                ->get(),
        ]);
    }

    /**
     * Show form to create a new gift campaign period.
     */
    public function create()
    {
        return Inertia::render('gift-campaigns/RecordForm', [
            'gifts' => Gift::where('is_active', true)
                ->where('quantity', '>', 0)
                ->select('id', 'name', 'quantity', 'description', 'image')
                ->get(),
        ]);
    }

    /**
     * Store a new gift campaign period with associated gifts.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
            'gifts' => 'required|array|min:1',
            'gifts.*.gift_id' => 'required|exists:gifts,id',
            'gifts.*.quantity' => 'required|integer|min:1',
            'gifts.*.notes' => 'nullable|string|max:500',
        ]);

        // Validate gift quantities availability
        foreach ($validated['gifts'] as $giftData) {
            $gift = Gift::find($giftData['gift_id']);
            if ($gift->quantity < $giftData['quantity']) {
                return redirect()->back()->with('error', 
                    "Insufficient quantity for gift '{$gift->name}'. Available: {$gift->quantity}, Requested: {$giftData['quantity']}");
            }
        }

        DB::transaction(function () use ($validated) {
            // Create the campaign period
            $period = GiftPeriod::create([
                'name' => $validated['name'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'is_active' => $validated['is_active'] ?? true,
            ]);

            // Reserve gift quantities (optional - create a pivot table or track in assignments)
            foreach ($validated['gifts'] as $giftData) {
                // You might want to create a separate table for campaign gifts
                // or store this info in a JSON field on the period
                $period->campaignGifts()->create([
                    'gift_id' => $giftData['gift_id'],
                    'allocated_quantity' => $giftData['quantity'],
                    'remaining_quantity' => $giftData['quantity'],
                    'notes' => $giftData['notes'] ?? null,
                ]);
            }
        });

        return redirect()
            ->route('gift-campaigns.index')
            ->with('success', 'Gift campaign created successfully with ' . count($validated['gifts']) . ' gift(s).');
    }

    /**
     * Edit campaign period and its gifts.
     */
    public function edit(GiftPeriod $period)
    {
        $period->load('campaignGifts.gift');
        
        return Inertia::render('gift-campaigns/RecordForm', [
            'period' => $period,
            'availableGifts' => Gift::where('is_active', true)
                ->select('id', 'name', 'quantity', 'description', 'image')
                ->get(),
        ]);
    }

    /**
     * Update campaign period and its gifts.
     */
    public function update(Request $request, GiftPeriod $period)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
            'gifts' => 'required|array|min:1',
            'gifts.*.gift_id' => 'required|exists:gifts,id',
            'gifts.*.quantity' => 'required|integer|min:1',
            'gifts.*.notes' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($validated, $period) {
            // Update campaign details
            $period->update([
                'name' => $validated['name'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'is_active' => $validated['is_active'] ?? $period->is_active,
            ]);

            // Get existing campaign gifts
            $existingGifts = $period->campaignGifts()->pluck('gift_id')->toArray();
            $updatedGiftIds = collect($validated['gifts'])->pluck('gift_id')->toArray();
            
            // Remove gifts that are no longer in the campaign
            $giftsToRemove = array_diff($existingGifts, $updatedGiftIds);
            if (!empty($giftsToRemove)) {
                // Before removing, check if any assignments exist for these gifts
                $assignedCount = GiftAssignment::where('gift_period_id', $period->id)
                    ->whereIn('gift_id', $giftsToRemove)
                    ->count();
                    
                if ($assignedCount > 0) {
                    throw new \Exception("Cannot remove gifts that have existing assignments.");
                }
                
                $period->campaignGifts()->whereIn('gift_id', $giftsToRemove)->delete();
            }

            // Update or create campaign gifts
            foreach ($validated['gifts'] as $giftData) {
                $campaignGift = $period->campaignGifts()
                    ->where('gift_id', $giftData['gift_id'])
                    ->first();

                if ($campaignGift) {
                    // Calculate new remaining quantity
                    $assignedQuantity = $campaignGift->allocated_quantity - $campaignGift->remaining_quantity;
                    $newRemaining = max(0, $giftData['quantity'] - $assignedQuantity);
                    
                    $campaignGift->update([
                        'allocated_quantity' => $giftData['quantity'],
                        'remaining_quantity' => $newRemaining,
                        'notes' => $giftData['notes'] ?? $campaignGift->notes,
                    ]);
                } else {
                    // Create new campaign gift
                    $period->campaignGifts()->create([
                        'gift_id' => $giftData['gift_id'],
                        'allocated_quantity' => $giftData['quantity'],
                        'remaining_quantity' => $giftData['quantity'],
                        'notes' => $giftData['notes'] ?? null,
                    ]);
                }
            }
        });

        return redirect()
            ->route('gift-campaigns.index')
            ->with('success', 'Gift campaign updated successfully.');
    }

    /**
     * Delete campaign period.
     */
    public function destroy(GiftPeriod $period)
    {
        DB::transaction(function () use ($period) {
            // Return gifts to inventory for non-cancelled assignments
            $assignments = $period->assignments()
                ->where('status', '!=', 'cancelled')
                ->get()
                ->groupBy('gift_id');

            foreach ($assignments as $giftId => $giftAssignments) {
                Gift::where('id', $giftId)->increment('quantity', $giftAssignments->count());
            }

            // Delete all assignments for this period
            $period->assignments()->delete();
            
            // Delete campaign gifts
            $period->campaignGifts()->delete();
            
            // Delete the period
            $period->delete();
        });

        return redirect()
            ->route('gift-campaigns.index')
            ->with('success', 'Gift campaign period deleted successfully.');
    }

    /**
     * Display eligible users for gift assignment.
     */
    public function showEligibleUsers(GiftPeriod $period)
    {
        // Get users with 4 consecutive months of active subscription
        $eligibleUsers = $this->getEligibleUsers();
        
        // Load campaign gifts with remaining quantities
        $campaignGifts = $period->campaignGifts()
            ->with('gift:id,name,image,description')
            ->where('remaining_quantity', '>', 0)
            ->get()
            ->map(function ($campaignGift) {
                return [
                    'id' => $campaignGift->id,
                    'gift_id' => $campaignGift->gift_id,
                    'name' => $campaignGift->gift->name,
                    'image' => $campaignGift->gift->image,
                    'description' => $campaignGift->gift->description,
                    'allocated' => $campaignGift->allocated_quantity,
                    'remaining' => $campaignGift->remaining_quantity,
                    'notes' => $campaignGift->notes,
                ];
            });

        // Get already assigned users for this period
        $assignedUserIds = GiftAssignment::where('gift_period_id', $period->id)
            ->pluck('user_id')
            ->toArray();

        return Inertia::render('gift-campaigns/EligibleUsers', [
            'period' => GiftPeriodData::from($period->load('campaignGifts.gift')),
            'eligibleUsers' => $eligibleUsers,
            'campaignGifts' => $campaignGifts,
            'assignedUserIds' => $assignedUserIds,
            'assignedCount' => $period->assignments()->count(),
            'totalEligible' => $eligibleUsers->count(),
        ]);
    }


    /**
     * Get users with 4 consecutive months of active subscription.
     */
    private function getEligibleUsers()
    {
        $fourMonthsAgo = now()->subMonths(4);
        
        // Get users who have had continuous subscription for at least 4 months
        $users = User::whereHas('subscription', function ($query) use ($fourMonthsAgo) {
            $query->where('payment_status', 'completed')
                ->where('starts_at', '<=', $fourMonthsAgo)
                ->where(function ($q) {
                    $q->where('status', 'active')
                      ->orWhere('ends_at', '>=', now());
                });
        })
        ->with(['activeSubscription.plan', 'profile'])
        ->get()
        ->filter(function ($user) {
            return $this->hasContinuousSubscription($user, 4);
        })
        ->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'profile' => $user->profile ? [
                    'avatar' => $user->profile->avatar ?? null,
                    'city' => $user->profile->city ?? null,
                ] : null,
                'current_plan' => $user->activeSubscription?->plan?->name ?? 'N/A',
                'subscription_started' => $user->activeSubscription?->starts_at?->format('Y-m-d') ?? 'N/A',
                'subscription_ends' => $user->activeSubscription?->ends_at?->format('Y-m-d') ?? 'N/A',
                'total_subscription_months' => $this->calculateContinuousMonths($user),
            ];
        })
        ->values();

        return $users;
    }

    /**
     * Check if user has continuous subscription for specified months.
     */
    private function hasContinuousSubscription($user, $months)
    {
        // Get all completed subscriptions for the user, ordered by start date
        $subscriptions = Subscription::where('user_id', $user->id)
            ->where('payment_status', 'completed')
            ->orderBy('starts_at')
            ->get();

        if ($subscriptions->isEmpty()) {
            return false;
        }

        // Check for continuous coverage of the last N months
        $checkDate = now();
        $startDate = now()->subMonths($months);
        
        // Check if there's a subscription covering each month
        for ($date = $startDate->copy(); $date <= $checkDate; $date->addMonth()) {
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();
            
            $hasCoverage = $subscriptions->contains(function ($subscription) use ($monthStart, $monthEnd) {
                $subStart = Carbon::parse($subscription->starts_at);
                $subEnd = $subscription->ends_at ? Carbon::parse($subscription->ends_at) : now();
                
                return $subStart <= $monthEnd && $subEnd >= $monthStart;
            });
            
            if (!$hasCoverage) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calculate total continuous months of subscription.
     */
    private function calculateContinuousMonths($user)
    {
        $subscriptions = Subscription::where('user_id', $user->id)
            ->where('payment_status', 'completed')
            ->orderBy('starts_at')
            ->get();

        if ($subscriptions->isEmpty()) {
            return 0;
        }

        $earliestStart = Carbon::parse($subscriptions->first()->starts_at);
        $latestEnd = now();

        // Check continuity
        $currentStart = $earliestStart;
        foreach ($subscriptions as $subscription) {
            $subStart = Carbon::parse($subscription->starts_at);
            $subEnd = $subscription->ends_at ? Carbon::parse($subscription->ends_at) : now();
            
            if ($subStart > $currentStart) {
                // Gap found, reset
                $earliestStart = $subStart;
            }
            $currentStart = $subEnd;
        }

        return $earliestStart->diffInMonths($latestEnd);
    }

    /**
     * Assign gifts to eligible users.
     */
    public function assignGifts(Request $request, GiftPeriod $period)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'gift_id' => 'required|exists:gifts,id',
            'notes' => 'nullable|string|max:500',
        ]);

        // Check campaign gift availability
        $campaignGift = $period->campaignGifts()
            ->where('gift_id', $validated['gift_id'])
            ->firstOrFail();

        if ($campaignGift->remaining_quantity < count($validated['user_ids'])) {
            return redirect()->back()->with('error', 
                "Insufficient allocated gifts. Remaining: {$campaignGift->remaining_quantity}, Requested: " . count($validated['user_ids']));
        }

        // Check global gift inventory
        $gift = Gift::findOrFail($validated['gift_id']);
        
        if ($gift->quantity < count($validated['user_ids'])) {
            return redirect()->back()->with('error', 
                "Insufficient gift inventory. Available: {$gift->quantity}, Requested: " . count($validated['user_ids']));
        }

        // Check if any users already assigned in this period
        $existingAssignments = GiftAssignment::where('gift_period_id', $period->id)
            ->whereIn('user_id', $validated['user_ids'])
            ->pluck('user_id')
            ->toArray();

        if (!empty($existingAssignments)) {
            return redirect()->back()->with('error', 
                'Some users already have gifts assigned in this period.');
        }

        DB::transaction(function () use ($validated, $period, $gift, $campaignGift) {
            // Create assignments
            $assignments = collect($validated['user_ids'])->map(function ($userId) use ($validated, $period) {
                return [
                    'gift_period_id' => $period->id,
                    'gift_id' => $validated['gift_id'],
                    'user_id' => $userId,
                    'assigned_by' => auth()->id(),
                    'assigned_at' => now(),
                    'status' => 'candidate',
                    'notes' => $validated['notes'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            GiftAssignment::insert($assignments);

            // Get the created assignments for notifications
            $createdAssignments = GiftAssignment::where('gift_period_id', $period->id)
                ->whereIn('user_id', $validated['user_ids'])
                ->where('gift_id', $validated['gift_id'])
                ->where('status', 'candidate')
                ->get();

            // Send notifications, messages, and emails to each user
            foreach ($createdAssignments as $assignment) {
                $user = User::find($assignment->user_id);
                
                // 1. Send Database + Broadcast + WebPush + Email Notification
                $user->notify(new GiftWonNotification($assignment, $gift, $period));
                
                // 2. Create in-app message using existing Message model (7 C's of communication)
                $this->sendGiftWonMessage($user, $gift, $period, $assignment);
            }

            // Decrease campaign gift remaining quantity
            $campaignGift->decrement('remaining_quantity', count($validated['user_ids']));
            
            // Decrease global gift quantity
            $gift->decrement('quantity', count($validated['user_ids']));
        });

        return redirect()
            ->route('gift-campaigns.eligible-users', $period)
            ->with('success', 'Gifts assigned successfully to ' . count($validated['user_ids']) . ' users. Notifications sent!');
    }

    /**
     * Bulk assign gifts to all eligible users.
     */
    public function bulkAssign(Request $request, GiftPeriod $period)
    {
        $validated = $request->validate([
            'gift_id' => 'required|exists:gifts,id',
            'notes' => 'nullable|string|max:500',
        ]);

        // Check campaign gift availability
        $campaignGift = $period->campaignGifts()
            ->where('gift_id', $validated['gift_id'])
            ->firstOrFail();

        $eligibleUsers = $this->getEligibleUsers();
        $eligibleUserIds = $eligibleUsers->pluck('id')->toArray();

        // Remove already assigned users
        $alreadyAssigned = GiftAssignment::where('gift_period_id', $period->id)
            ->pluck('user_id')
            ->toArray();

        $newUserIds = array_diff($eligibleUserIds, $alreadyAssigned);

        if (empty($newUserIds)) {
            return redirect()->back()->with('info', 'No new eligible users to assign gifts to.');
        }

        if ($campaignGift->remaining_quantity < count($newUserIds)) {
            return redirect()->back()->with('error', 
                "Insufficient allocated gifts. Remaining: {$campaignGift->remaining_quantity}, Eligible users: " . count($newUserIds));
        }

        $gift = Gift::findOrFail($validated['gift_id']);

        if ($gift->quantity < count($newUserIds)) {
            return redirect()->back()->with('error', 
                "Insufficient gift inventory. Available: {$gift->quantity}, Eligible users: " . count($newUserIds));
        }

        DB::transaction(function () use ($newUserIds, $validated, $period, $gift, $campaignGift) {
            $assignments = collect($newUserIds)->map(function ($userId) use ($validated, $period) {
                return [
                    'gift_period_id' => $period->id,
                    'gift_id' => $validated['gift_id'],
                    'user_id' => $userId,
                    'assigned_by' => auth()->id(),
                    'assigned_at' => now(),
                    'status' => 'candidate',
                    'notes' => $validated['notes'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            GiftAssignment::insert($assignments);

            // Get the created assignments for notifications
            $createdAssignments = GiftAssignment::where('gift_period_id', $period->id)
                ->whereIn('user_id', $newUserIds)
                ->where('gift_id', $validated['gift_id'])
                ->where('status', 'candidate')
                ->get();

            // Send notifications, messages, and emails to each user
            foreach ($createdAssignments as $assignment) {
                $user = User::find($assignment->user_id);
                
                // 1. Send Database + Broadcast + WebPush + Email Notification
                $user->notify(new GiftWonNotification($assignment, $gift, $period));
                
                // 2. Create in-app message using existing Message model (7 C's of communication)
                $this->sendGiftWonMessage($user, $gift, $period, $assignment);
            }

            $campaignGift->decrement('remaining_quantity', count($newUserIds));
            $gift->decrement('quantity', count($newUserIds));
        });

        return redirect()
            ->route('gift-campaigns.eligible-users', $period)
            ->with('success', "Gifts bulk assigned successfully to " . count($newUserIds) . " users. Notifications sent!");
    }

    /**
     * Send gift won message using existing Message model
     * Crafted with the 7 C's of Communication:
     * Clear, Concise, Concrete, Correct, Coherent, Complete, Courteous
     */
    private function sendGiftWonMessage($user, $gift, $period, $assignment)
    {
        // Log::info("Sending gift won message to user {$user->id} for gift {$gift->id} in period {$period->id}");
        // Find or create a conversation with the admin/system user
        $adminUser = User::where('email', 'admin@test.com')->first() ?? User::first();
        
        $conversation = Conversation::firstOrCreate(
            [
                'buyer_id' => auth()->id() ?? $adminUser->id,
                'seller_id' => $user->id, 
            ],
            [
                'seller_id' => $user->id,
                'buyer_id' => auth()->id() ?? $adminUser->id,
                'last_message_at' => now(),
            ]
        );

        // // Log::info("Conversation ID for user {$user->id}: {$conversation->id}");

        // Update last message timestamp
        $conversation->update(['last_message_at' => now()]);

        // Craft message using 7 C's of Communication
        $messageBody = $this->craftGiftWonMessage($user, $gift, $period, $assignment);

        // Log::info("Crafted gift won message for user {$user->id}: {$messageBody}");

        // Create the message
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $adminUser->id,
            'body' => $messageBody,
            'type' => 'gift_won',
            'is_read' => false,
        ]);

        $user->notify(
            new NewMessageNotification($conversation, $message)
        );
        // Broadcast message event
        broadcast(new MessageSent($message))->toOthers();
    }

    /**
     * Craft a gift won message using the 7 C's of Communication:
     * 1. CLEAR - Easy to understand, no ambiguity
     * 2. CONCISE - To the point, no unnecessary words
     * 3. CONCRETE - Specific details, not vague
     * 4. CORRECT - Accurate information, no errors
     * 5. COHERENT - Logical flow, well-organized
     * 6. COMPLETE - All necessary information included
     * 7. COURTEOUS - Polite, respectful, and appreciative
     */
    private function craftGiftWonMessage($user, $gift, $period, $assignment): string
    {
        $message = "🎁 *Congratulations {$user->name}!*\n\n";
        
        // CLEAR & CONCISE: Get straight to the point
        $message .= "You have won a special gift — *{$gift->name}* — in our *{$period->name}* loyalty program!\n\n";
        
        // CONCRETE: Specific details about the gift
        $message .= "📋 *Your Gift Details:*\n";
        $message .= "• Gift: {$gift->name}\n";
        if ($gift->description) {
            $message .= "• Description: {$gift->description}\n";
        }
        $message .= "• Campaign: {$period->name}\n";
        $message .= "• Period: {$period->start_date->format('M d, Y')} to {$period->end_date->format('M d, Y')}\n";
        $message .= "• Status: Reserved for you ✅\n\n";
        
        // CORRECT & COHERENT: Accurate next steps in logical order
        $message .= "📝 *What Happens Next:*\n";
        $message .= "1️⃣ Your gift is now reserved and will be processed shortly.\n";
        $message .= "2️⃣ Our team will contact you with delivery details.\n";
        $message .= "3️⃣ You'll receive a confirmation when your gift is on its way.\n\n";
        
        // COMPLETE: All necessary information
        $message .= "📞 *Need Help?*\n";
        $message .= "If you have any questions, simply reply to this message or contact our support team. We're here to help!\n\n";
        
        // COURTEOUS: Show appreciation and respect
        $message .= "💝 *Thank You for Your Loyalty!*\n";
        $message .= "This gift is our way of saying thank you for being a valued subscriber for over 4 months. ";
        $message .= "Your trust and support inspire us to keep delivering the best experience.\n\n";
        
        $message .= "Warm regards,\n";
        $message .= "*The " . config('app.name', 'Our') . " Team* 🚀\n";
        $message .= "_Delivering smiles, one gift at a time._";

        return $message;
    }

    /**
     * Show gift assignments for a period.
     */
    public function assignments(GiftPeriod $period)
    {
        $columns = [
            'assigned_at',
            'status',
            'created_at',
        ];

        $globalSearch = getGlobalSearchFilter(['users.name', 'users.email', 'gifts.name']);

        $assignments = QueryBuilder::for(GiftAssignment::class)
            ->where('gift_period_id', $period->id)
            ->with([
                'user:id,name,email,phone', 
                'user.profile',
                'gift:id,name,image,description', 
                'assignedBy:id,name'
            ])
            ->defaultSort('-assigned_at')
            ->allowedSorts($columns)
            ->allowedFilters([
                $globalSearch,
                AllowedFilter::exact('status'),
                AllowedFilter::exact('gift_id'),
            ])
            ->paginate(getPaginate())
            ->withQueryString()
            ->through(function ($assignment) {
                // Get subscription info for the user
                $user = $assignment->user;
                $activeSubscription = $user->activeSubscription;
                
                // Calculate continuous months
                $subscriptions = Subscription::where('user_id', $user->id)
                    ->where('payment_status', 'completed')
                    ->orderBy('starts_at')
                    ->get();
                
                $months = 0;
                if ($subscriptions->isNotEmpty()) {
                    $earliestStart = Carbon::parse($subscriptions->first()->starts_at);
                    $months = $earliestStart->diffInMonths(now());
                }
                
                return [
                    'id' => $user->id,
                    'assignment_id' => $assignment->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'profile' => $user->profile ? [
                        'avatar' => $user->profile->avatar ?? null,
                        'city' => $user->profile->city ?? null,
                        'address' => $user->profile->address ?? null,
                    ] : null,
                    'gift_name' => $assignment->gift->name,
                    'gift_image' => $assignment->gift->image,
                    'gift_description' => $assignment->gift->description,
                    'assigned_at' => $assignment->assigned_at?->toDateTimeString(),
                    'status' => $assignment->status,
                    'notes' => $assignment->notes,
                    'assigned_by_name' => $assignment->assignedBy->name ?? 'System',
                    'delivered_at' => $assignment->delivered_at?->toDateTimeString(),
                    'subscription_months' => $months,
                    'current_plan' => $activeSubscription?->plan?->name ?? 'N/A',
                ];
            });

        // Get gift statistics for this campaign
        $giftStats = $period->campaignGifts()
            ->with('gift:id,name')
            ->get()
            ->map(function ($campaignGift) {
                return [
                    'gift_name' => $campaignGift->gift->name,
                    'allocated' => $campaignGift->allocated_quantity,
                    'assigned' => $campaignGift->allocated_quantity - $campaignGift->remaining_quantity,
                    'remaining' => $campaignGift->remaining_quantity,
                ];
            });

        return Inertia::render('gift-campaigns/Assignments', [
            'period' => GiftPeriodData::from($period->load('campaignGifts.gift')),
            'assignments' => $assignments,
            'giftStats' => $giftStats,
            'gifts' => Gift::where('is_active', true)->select('id', 'name')->get(),
        ]);
    }

    /**
     * Update assignment status (e.g., mark as delivered, cancelled).
     */
    public function updateAssignmentStatus(Request $request, GiftAssignment $assignment)
    {
        $validated = $request->validate([
            'status' => 'required|in:assigned,delivered,cancelled',
            'notes' => 'nullable|string|max:500',
        ]);

        $oldStatus = $assignment->status;
        $newStatus = $validated['status'];

        // Prevent changing to same status
        if ($oldStatus === $newStatus) {
            return redirect()->back()->with('info', 'Status is already ' . $newStatus . '.');
        }

        DB::transaction(function () use ($validated, $assignment, $oldStatus, $newStatus) {
            // Update the assignment status
            $assignment->update([
                'status' => $newStatus,
                'notes' => $validated['notes'] ?? $assignment->notes,
                'delivered_at' => $newStatus === 'delivered' ? now() : $assignment->delivered_at,
            ]);

            $user = User::find($assignment->user_id);
            $gift = Gift::find($assignment->gift_id);
            $period = GiftPeriod::find($assignment->gift_period_id);
            $adminUser = User::where('email', 'admin@example.com')->first() ?? auth()->user();

            // Handle status change side effects
            if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
                // Return gift to inventory
                $gift->increment('quantity');
                
                // Return to campaign gift allocation
                $campaignGift = $assignment->period->campaignGifts()
                    ->where('gift_id', $assignment->gift_id)
                    ->first();
                    
                if ($campaignGift) {
                    $campaignGift->increment('remaining_quantity');
                }

                // Send cancellation notification
                if ($user) {
                    $user->notify(new GiftCancelledNotification($assignment, $gift, $period));
                    $this->sendGiftStatusMessage($user, $gift, $period, $assignment, $adminUser, 'cancelled');
                }
            }

            if ($newStatus === 'delivered' && $oldStatus !== 'delivered') {
                // Send delivery confirmation notification
                if ($user) {
                    $user->notify(new GiftDeliveredNotification($assignment, $gift, $period));
                    $this->sendGiftStatusMessage($user, $gift, $period, $assignment, $adminUser, 'delivered');
                }
            }

            if ($newStatus === 'candidate' && $oldStatus === 'cancelled') {
                // Re-assigning a cancelled gift
                // Check gift availability again
                if ($gift->quantity < 1) {
                    throw new \Exception("Insufficient gift inventory to re-assign.");
                }
                
                $gift->decrement('quantity');
                
                $campaignGift = $assignment->period->campaignGifts()
                    ->where('gift_id', $assignment->gift_id)
                    ->first();
                    
                if ($campaignGift && $campaignGift->remaining_quantity < 1) {
                    throw new \Exception("Insufficient campaign allocation to re-assign.");
                }
                
                if ($campaignGift) {
                    $campaignGift->decrement('remaining_quantity');
                }
            }
        });

        $statusLabel = ucfirst($newStatus);
        return redirect()
            ->back()
            ->with('success', "Assignment status updated to '{$statusLabel}' successfully.");
    }

    /**
     * Send gift status update message to user
     */
    private function sendGiftStatusMessage($user, $gift, $period, $assignment, $adminUser, $status)
    {
        // Find or create conversation
        $conversation = Conversation::firstOrCreate(
            [
                'seller_id' => $user->id,
                'buyer_id' => auth()->id(),
            ],
            [
                'seller_id' => $user->id,
                'buyer_id' => auth()->id(),
                'last_message_at' => now(),
            ]
        );

        $conversation->update(['last_message_at' => now()]);

        // Craft message based on status using 7 C's
        $messageBody = $this->craftStatusUpdateMessage($user, $gift, $period, $assignment, $status);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $adminUser->id,
            'body' => $messageBody,
            'type' => 'gift_status_update',
            'is_read' => false,
        ]);

        $user->notify(
            new NewMessageNotification($conversation, $message)
        );
        // Broadcast message event
        broadcast(new MessageSent($message))->toOthers();
    }

    /**
     * Craft status update message using 7 C's of Communication
     */
    private function craftStatusUpdateMessage($user, $gift, $period, $assignment, $status): string
    {
        $message = "";
        
        if ($status === 'delivered') {
            // DELIVERED message
            $message = "🚚 *Your Gift Has Been Delivered!*\n\n";
            $message .= "Great news, {$user->name}!\n\n";
            $message .= "Your gift — *{$gift->name}* — from our *{$period->name}* campaign has been successfully delivered.\n\n";
            $message .= "📋 *Delivery Summary:*\n";
            $message .= "• Gift: {$gift->name}\n";
            $message .= "• Campaign: {$period->name}\n";
            $message .= "• Delivered On: " . now()->format('M d, Y') . "\n\n";
            $message .= "✅ *What You Should Do:*\n";
            $message .= "1. Please check that you've received the correct item.\n";
            $message .= "2. If everything looks good, no further action is needed.\n";
            $message .= "3. If you have any concerns, reply to this message within 48 hours.\n\n";
            $message .= "💝 *Enjoy Your Gift!*\n";
            $message .= "Thank you for being a loyal subscriber. We hope you love your gift!\n\n";
            $message .= "Warm regards,\n";
            $message .= "*The " . config('app.name', 'Our') . " Team* 🎉";
            
        } elseif ($status === 'cancelled') {
            // CANCELLED message
            $message = "⚠️ *Gift Assignment Update*\n\n";
            $message .= "Dear {$user->name},\n\n";
            $message .= "We're writing to inform you that your gift assignment for *{$gift->name}* in our *{$period->name}* campaign has been cancelled.\n\n";
            $message .= "📋 *Cancellation Details:*\n";
            $message .= "• Gift: {$gift->name}\n";
            $message .= "• Campaign: {$period->name}\n";
            $message .= "• Cancelled On: " . now()->format('M d, Y') . "\n";
            if ($assignment->notes) {
                $message .= "• Reason: {$assignment->notes}\n";
            }
            $message .= "\n";
            $message .= "🤔 *What Happens Now?*\n";
            $message .= "1. This cancellation may be due to inventory constraints or campaign adjustments.\n";
            $message .= "2. You remain eligible for future campaigns and rewards.\n";
            $message .= "3. Your subscription benefits continue uninterrupted.\n\n";
            $message .= "💬 *Need Clarification?*\n";
            $message .= "We understand this may be disappointing. If you have any questions or concerns, please reply to this message and our support team will assist you promptly.\n\n";
            $message .= "We value your loyalty and look forward to serving you better.\n\n";
            $message .= "Sincerely,\n";
            $message .= "*The " . config('app.name', 'Our') . " Team* 🤝";
        }
        
        return $message;
    }


}