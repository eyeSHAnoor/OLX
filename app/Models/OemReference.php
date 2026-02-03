<?php
// app/Models/OemReference.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OemReference extends Model
{
    use HasFactory;

    protected $fillable = [
        'oem_brand',
        'oem_display_no',
        'oem_article_no',
    ];

    // Relationships
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_oem_reference')
            ->withPivot('reference_type', 'priority')
            ->withTimestamps();
    }
}