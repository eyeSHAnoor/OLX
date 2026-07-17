<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Data\BranchData;
use App\Traits\Fileable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use NotificationChannels\WebPush\HasPushSubscriptions;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, Fileable, HasPushSubscriptions;

    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'phone',
        'warning_count',
        'suspended_until',
        'verification_code',
        'verification_code_expires_at',
        'terms_accepted',
        'terms_accepted_at',
        'rank',
        'referral_code',
        'referred_by',
        'points_balance',
        'code_assigned_by'
    ];

   protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = ['profile_image'];

    public function getProfileImageAttribute()
    {
        return $this->profile?->profile_image;
    }
    
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'suspended_until' => 'datetime',
            'verification_code_expires_at' => 'datetime',
        ];
    }

    // Add method to generate verification code
     public function generateVerificationCode(): string
    {
        $this->verification_code = sprintf("%06d", mt_rand(1, 999999));
        $this->verification_code_expires_at = now()->addMinutes(2); // 2 minutes expiration
        $this->save();
        
        return $this->verification_code;
    }

    // Add method to verify code
    public function verifyCode(string $code): bool
    {
        return $this->verification_code === $code && 
            $this->verification_code_expires_at && 
            $this->verification_code_expires_at->isFuture();
    }

    /**
     * Clear verification code and mark email as verified
     */
    public function markEmailAsVerified(): void
    {
        $this->verification_code = null;
        $this->verification_code_expires_at = null;
        $this->email_verified_at = now();
        $this->save();
    }

    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function preferences(): HasOne
    {
        return $this->hasOne(UserPreference::class);
    }

    public function notificationSettings(): HasMany
    {
        return $this->hasMany(UserNotificationSetting::class);
    }

    // app/Models/User.php
    public function notifications()
    {
        return $this->morphMany(\Illuminate\Notifications\DatabaseNotification::class, 'notifiable')
                    ->orderBy('created_at', 'desc');
    }

    public function unreadNotifications()
    {
        return $this->notifications()->whereNull('read_at');
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    public function latestSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    // Active subscription: payment completed and ends_at is in the future
    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)
            ->where('payment_status', 'completed')
            ->where('ends_at', '>', Carbon::now());
    }

    // Pending subscription: payment submitted but not completed
    public function pendingSubscription()
    {
        return $this->hasOne(Subscription::class)
            ->where('payment_status', 'pending');
    }

    public function expiredSubscription()
    {
        // Disable the global scope so we can fetch expired subscriptions
        return $this->hasOne(Subscription::class)->withoutGlobalScope('not_expired')
                    ->where(function ($q) {
                        $q->where('status', 'expired')
                        ->orWhere('ends_at', '<', now());
                    });
    }

    public function subscriptionStatus(): string
    {
        if ($this->activeSubscription()->exists()) {
            return 'active';
        }

        if ($this->pendingSubscription()->exists()) {
            return 'pending';
        }

        if ($this->expiredSubscription()->exists()) {
            return 'expired';
        }

        return 'none';
    }

    public function receivedRatings()
    {
        return $this->hasMany(Rating::class, 'rated_user_id');
    }

    public function givenRatings()
    {
        return $this->hasMany(Rating::class, 'rater_id');
    }

    public function averageRating(): float
    {
        return round($this->receivedRatings()->avg('rating') ?? 0, 1);
    }

    public function ratingsCount(): int
    {
        return $this->receivedRatings()->count();
    }

    public function favoriteAds()
    {
        return $this->belongsToMany(Ad::class, 'ad_favorites')
            ->withTimestamps();
    }

    public function reportsMade(): HasMany
    {
        return $this->hasMany(UserReport::class, 'reported_by');
    }

    public function reportsReceived(): HasMany
    {
        return $this->hasMany(UserReport::class, 'reported_user_id');
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended' && $this->suspended_until > now();
    }

    public function sellerOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'seller_id');
    }

    public function orderStats()
    {
        return Order::sellerStats($this->id);
    }

    public function completedOrdersCount(): int
    {
        return $this->sellerOrders()
            ->where('status', 'accepted')
            ->count();
    }

    public function calculateRank(): int
    {
        return floor($this->completedOrdersCount() / 10);
    }

    public function ads(): HasMany
    {
        return $this->hasMany(Ad::class, 'user_id'); 
    }

    /**
     * Check if the user has a specific plan permission (from their active subscription).
     *
     * @param string $permission
     * @return bool
     */
    public function hasPlanPermission(string $permission): bool
    {
        // Load the active subscription with its plan permissions if not already loaded
        if (!$this->relationLoaded('activeSubscription')) {
            $this->load('activeSubscription.plan.permissions');
        }

        $subscription = $this->activeSubscription;
        if (!$subscription || !$subscription->plan) {
            return false;
        }

        return $subscription->plan->permissions->contains('name', $permission);
    }

    public function giftAssignments()
    {
        return $this->hasMany(GiftAssignment::class);
    }

    public function assignedGifts()
    {
        return $this->hasMany(GiftAssignment::class, 'assigned_by');
    }

     public function referralsMade()
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    public function referralReceived()
    {
        return $this->hasOne(Referral::class, 'referred_user_id');
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function addPoints($points)
    {
        $this->points_balance += $points;
        $this->save();
    }

    public function rootReferrer(): ?User
    {
        $ancestor = $this;
        while ($ancestor->referred_by) {
            $ancestor = $ancestor->referrer; // via belongsTo
        }
        return $ancestor->referral_code ? $ancestor : null; // must have a code
    }

    public function codeAssigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'code_assigned_by');
    }

    // Users to whom THIS user assigned a code
    public function codeAssignees(): HasMany
    {
        return $this->hasMany(User::class, 'code_assigned_by');
    }
}   
