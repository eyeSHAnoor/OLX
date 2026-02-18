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
        $searchTerm = $request->input('filter.global', null);
        $categoryFilter = $request->input('filter.category', null);
        $brandFilter = $request->input('filter.brand', null);
        $minPrice = $request->input('min_price', null);
        $maxPrice = $request->input('max_price', null);
        $sortBy = $request->input('sort_by', 'newest');

        // Get all categories with their children for sidebar
        $categories = Category::whereNull('parent_id')
            ->with(['childrenRecursive', 'files'])
            ->orderBy('position')
            ->get();

        // Main ads query
        $adQuery = Ad::with(['images', 'brand', 'category'])
            ->when($selectedCity !== 'pakistan', fn($q) => $q->whereRaw('LOWER(city) = ?', [$selectedCity]));

        // Apply global search with intelligent word handling
        if (!empty($searchTerm)) {
            $searchTerm = trim($searchTerm);
            $searchTermLower = strtolower($searchTerm);
            
            $adQuery->where(function ($q) use ($searchTerm, $searchTermLower) {
                // Get database connection type
                $connection = config('database.default');
                $isMySQL = ($connection === 'mysql' || $connection === 'mariadb');
                
                // 1. FIRST PRIORITY: Search in search_keywords (JSON array)
                if ($isMySQL) {
                    // MySQL/MariaDB version using JSON functions
                    $q->orWhere(function ($keywordQ) use ($searchTermLower) {
                        // Search for exact match in search_keywords
                        $keywordQ->where(function ($exactQ) use ($searchTermLower) {
                            $exactQ->whereRaw(
                                "JSON_SEARCH(LOWER(JSON_UNQUOTE(search_keywords)), 'one', ?) IS NOT NULL",
                                [$searchTermLower]
                            );
                        });
                        
                        // Also search for individual words if the search term has multiple words
                        if (str_contains($searchTermLower, ' ')) {
                            $words = explode(' ', $searchTermLower);
                            foreach ($words as $word) {
                                $word = trim($word);
                                if (!empty($word) && strlen($word) >= 2) {
                                    $keywordQ->orWhere(function ($wordQ) use ($word) {
                                        $wordQ->whereRaw(
                                            "JSON_SEARCH(LOWER(JSON_UNQUOTE(search_keywords)), 'one', ?) IS NOT NULL",
                                            [$word]
                                        );
                                    });
                                }
                            }
                        }
                    });
                } else {
                    // SQLite version using LIKE (since SQLite doesn't have JSON functions)
                    $q->orWhere(function ($keywordQ) use ($searchTermLower) {
                        $keywordQ->whereRaw("LOWER(search_keywords) LIKE ?", ["%\"{$searchTermLower}\"%"]);
                        
                        // Also search for individual words if the search term has multiple words
                        if (str_contains($searchTermLower, ' ')) {
                            $words = explode(' ', $searchTermLower);
                            foreach ($words as $word) {
                                $word = trim($word);
                                if (!empty($word) && strlen($word) >= 2) {
                                    $keywordQ->orWhereRaw("LOWER(search_keywords) LIKE ?", ["%\"{$word}\"%"]);
                                }
                            }
                        }
                    });
                }

                // 2. SECOND PRIORITY: Search in ad_title (case-insensitive)
                $q->orWhereRaw('LOWER(ad_title) LIKE ?', ["%{$searchTermLower}%"]);
                
                // If search term has spaces, also search for individual words in ad_title
                if (str_contains($searchTermLower, ' ')) {
                    $words = explode(' ', $searchTermLower);
                    foreach ($words as $word) {
                        $word = trim($word);
                        if (!empty($word) && strlen($word) >= 2) {
                            $q->orWhereRaw('LOWER(ad_title) LIKE ?', ["%{$word}%"]);
                        }
                    }
                }
                
                // 3. THIRD PRIORITY: Search in description (case-insensitive)
                $q->orWhereRaw('LOWER(description) LIKE ?', ["%{$searchTermLower}%"]);
                
                // Individual words in description
                if (str_contains($searchTermLower, ' ')) {
                    $words = explode(' ', $searchTermLower);
                    foreach ($words as $word) {
                        $word = trim($word);
                        if (!empty($word) && strlen($word) >= 2) {
                            $q->orWhereRaw('LOWER(description) LIKE ?', ["%{$word}%"]);
                        }
                    }
                }
                
                // 4. Search in brands (case-insensitive)
                $q->orWhereHas('brand', function ($brandQ) use ($searchTermLower) {
                    $brandQ->whereRaw('LOWER(name) LIKE ?', ["%{$searchTermLower}%"]);
                    
                    // Individual brand words
                    if (str_contains($searchTermLower, ' ')) {
                        $words = explode(' ', $searchTermLower);
                        foreach ($words as $word) {
                            $word = trim($word);
                            if (!empty($word) && strlen($word) >= 2) {
                                $brandQ->orWhereRaw('LOWER(name) LIKE ?', ["%{$word}%"]);
                            }
                        }
                    }
                });
                
                // 5. Search in categories (case-insensitive)
                $q->orWhereHas('category', function ($catQ) use ($searchTermLower) {
                    $catQ->whereRaw('LOWER(name) LIKE ?', ["%{$searchTermLower}%"]);
                    
                    // Individual category words
                    if (str_contains($searchTermLower, ' ')) {
                        $words = explode(' ', $searchTermLower);
                        foreach ($words as $word) {
                            $word = trim($word);
                            if (!empty($word) && strlen($word) >= 2) {
                                $catQ->orWhereRaw('LOWER(name) LIKE ?', ["%{$word}%"]);
                            }
                        }
                    }
                });
                
                // 6. Handle common variations and synonyms
                $this->addSearchVariations($q, $searchTermLower, $isMySQL);
            });
        }

        // Category filter (optional)
        if (!empty($categoryFilter)) {
            $selectedCategory = Category::find($categoryFilter);
            if ($selectedCategory) {
                if ($selectedCategory->children()->exists()) {
                    $categoryIds = $selectedCategory->getLeafCategoriesEfficient()->pluck('id')->toArray();
                    $categoryIds[] = $selectedCategory->id;
                    $adQuery->whereIn('category_id', $categoryIds);
                } else {
                    $adQuery->where('category_id', $categoryFilter);
                }
            }
        }

        // Brand filter
        if (!empty($brandFilter)) {
            $adQuery->where('brand_id', $brandFilter);
        }

        // Price filters
        if (!empty($minPrice)) {
            $adQuery->where('price', '>=', $minPrice);
        }
        if (!empty($maxPrice)) {
            $adQuery->where('price', '<=', $maxPrice);
        }

        // Get database connection type for sorting
        $connection = config('database.default');
        $isMySQL = ($connection === 'mysql' || $connection === 'mariadb');

        // Sorting - Add relevance sorting for search results
        if (!empty($searchTerm)) {
            $searchTermLower = strtolower(trim($searchTerm));
            
            if ($isMySQL) {
                // MySQL/MariaDB version with JSON functions
                $adQuery->orderByRaw("
                    CASE 
                        WHEN JSON_SEARCH(LOWER(JSON_UNQUOTE(search_keywords)), 'one', ?) IS NOT NULL THEN 1
                        WHEN LOWER(ad_title) LIKE ? THEN 2
                        WHEN LOWER(description) LIKE ? THEN 3
                        ELSE 4
                    END,
                    created_at DESC
                ", [
                    $searchTermLower,
                    "%{$searchTermLower}%",
                    "%{$searchTermLower}%"
                ]);
            } else {
                // SQLite version without JSON functions
                $adQuery->orderByRaw("
                    CASE 
                        WHEN LOWER(search_keywords) LIKE ? THEN 1
                        WHEN LOWER(ad_title) LIKE ? THEN 2
                        WHEN LOWER(description) LIKE ? THEN 3
                        ELSE 4
                    END,
                    created_at DESC
                ", [
                    "%\"{$searchTermLower}\"%",
                    "%{$searchTermLower}%",
                    "%{$searchTermLower}%"
                ]);
            }
        } else {
            // Default sorting when no search
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

        // Get all brands for filters
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