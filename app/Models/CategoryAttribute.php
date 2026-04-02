<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'attribute_group_id',
        'name',
        'type',
        'is_required',
        'is_filterable',
        'position'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function group()
    {
        return $this->belongsTo(AttributeGroup::class, 'attribute_group_id');
    }

    public function options()
    {
        return $this->hasMany(AttributeOption::class, 'category_attribute_id');
    }

    public function adValues()
    {
        return $this->hasMany(AdAttributeValue::class, 'category_attribute_id');
    }
}