<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Brand;
use App\Models\Ad;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function allItems(Request $request)
    {
        $selectedCity = strtolower(session('city', 'Pakistan'));

        // Search / filter inputs
        $searchTerm = trim($request->input('filter.global', ''));
        $categoryFilter = $request->input('filter.category', null);
        $brandFilter = $request->input('filter.brand', null);
        $minPrice = $request->input('min_price', null);
        $maxPrice = $request->input('max_price', null);
        $sortBy = $request->input('sort_by', 'newest');

        // Categories for sidebar
        $categories = Category::whereNull('parent_id')
            ->with(['childrenRecursive', 'files'])
            ->orderBy('position')
            ->get();

        // Main ads query
        $adQuery = Ad::with(['images', 'brand', 'category'])
            ->when($selectedCity !== 'pakistan', fn($q) => $q->whereRaw('LOWER(city) = ?', [$selectedCity]));

        // Apply global search
        if (!empty($searchTerm)) {
            $searchTermLower = strtolower($searchTerm);
            $connection = config('database.default');
            $isMySQL = ($connection === 'mysql' || $connection === 'mariadb');

            $adQuery->where(function ($q) use ($searchTermLower, $isMySQL) {
                // Search keywords
                if ($isMySQL) {
                    $q->orWhereRaw("JSON_SEARCH(LOWER(JSON_UNQUOTE(search_keywords)), 'one', ?) IS NOT NULL", [$searchTermLower]);
                } else {
                    $q->orWhereRaw("LOWER(search_keywords) LIKE ?", ["%\"{$searchTermLower}\"%"]);
                }

                // Search ad_title
                $q->orWhereRaw('LOWER(ad_title) LIKE ?', ["%{$searchTermLower}%"]);
                // Search description
                $q->orWhereRaw('LOWER(description) LIKE ?', ["%{$searchTermLower}%"]);

                // Search in brand
                $q->orWhereHas('brand', fn($brandQ) => $brandQ->whereRaw('LOWER(name) LIKE ?', ["%{$searchTermLower}%"]));
                // Search in category
                $q->orWhereHas('category', fn($catQ) => $catQ->whereRaw('LOWER(name) LIKE ?', ["%{$searchTermLower}%"]));

                // Add search variations
                $this->addSearchVariations($q, $searchTermLower, $isMySQL);
            });
        }

        // Category filter
        if (!empty($categoryFilter) && $category = Category::find($categoryFilter)) {
            $categoryIds = $category->children()->exists()
                ? $category->getLeafCategoriesEfficient()->pluck('id')->toArray()
                : [];
            if (!empty($categoryIds)) {
                $categoryIds[] = $category->id;
                $adQuery->whereIn('category_id', $categoryIds);
            } else {
                $adQuery->where('category_id', $category->id);
            }
        }

        // Brand filter
        if (!empty($brandFilter)) {
            $adQuery->where('brand_id', $brandFilter);
        }

        // Price filter
        if (!empty($minPrice)) $adQuery->where('price', '>=', $minPrice);
        if (!empty($maxPrice)) $adQuery->where('price', '<=', $maxPrice);

        // Sorting
        $connection = config('database.default');
        $isMySQL = ($connection === 'mysql' || $connection === 'mariadb');

        if (!empty($searchTerm) && $sortBy === 'relevance') {
            // Relevance sorting for search
            if ($isMySQL) {
                $adQuery->orderByRaw("
                    CASE 
                        WHEN JSON_SEARCH(LOWER(JSON_UNQUOTE(search_keywords)), 'one', ?) IS NOT NULL THEN 1
                        WHEN LOWER(ad_title) LIKE ? THEN 2
                        WHEN LOWER(description) LIKE ? THEN 3
                        ELSE 4
                    END,
                    created_at DESC
                ", [$searchTermLower, "%{$searchTermLower}%", "%{$searchTermLower}%"]);
            } else {
                $adQuery->orderByRaw("
                    CASE 
                        WHEN LOWER(search_keywords) LIKE ? THEN 1
                        WHEN LOWER(ad_title) LIKE ? THEN 2
                        WHEN LOWER(description) LIKE ? THEN 3
                        ELSE 4
                    END,
                    created_at DESC
                ", ["%\"{$searchTermLower}\"%", "%{$searchTermLower}%", "%{$searchTermLower}%"]);
            }
        } else {
            // User-selected sorting
            switch ($sortBy) {
                case 'price_low':
                    $adQuery->orderBy('price', 'asc');
                    break;
                case 'price_high':
                    $adQuery->orderBy('price', 'desc');
                    break;
                case 'newest':
                default:
                    $adQuery->orderBy('created_at', 'desc');
                    break;
            }
        }

        // Pagination
        $ads = $adQuery->paginate(24)->withQueryString();

        // Brands for filters
        $brands = Brand::with(['categories.files'])->get();

        return Inertia::render('home/AllItems', [
            'ads' => $ads,
            'categories' => $categories,
            'brands' => $brands,
            'filters' => [
                'filter' => [
                    'global' => $searchTerm,
                    'category' => $categoryFilter,
                    'brand' => $brandFilter,
                ],
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'sort_by' => $sortBy,
            ],
            'totalAds' => $ads->total(),
        ]);
    }

    /**
     * Add search variations for common words (case-insensitive)
     */
    private function addSearchVariations($query, $searchTermLower, $isMySQL = true)
    {
        $variations = [];
        
        // Handle plural/singular (simple English)
        if (str_ends_with($searchTermLower, 's') && strlen($searchTermLower) > 3) {
            $variations[] = rtrim($searchTermLower, 's');
        } else {
            $variations[] = $searchTermLower . 's';
        }
        
        // Handle common word mappings (case-insensitive)
        $mappings = [
            'mobile' => ['phone', 'cellphone', 'smartphone'],
            'phone' => ['mobile', 'cellphone', 'smartphone'],
            'laptop' => ['notebook', 'computer'],
            'notebook' => ['laptop', 'computer'],
            'tv' => ['television'],
            'television' => ['tv'],
            'bike' => ['bicycle', 'cycle'],
            'bicycle' => ['bike', 'cycle'],
            'cycle' => ['bike', 'bicycle'],
            'fridge' => ['refrigerator'],
            'refrigerator' => ['fridge'],
            'ac' => ['air conditioner', 'airconditioner'],
            'car' => ['automobile', 'vehicle'],
            'automobile' => ['car', 'vehicle'],
            'vehicle' => ['car', 'automobile'],
            'house' => ['home', 'property'],
            'home' => ['house', 'property'],
            'property' => ['house', 'home'],
        ];
        
        // Check if any word in search term has mappings
        $words = explode(' ', $searchTermLower);
        foreach ($words as $word) {
            $word = trim($word);
            if (isset($mappings[$word])) {
                $variations = array_merge($variations, $mappings[$word]);
            }
        }
        
        // Also check the full search term
        if (isset($mappings[$searchTermLower])) {
            $variations = array_merge($variations, $mappings[$searchTermLower]);
        }
        
        // Remove duplicates
        $variations = array_unique($variations);
        
        // Add variations to query
        foreach ($variations as $variation) {
            if ($variation !== $searchTermLower) {
                // Search variations in search_keywords
                if ($isMySQL) {
                    $query->orWhere(function ($varQ) use ($variation) {
                        $varQ->whereRaw(
                            "JSON_SEARCH(LOWER(JSON_UNQUOTE(search_keywords)), 'one', ?) IS NOT NULL",
                            [$variation]
                        );
                    });
                } else {
                    $query->orWhere(function ($varQ) use ($variation) {
                        $varQ->whereRaw("LOWER(search_keywords) LIKE ?", ["%\"{$variation}\"%"]);
                    });
                }
                
                // Also search in other fields
                $query->orWhereRaw('LOWER(ad_title) LIKE ?', ["%{$variation}%"]);
                $query->orWhereRaw('LOWER(description) LIKE ?', ["%{$variation}%"]);
            }
        }
    }
}