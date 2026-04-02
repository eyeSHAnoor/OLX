<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'country', 'lat', 'lng'];

    // Relationship: a city has many regions
    public function regions()
    {
        return $this->hasMany(Region::class);
    }
}