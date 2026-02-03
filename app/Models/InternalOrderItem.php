<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'internal_order_id',
        'product_id',
        'product_name',
        'catalog_number',
        'unit',
        'quantity',
        'status',
        'comments',
        'customer_name',
        'received_quantity',
        'confirmed_quantity',
        'correction_quantity',
        'confirmed_by', // user ID who confirmed
        'confirmed_at', // timestamp
        'created_by',
    ];

    /* -------------------------------- Relations ------------------------------- */

    public function order()
    {
        return $this->belongsTo(InternalOrder::class, 'internal_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function confirmer()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function histories()
    {
        return $this->hasMany(InternalOrderHistory::class, 'internal_order_item_id')->orderBy('created_at', 'asc');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function shipOrderItems()
    {
        return $this->hasMany(ShipOrderItem::class);
    }
    /* --------------------------------- Accessors ------------------------------ */

    public function getStatusLabelAttribute()
    {
        return ucfirst($this->status);
    }

    public function getUndeliveredQuantityAttribute(): int
    {
        // delivered + correction - requested
        return ($this->confirmed_quantity + $this->correction_quantity) - $this->quantity;
    }

    /* --------------------------------- Helpers -------------------------------- */

    public function isFullyDelivered(): bool
    {
        return $this->undelivered_quantity === 0;
    }

    public function isPartiallyDelivered(): bool
    {
        return $this->undelivered_quantity < 0;
    }
}
