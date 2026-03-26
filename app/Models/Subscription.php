<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'plan_id',
        'starts_at',
        'ends_at',
        'payment_status',
        'receipt_image',
        'payment_method',
        'transaction_id',
        'payment_gateway',
        'amount_paid',
        'payment_data',
        'status'
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'payment_data' => 'array',
        'amount_paid' => 'decimal:2'
    ];


    protected static function booted()
    {
        // your existing creating logic
        static::creating(function ($subscription) {
            if (empty($subscription->starts_at)) {
                $subscription->starts_at = now();
            }
            if (empty($subscription->ends_at) && $subscription->plan) {
                $subscription->ends_at = now()->addDays($subscription->plan->duration_days);
            }
        });

        // static::addGlobalScope('not_expired', function (Builder $builder) {
        //     $builder->where(function ($query) {
        //         $query->where('status', '!=', 'expired')
        //             ->where(function ($q) {
        //                 $q->whereNull('ends_at')
        //                     ->orWhere('ends_at', '>', now());
        //             });
        //     });
        // });
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isActive()
    {
        return
            $this->status === 'active' &&
            $this->payment_status === 'completed' && 
            $this->ends_at && 
            $this->ends_at->isFuture();
    }

    public function isPending()
    {
        return $this->payment_status === 'pending' && 
               $this->ends_at && 
               $this->ends_at->isFuture();
    }
}