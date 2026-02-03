<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'ship_order_id',
        'internal_order_item_id',
        'product_name',
        'confirmed_quantity_to_ship',
        'type',
        'customer_name',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Parent ship order
    public function shipOrder()
    {
        return $this->belongsTo(ShipOrder::class);
    }

    // Linked item from internal order
    public function internalOrderItem()
    {
        return $this->belongsTo(InternalOrderItem::class);
    }
}
