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
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, Fileable;

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
        'rank'
    ];

   protected $hidden = [
        'password',
        'remember_token',
    ];


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
}   
