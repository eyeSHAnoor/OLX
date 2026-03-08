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
        'suspended_until'
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
        ];
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

    public function subscriptionStatus(): string
    {
        if ($this->activeSubscription()->exists()) {
            return 'active';
        }

        if ($this->pendingSubscription()->exists()) {
            return 'pending';
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
}   
