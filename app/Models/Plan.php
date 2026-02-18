<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'price',
        'duration_days',
        'description',
        'features',
        'is_popular',
        'sort_order'
    ];

    protected $casts = [
        'features' => 'array',
        'is_popular' => 'boolean',
        'price' => 'decimal:2'
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function getDailyPriceAttribute()
    {
        return $this->price / $this->duration_days;
    }
}