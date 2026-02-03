<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrier extends Model
{
    use HasFactory;

    protected $fillable = [
        'tracking_order_id',
        'tracking_id',
        'forwarder',
        'carrier',
        'expected_delivery_date',
        'shipped_at',
        'delivered_at',
    ];

    protected $casts = [
        'expected_delivery_date' => 'date:Y-m-d',
        'shipped_at' => 'date:Y-m-d',
        'delivered_at' => 'date:Y-m-d',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function trackingOrder()
    {
        return $this->belongsTo(TrackingOrder::class);
    }
}
