<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceivingSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'product_name',
        'order_number',
        'barcode',
        'quantity',
        'product_id',
        'order_id',
        'inbound_order_item'
    ];

    public function inboundOrderItem()
    {
        return $this->belongsTo(OrderItem::class, 'inbound_order_item');
    }
}
