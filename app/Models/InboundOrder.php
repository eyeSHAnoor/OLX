<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InboundOrder extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::creating(function ($order) {

            // If order_number is already set, don't overwrite it
            if (! empty($order->order_number)) {
                return;
            }

            $prefix = 'IN-' . now()->format('Ym'); // IN-202602

            // Lock rows for this prefix to avoid duplicates
            $lastOrder = self::where('order_number', 'like', $prefix . '%')
                ->orderByDesc('order_number')
                ->lockForUpdate()
                ->first();

            $nextSequence = 1;

            if ($lastOrder) {
                $lastSequence = (int) substr($lastOrder->order_number, -4);
                $nextSequence = $lastSequence + 1;
            }

            $order->order_number = $prefix . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
        });
    }

    protected $fillable = [
        'order_number',
        'supplier_name',
        'supplier_invoice_no',
        'supplier_code',
        'document_type',
        'due_date',
        'expected_date',
        'invoice_date',
        'payment_method',
        'bank_account',
        'currency',
        'total_amount',
        'tax_amount',
        'notes',
        'supplier_tax_id',
        'supplier_address',
        'status',
        'created_by',
        'updated_by',
        'verified',
        'batch_id',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'inbound_order_id');
    }
}

