<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdFeature extends Model
{
    protected $fillable = [
        'ad_id',
        'field',
        'value',
    ];

    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }
}

