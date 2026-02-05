<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'brand_id',
        'ad_title',
        'description',
        'price',
        'location',
        'seller_name',
        'seller_phone',
    ];

    public function images()
    {
        return $this->hasMany(AdImage::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
}
