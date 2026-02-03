<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalOrderHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'internal_order_id',
        'internal_order_item_id',
        'message',
        'order_number',
        'product_name',
        'quantity',
        'status',
        'action_by',
    ];

    public function order()
    {
        return $this->belongsTo(InternalOrder::class, 'internal_order_id');
    }

    public function item()
    {
        return $this->belongsTo(InternalOrderItem::class, 'internal_order_item_id');
    }

    public function actionByUser()
    {
        return $this->belongsTo(User::class, 'action_by');
    }

}
