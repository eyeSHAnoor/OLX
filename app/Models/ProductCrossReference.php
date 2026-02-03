<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductCrossReference extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'tecdoc_article_id',
        'reference_type',
        'priority'
    ];

    protected $casts = [
        'priority' => 'integer',
    ];

    /**
     * Get the product for this cross-reference
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the TecDoc article for this cross-reference
     */
    public function tecdocArticle(): BelongsTo
    {
        return $this->belongsTo(TecdocArticle::class, 'tecdoc_article_id');
    }
}