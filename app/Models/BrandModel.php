<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrandModel extends Model
{
    use HasFactory;

    protected $fillable = ['brand_id','name'];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function ads()
    {
        return $this->hasMany(Ad::class, 'model_id');
    }
}