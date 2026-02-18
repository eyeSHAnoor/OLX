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
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, Fileable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'phone'
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
        return $this->hasMany(Notification::class, 'requested_by');
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

}
