<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TecdocArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'article_id',
        'article_no',
        'supplier_name',
        'article_product_name'
    ];

    protected $casts = [
        'article_id' => 'integer',
    ];

    /**
     * Get the cross-references for this article
     */
    public function crossReferences(): HasMany
    {
        return $this->hasMany(ProductCrossReference::class, 'tecdoc_article_id');
    }

    /**
     * Get the products that reference this article
     */
    public function products()
    {
        return $this->belongsToMany(
            Product::class,
            'product_cross_references',
            'tecdoc_article_id',
            'product_id'
        )->withPivot('reference_type', 'priority');
    }
}