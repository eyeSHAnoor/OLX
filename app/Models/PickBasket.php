<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PickBasket extends Model
{
    protected $fillable = [
        'id',
        'basket_number',
        'status', // draft, moved_to_outbound
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PickBasketItem::class);
    }
}
