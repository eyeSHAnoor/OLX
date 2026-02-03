<?php

namespace App\Models;

use App\Traits\Fileable;
use App\Traits\QueryFilter;
use App\Traits\RelatedRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use Fileable, QueryFilter, RelatedRecord;


    protected $fillable = [
        'merchant_id',
        'code',
        'name',
        'address',
        'city',
        'country',
        'contact_person',
        'contact_phone',
    ];

    public function positions(): HasMany
    {
        return $this->hasMany(WarehousePosition::class);
    }
}
