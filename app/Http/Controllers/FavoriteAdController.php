<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FavoriteAdController extends Controller
{
    /**
     * Display the user's favorite ads.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // --------------------------
        // FILTERS
        // --------------------------
        $searchTerm = $request->input('filter.global');
        $categoryId = $request->input('filter.category');
        $brand = $request->input('filter.brand');
        $minPrice = $request->input('filter.min_price');
        $maxPrice = $request->input('filter.max_price');
        $sort = $request->input('sort', 'newest');

        // --------------------------
        // BASE QUERY
        // --------------------------
        $baseQuery = $user->favoriteAds()
            ->with(['images', 'category.parent', 'brand', 'user']);

        // --------------------------
        // SEARCH
        // --------------------------
        if ($searchTerm) {
            $search = strtolower($searchTerm);
            $baseQuery->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(ad_title) LIKE ?', ["%{$search}%"])
                ->orWhereRaw('LOWER(description) LIKE ?', ["%{$search}%"])
                ->orWhereRaw('LOWER(location) LIKE ?', ["%{$search}%"])
                ->orWhereHas('brand', fn($b) =>
                        $b->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                );
            });
        }

        // --------------------------
        // CATEGORY FILTER (INCLUDES CHILDREN)
        // --------------------------
        $categoryIds = [];
        $selectedCategory = null;

        if ($categoryId) {
            $selectedCategory = Category::with(['childrenRecursive'])->find($categoryId);

            if ($selectedCategory) {
                // Get all leaf categories + selected category
                $categoryIds = $selectedCategory
                    ->getLeafCategoriesEfficient()
                    ->pluck('id')
                    ->push($selectedCategory->id)
                    ->unique()
                    ->toArray();

                $baseQuery->whereIn('category_id', $categoryIds);
            }
        }

        // --------------------------
        // OTHER FILTERS
        // --------------------------
        $baseQuery
            ->when($brand, fn($q) => $q->where('brand_id', $brand))
            ->when($minPrice, fn($q) => $q->where('price', '>=', $minPrice))
            ->when($maxPrice, fn($q) => $q->where('price', '<=', $maxPrice));

        // --------------------------
        // SORTING
        // --------------------------
        match ($sort) {
            'price_low' => $baseQuery->orderBy('price', 'asc'),
            'price_high' => $baseQuery->orderBy('price', 'desc'),
            default => $baseQuery->latest(),
        };

        // --------------------------
        // PAGINATION
        // --------------------------
        $favoriteAds = $baseQuery->paginate(12)->withQueryString();

        // --------------------------
        // CATEGORIES (only parents)
        // --------------------------
        $categories = Category::whereNull('parent_id')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        // --------------------------
        // BRANDS (ONLY FOR SELECTED CATEGORY + CHILDREN)
        // --------------------------
        $brands = collect();
        if ($selectedCategory && count($categoryIds) > 0) {
            $brands = Brand::whereHas('categories', function ($q) use ($categoryIds) {
                $q->whereIn('categories.id', $categoryIds);
            })
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
        }

        // --------------------------
        // PRICE RANGE
        // --------------------------
        $priceRange = [
            'min' => (clone $baseQuery)->min('price'),
            'max' => (clone $baseQuery)->max('price'),
        ];

        return Inertia::render('home/FavoriteAds', [
            'favoriteAds' => $favoriteAds,
            'categories' => $categories,
            'brands' => $brands,

            'filters' => [
                'filter' => [
                    'global' => $searchTerm,
                    'category' => $categoryId,
                    'brand' => $brand,
                    'min_price' => $minPrice,
                    'max_price' => $maxPrice,
                ],
                'sort' => $sort,
            ],

            'priceRange' => $priceRange,
        ]);
    }

    /**
     * Toggle favorite status for an ad.
     */
    public function toggle(Ad $ad)
    {
        $user = auth()->user();
        
        if ($user->favoriteAds()->where('ad_id', $ad->id)->exists()) {
            $user->favoriteAds()->detach($ad->id);
            $message = 'Ad removed from favorites';
            $isFavorited = false;
        } else {
            $user->favoriteAds()->attach($ad->id);
            $message = 'Ad added to favorites';
            $isFavorited = true;
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'is_favorited' => $isFavorited
        ]);
    }

    /**
     * API endpoint for filtered favorite ads (if needed for AJAX).
     */
    public function apiIndex(Request $request)
    {
        $user = auth()->user();
        
        $query = $user->favoriteAds()
            ->with(['images', 'category', 'brand', 'user']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ad_title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('brand')) {
            $query->where('brand_id', $request->brand);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $favoriteAds = $query->latest()->paginate(12)->withQueryString();

        return response()->json($favoriteAds);
    }

    /**
     * Remove a specific ad from favorites.
     */
    public function destroy(Ad $ad)
    {
        $user = auth()->user();
        
        $user->favoriteAds()->detach($ad->id);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Ad removed from favorites'
            ]);
        }

        return redirect()->back()->with('success', 'Ad removed from favorites');
    }

    /**
     * Clear all favorite ads.
     */
    public function clearAll()
    {
        $user = auth()->user();
        
        $user->favoriteAds()->detach();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'All favorites cleared'
            ]);
        }

        return redirect()->back()->with('success', 'All favorites cleared');
    }
}