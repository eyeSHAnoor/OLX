<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Brand extends Model
{
    protected $fillable = [
        'name'
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function ads()
    {
        return $this->hasMany(\App\Models\Ad::class);
    }

    public function models()
    {
        return $this->hasMany(BrandModel::class);
    }
}

