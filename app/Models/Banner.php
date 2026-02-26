<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image_url',
        'link',
        'position',
        'target_category_id',
        'sort_order',
        'start_date',
        'end_date',
        'status',
    ];

    /**
     * Scope to get only active banners based on status and schedule
     */
    public function scopeActive($query)
    {
        $now = Carbon::now();
        return $query->where('status', true)
                     ->where(function ($q) use ($now) {
                         $q->whereNull('start_date')
                           ->orWhere('start_date', '<=', $now);
                     })
                     ->where(function ($q) use ($now) {
                         $q->whereNull('end_date')
                           ->orWhere('end_date', '>=', $now);
                     });
    }

    /**
     * Optional relation if targeting a category
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'target_category_id');
    }
}
