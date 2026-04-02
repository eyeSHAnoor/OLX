<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'buyer_id',
        'seller_id',
        'ad_id',
        'price',
        'status'
    ];

    // 🔗 Relationships

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }

    // Helpers
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public static function sellerStats($sellerId)
    {
        $stats = self::where('seller_id', $sellerId)
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_orders,
                SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled_orders,
                SUM(CASE WHEN status = "completed" THEN price * qty ELSE 0 END) as completed_amount
            ')
            ->first();

        $completionRate = $stats->total_orders > 0
            ? round(($stats->completed_orders / $stats->total_orders) * 100, 2)
            : 0;

        $cancelRate = $stats->total_orders > 0
            ? round(($stats->cancelled_orders / $stats->total_orders) * 100, 2)
            : 0;

        return [
            'total_orders' => $stats->total_orders,
            'completed_orders' => $stats->completed_orders,
            'cancelled_orders' => $stats->cancelled_orders,
            'completed_amount' => $stats->completed_amount,
            'completion_rate' => $completionRate,
            'cancel_rate' => $cancelRate,
        ];
    }

    // Add broadcast method for real-time notifications
    public function broadcastOrderAccepted()
    {
        broadcast(new \App\Events\OrderAccepted($this));
    }
}
