<?php

// app/Models/Product.php

namespace App\Models;


use App\Traits\Fileable;
use App\Traits\QueryFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use Fileable, HasFactory, QueryFilter;

    protected $fillable = [
        'sku',
        'name',
        'category_id',
        'brand',
        'warehouse_position_id',
        'description',
        'quantity',
        'unit',
        'ean',
        'tax',
        'barcode',
        'low_stock',
        'max_discount',
        'category',
        'status',
        'price',
        'last_low_stock_notified_at',
        'article_details',
    ];

    protected $casts = [
        'article_details' => 'array',
    ];

    // Relationships
    public function categoryData()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }


    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function crossReferences()
    {
        return $this->hasMany(ProductCrossReference::class);
    }

    public function internalOrderItems()
    {
        return $this->hasMany(InternalOrderItem::class);
    }

    public function tecdocArticles()
    {
        return $this->belongsToMany(
            TecdocArticle::class,
            'product_cross_references'
        )->withPivot('reference_type', 'priority');
    }

    // Add OEM relationship
    public function oemReferences()
    {
        return $this->belongsToMany(
            OemReference::class,
            'product_oem_reference'
        )->withPivot('reference_type', 'priority')
            ->withTimestamps();
    }

     public function outboundItems(): HasMany
    {
        return $this->hasMany(OutboundOrderItem::class, 'product_id');
    }

    /**
     * Sync OEM references for this product
     */
    public function syncOemReferences(array $oemData, string $referenceType = 'tecdoc')
    {
        $oemIds = [];

        foreach ($oemData as $oem) {
            if (! empty($oem['oemBrand']) && ! empty($oem['oemDisplayNo'])) {
                // Clean the data
                $oemBrand = trim($oem['oemBrand']);
                $oemDisplayNo = trim($oem['oemDisplayNo']);

                // Skip if brand is UNKNOWN and we want to filter it out
                if (strtoupper($oemBrand) === 'UNKNOWN') {
                    continue;
                }

                $oemReference = OemReference::firstOrCreate([
                    'oem_brand' => $oemBrand,
                    'oem_display_no' => $oemDisplayNo,
                ], [
                    'oem_article_no' => $oem['oemArticleNo'] ?? null,
                ]);

                $oemIds[$oemReference->id] = [
                    'reference_type' => $referenceType,
                    'priority' => $oem['priority'] ?? 0,
                ];
            }
        }

        $this->oemReferences()->sync($oemIds);

        return $this;
    }

    /**
     * Delete a product safely.
     */
    public function deleteProduct(): bool
    {
        // You can add inventory checks here later
        return $this->delete();
    }
}
