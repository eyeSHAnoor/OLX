<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'shipment_date',
        'total_items',
        'internal_order_id',
    ];

    protected $casts = [
        'shipment_date' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Parent internal order
    public function internalOrder()
    {
        return $this->belongsTo(InternalOrder::class);
    }

    // Ship order items
    public function items()
    {
        return $this->hasMany(ShipOrderItem::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    // Auto calculate total items
    public function updateTotalItems()
    {
        $this->total_items = $this->items()->sum('confirmed_quantity_to_ship');
        $this->save();
    }
}
