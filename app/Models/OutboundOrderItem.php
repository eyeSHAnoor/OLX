<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class OutboundOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'outbound_order_id',
        'product_id',
        'product_name',
        'brand',
        'sku',
        'barcode',
        'qty',
        'shipped_qty',
        'available_qty',
        'status',
        'price'
    ];

    protected $casts = [
        'scan_log' => 'array', 
    ];

    public function outboundOrder()
    {
        return $this->belongsTo(OutboundOrder::class, 'outbound_order_id');
    }

    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class);
    }

    /**
     * Remaining quantity that can still be shipped
     */
    public function remainingQuantity(): int
    {
        return max(0, $this->qty - $this->shipped_qty);
    }

    public function canShip(int $qty): bool
    {
        return $qty > 0 && $qty <= $this->remainingQuantity();
    }

    public function validateShippingOrFail(int $qty): void
    {
        if (!$this->canShip($qty)) {
            throw ValidationException::withMessages([
                'quantity' => "Cannot ship {$qty}, only {$this->remainingQuantity()} available."
            ]);
        }
    }
}
