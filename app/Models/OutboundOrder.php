<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class OutboundOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'employer_id',
        'customer_id',
        'customer_name',
        'vehicle',
        'notes',
        'status',
        'order_date',
        'picker_id',
        'deliverer_id',
        'picking_started_at',
        'picking_completed_at',
        'ready_for_delivery_at',
        'delivered_at',
        'delivery_barcode',
        'remarks', // Added this
    ];

    protected $casts = [
        'picking_started_at' => 'datetime',
        'picking_completed_at' => 'datetime',
        'ready_for_delivery_at' => 'datetime',
        'delivered_at' => 'datetime',
        'order_date' => 'datetime',
    ];



    protected static function booted()
    {
        static::creating(function ($order) {

            // Only generate if not manually set
            if (! empty($order->order_number)) {
                return;
            }

            $prefix = 'OUT-' . now()->format('Ym'); // OUT-202602

            // Get last order number for current month
            $lastOrder = self::where('order_number', 'like', $prefix . '%')
                ->orderByDesc('order_number')
                ->lockForUpdate() // prevents race condition
                ->first();

            $nextSequence = 1;

            if ($lastOrder) {
                $lastSequence = (int) substr($lastOrder->order_number, -4);
                $nextSequence = $lastSequence + 1;
            }

            $order->order_number = $prefix . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
        });
    }


    /**
     * Outbound order items
     */
    public function items()
    {
        return $this->hasMany(OutboundOrderItem::class, 'outbound_order_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Employer (user who created the order)
     */
    public function employer()
    {
        return $this->belongsTo(User::class, 'employer_id');
    }

    /**
     * Picker (warehouse staff who picks the order)
     */
    public function picker()
    {
        return $this->belongsTo(User::class, 'picker_id');
    }

    /**
     * Deliverer (who delivers the order)
     */
    public function deliverer()
    {
        return $this->belongsTo(User::class, 'deliverer_id');
    }
}