<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'position'
    ];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function childrenRecursive()
    {
        return $this->children()->with(['childrenRecursive', 'files']);
    }



    public function brands()
    {
        return $this->belongsToMany(Brand::class);
    }

    public function files()
    {
        return $this->morphMany(File::class, 'fileable');
    }


}
