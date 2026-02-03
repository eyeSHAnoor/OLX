<?php

namespace App\Models;

use App\Traits\Fileable;
use App\Traits\QueryFilter;
use App\Traits\RelatedRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehousePosition extends Model
{
    protected $fillable = [
        'code',
        'zone',
        'type',
        'capacity',
    ];

    protected $casts = [
        'capacity' => 'integer',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'warehouse_location_id');
    }
}
