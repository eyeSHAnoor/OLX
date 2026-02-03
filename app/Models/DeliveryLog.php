<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'outbound_order_item_id',
        'outbound_order_id',
        'product_id',
        'scanned_barcode',
        'qty_delivered',
        'status',
        'deliverer_id',
        'delivered_at',
        'metadata'
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
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

    public function deliverer()
    {
        return $this->belongsTo(User::class, 'deliverer_id');
    }
}