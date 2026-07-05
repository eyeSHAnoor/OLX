<?php

// app/Models/Referral.php
class Referral extends Model
{
    protected $fillable = [
        'referrer_id', 'referred_user_id', 'status', 
        'points_awarded', 'link_code', 'visited_at'
    ];

    protected $casts = [
        'visited_at' => 'datetime',
    ];

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referredUser()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }
}