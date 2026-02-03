<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'inbound_order_id',
        'product_id',
        'unique_id',
        'sku',
        'category',
        'item_name',
        'barcode',
        'supplies_barcode',
        'rfid_barcode',
        'qty',
        'received_qty',
        'unit',
        'purchase_price',
        'rebate',
        'markup',
        'tax',
        'sales_price',
        'max_discount',
        'kpd_code',
        'status',
        'net_purchase_price',
        'total_price',
        'received_at',
    ];

    public function inboundOrder()
    {
        return $this->belongsTo(InboundOrder::class, 'inbound_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'inbound_order_item_id');
    }

    public function inventories()
    {
        return $this->hasManyThrough(
            Inventory::class,
            Product::class,
            'id',         // Product primary key
            'product_id', // Inventory foreign key
            'product_id', // OrderItem foreign key
            'id'          // Product primary key
        );
    }


    // Automatically generate unique barcode before creating
    protected static function booted()
    {
        static::creating(function ($item) {

            if (empty($item->barcode)) {

                if (! $item->relationLoaded('inboundOrder') && ! $item->inboundOrder) {
                    throw new \RuntimeException('Inbound order must be set before creating OrderItem');
                }

                $orderNo = $item->inboundOrder->order_number;

                if (empty($orderNo) || empty($item->sku)) {
                    throw new \RuntimeException('Order number and SKU are required for barcode generation');
                }

                $item->barcode = self::generateUniqueBarcode(
                    $orderNo,
                    $item->sku
                );
            }

            if (empty($item->unique_id)) {
                $item->unique_id = self::generateUniqueId();
            }
        });
    }

    private static function generateUniqueId(): string
    {
        return 'W'.str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
    }

    private static function generateUniqueBarcode(string $orderNo, string $sku): string
    {
        return "RIV-{$orderNo}-{$sku}";
    }




    public function allocatedQuantity(): int
    {
        // TODO: Implement allocation tracking when the relationship is set up
        // For now, return 0 as allocations are not being tracked via Inventory model
        return 0;
    }

    /**
     * Remaining quantity that can still be allocated (order qty - allocated).
     */
    public function remainingQuantity(): int
    {
        // Since allocations aren't tracked yet, remaining is the full order qty
        return (int) $this->qty;
    }

    /**
     * Returns true if $qty can be allocated without exceeding the order qty.
     */
    public function canAllocate(int $qty): bool
    {
        if ($qty <= 0) {
            return false;
        }

        return $qty <= $this->remainingQuantity();
    }

    /**
     * Validate allocation and throw a ValidationException on failure.
     */
    public function validateAllocationOrFail(int $qty): void
    {
        if (! $this->canAllocate($qty)) {
            throw ValidationException::withMessages([
                'quantity' => ["Allocation of {$qty} would exceed available quantity ({$this->remainingQuantity()})."],
            ]);
        }
    }
}
