<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignGift extends Model
{
    use HasFactory;

    protected $fillable = [
        'gift_period_id',
        'gift_id',
        'allocated_quantity',
        'remaining_quantity',
        'notes',
    ];

    protected $casts = [
        'allocated_quantity' => 'integer',
        'remaining_quantity' => 'integer',
    ];

    public function period()
    {
        return $this->belongsTo(GiftPeriod::class, 'gift_period_id');
    }

    public function gift()
    {
        return $this->belongsTo(Gift::class);
    }

    public function assignments()
    {
        return $this->hasMany(GiftAssignment::class);
    }

    public function getAssignedQuantityAttribute(): int
    {
        return $this->allocated_quantity - $this->remaining_quantity;
    }

    public function isAvailable(): bool
    {
        return $this->remaining_quantity > 0;
    }
}