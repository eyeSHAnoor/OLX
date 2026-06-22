<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'price',
        'discount',
        'duration_days',
        'description',
        'features',
        'is_popular',
        'sort_order'
    ];

    protected $casts = [
        'features' => 'array',
        'is_popular' => 'boolean',
        'discount' => 'decimal:2',
        'price' => 'decimal:2'
    ];

     public function permissions()
    {
        return $this->belongsToMany(
            SubscriptionPermission::class,
            'plan_permission',
            'plan_id',
            'subscription_permission_id'
        );
    }

    public function hasPermission($permissionName)
    {
        return $this->permissions->contains('name', $permissionName);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function getDailyPriceAttribute()
    {
        return $this->price / $this->duration_days;
    }
}