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
        return $this->children()->with(['childrenRecursive', 'files', 'brands']);
    }

    public function ads()
    {
        return $this->hasMany(Ad::class);
    }

    public function brands()
    {
        return $this->belongsToMany(Brand::class);
    }

    // Category-specific attributes/specifications
    public function attributes()
    {
        return $this->hasMany(CategoryAttribute::class);
    }


    public function files()
    {
        return $this->morphMany(File::class, 'fileable');
    }

    /**
     * Check if the category is a leaf (no children)
     */
    public function isLeaf(): bool
    {
        return $this->children()->count() === 0;
    }

    public function allChildrenIds()
    {
        return $this->descendantsAndSelf()->pluck('id');
    }

    /**
     * Recursively get all leaf categories
     */
     /**
     * Get all leaf categories under this category (efficient version)
     */
    public function getLeafCategoriesEfficient()
    {
        // Load all descendants recursively in one query
        $allDescendants = $this->childrenRecursive()->get()->flatMap(function ($child) {
            return $child->descendantsAndSelf();
        });

        // Include self in the collection
        $all = collect([$this])->merge($allDescendants);

        // Filter categories that have no children
        return $all->filter(fn($category) => $category->children->isEmpty());
    }

    /**
     * Helper: Get self and all descendants recursively
     */
    public function descendantsAndSelf()
    {
        $descendants = collect([$this]);
        foreach ($this->children as $child) {
            $descendants = $descendants->merge($child->descendantsAndSelf());
        }
        return $descendants;
    }
}
