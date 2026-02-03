<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
class Inventory extends Model
{
    protected $fillable = [
        'product_id',
        'warehouse_location_id',
        'quantity',
        'reserved_quantity',
        'total_quantity',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouseLocation()
    {
        return $this->belongsTo(WarehousePosition::class, 'warehouse_location_id');
    }

    public function latestMovement()
    {
        return $this->hasOne(StockMovement::class, 'product_id', 'product_id')
            ->latestOfMany();
    }

    public function movements()
    {
        // Fetch all movements for this product in this warehouse
        return $this->hasMany(StockMovement::class, 'product_id', 'product_id')
            ->whereColumn('warehouse_location_id', 'inventory.warehouse_location_id');
    }
}

