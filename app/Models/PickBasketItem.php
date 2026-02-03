<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PickBasketItem extends Model
{
    protected $fillable = [
        'pick_basket_id',
        'product_id',
        'sku',
        'item_name',
        'qty',
        'warehouse_location_ids',
    ];

    protected $casts = [
        'warehouse_location_ids' => 'array',
    ];

    public function basket(): BelongsTo
    {
        return $this->belongsTo(PickBasket::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouseLocations()
    {
        $ids = $this->warehouse_location_ids ?? [];
        return WarehousePosition::whereIn('id', $ids)->get();
    }


}
