<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $fillable = ['city_id', 'name'];

    // Relationship: region belongs to a city
    public function city()
    {
        return $this->belongsTo(City::class);
    }
}