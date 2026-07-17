<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    protected $fillable = [
        'referrer_id', 
        'referred_user_id', 
        'status', 
        'points_awarded', 
        'link_code', 
        'visited_at',
        'level',
    ];

    protected $casts = [
        'visited_at' => 'datetime',
        'points_awarded' => 'integer',
    ];

    // Status constants
    const STATUS_VISITED = 'visited';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referredUser()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }
    
    // Scope for completed referrals
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }
    
    // Scope for visited referrals (not yet registered)
    public function scopeVisited($query)
    {
        return $query->where('status', self::STATUS_VISITED);
    }
}