<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdAttributeValue extends Model
{
    use HasFactory;

    protected $fillable = ['ad_id','category_attribute_id','value'];

    public function ad()
    {
        return $this->belongsTo(Ad::class, 'ad_id');
    }

    public function attribute()
    {
        return $this->belongsTo(CategoryAttribute::class, 'category_attribute_id');
    }
}