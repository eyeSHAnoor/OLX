<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternalOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'order_number',
        'status',
        'warehouse_comment',
        'created_by',
        // 'confirmed_by',
    ];

    /* --- Relationships --- */

    public function branch()
    {
        return $this->belongsTo(Warehouse::class, 'branch_id');
    }

    public function items()
    {
        return $this->hasMany(InternalOrderItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }


    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function shipOrders()
    {
        return $this->hasMany(ShipOrder::class);
    }


    /* --- Helper --- */
    public function isEditable()
    {
        return $this->status === 'draft';
    }
}

