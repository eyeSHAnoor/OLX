<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'type', // in / out / reserved
        'quantity',
        'reserved_quantity',
        'total_quantity',
        'inbound_order_item_id',
        'outbound_order_item_id',
        'warehouse_location_id',
        'movement_date',
        'remarks',
    ];

    protected $casts = [
        'movement_date' => 'datetime', // <-- this ensures it's a Carbon object
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function inboundItem()
    {
        return $this->belongsTo(OrderItem::class, 'inbound_order_item_id');
    }

    public function outboundItem()
    {
        return $this->belongsTo(OutboundOrderItem::class, 'outbound_order_item_id');
    }

    public function warehouseLocation()
    {
        return $this->belongsTo(WarehousePosition::class, 'warehouse_location_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::creating(function ($movement) {
            if (Auth::check() && !$movement->user_id) {
                $movement->user_id = Auth::id();
            }
        });
    }
}
