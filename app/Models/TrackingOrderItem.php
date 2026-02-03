<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackingOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'tracking_order_id',
        'product_id',
        'item_name',
        'sku',
        'quantity',
        'delivered_qty',
        'customer_type',
        'customer_name',
        'price',
        'po_number',
        'status',
        'shipped_at',
        'delivered_at',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'expected_delivery_date' => 'date',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function order()
    {
        return $this->belongsTo(TrackingOrder::class, 'tracking_order_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    public function isPartiallyDelivered(): bool
    {
        return $this->status === 'partially_delivered';
    }

    public function markAsShipped()
    {
        $this->update(['status' => 'shipped']);
    }

    public function markAsDelivered()
    {
        $this->update(['status' => 'delivered']);
    }
}
