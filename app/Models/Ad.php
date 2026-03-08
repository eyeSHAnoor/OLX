<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'brand_id',
        'ad_title',
        'description',
        'price',
        'city',
        'location',
        'seller_name',
        'seller_phone',
        'search_keywords'
    ];

    protected $casts = [
        'search_keywords' => 'array',
    ];

    protected $appends = ['is_favorited'];

    public function getIsFavoritedAttribute(): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false; // not logged in
        }

        return $this->favoritedBy()->where('user_id', $user->id)->exists();
    }

    protected static function booted()
    {
        static::saving(function ($ad) {

            // --------------------------
            // Auto-generate keywords
            // --------------------------
            $text = implode(' ', [
                $ad->ad_title,
                optional($ad->category)->name,
                optional($ad->brand)->name,
                $ad->city,
                $ad->location,
            ]);

            $text = strtolower(preg_replace('/[^a-z0-9\s]/i', '', $text));
            $autoKeywords = collect(preg_split('/\s+/', $text))
                ->filter(fn($w) => strlen($w) > 2)
                ->values()
                ->toArray();

            // --------------------------
            // Merge with frontend keywords
            // --------------------------
            $frontendKeywords = is_array($ad->search_keywords) ? $ad->search_keywords : [];
            $merged = collect(array_merge($autoKeywords, $frontendKeywords))
                ->map(fn($w) => strtolower($w))  // lowercase
                ->filter(fn($w) => strlen($w) > 2) // optional extra filter
                ->unique()
                ->values()
                ->toArray();

            $ad->search_keywords = $merged;
        });
    }

    // --------------------------
    // Relationships
    // --------------------------
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
    public function features()
    {
        return $this->belongsToMany(Feature::class, 'ad_feature')
            ->withPivot(['feature_value_id', 'custom_value'])
            ->withTimestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'ad_favorites')
            ->withTimestamps();
    }

    public function reports(): HasMany
    {
        return $this->hasMany(UserReport::class);
    }
}   
