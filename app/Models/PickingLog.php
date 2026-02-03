<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickingLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'outbound_order_item_id',
        'outbound_order_id',
        'product_id',
        'scanned_barcode',
        'qty_picked',
        'status',
        'picker_id',
        'picked_at',
        'metadata'
    ];

    protected $casts = [
        'picked_at' => 'datetime',
        'metadata' => 'array'
    ];

    public function outboundOrderItem()
    {
        return $this->belongsTo(OutboundOrderItem::class);
    }

    public function outboundOrder()
    {
        return $this->belongsTo(OutboundOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function picker()
    {
        return $this->belongsTo(User::class, 'picker_id');
    }

     /**
     * Customer details through outbound order
     */
    public function customer()
    {
        // This uses a "hasOneThrough" to get customer via outboundOrder
        return $this->hasOneThrough(
            Customer::class,        // The final model we want
            OutboundOrder::class,   // The intermediate model
            'id',                   // Foreign key on OutboundOrder (local key on PickingLog is outbound_order_id)
            'id',                   // Foreign key on Customer (local key on OutboundOrder is customer_id)
            'outbound_order_id',    // Local key on PickingLog (the column pointing to outbound_order)
            'customer_id'           // Local key on OutboundOrder (column pointing to customer)
        );
    }
}