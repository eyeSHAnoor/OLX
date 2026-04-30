<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Brand;
use App\Models\Ad;
use App\Models\Banner;
use App\Models\BrandModel;
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function allItems(Request $request)
    {
        // $selectedCity = strtolower(session('city', 'Pakistan'));
        $selectedCity = strtolower($request->cookie('user_city', 'Pakistan'));
        $selectedCity = $selectedCity === 'pakistan' ? 'all' : $selectedCity;
        $selectedRegion = $request->cookie('user_region') 
                      ?? session('region');

        // Search / filter inputs
        $searchTerm = trim($request->input('filter.global', ''));
        $categoryFilter = $request->input('filter.category', null);
        $brandFilter = $request->input('filter.brand', null);
        $modelFilter = $request->input('filter.model', null);
        $minPrice = $request->input('min_price', null);
        $maxPrice = $request->input('max_price', null);
        $sortBy = $request->input('sort_by', 'newest');

        // Attribute filters
        $attributeFilters = collect($request->input('filter', []))
            ->filter(fn ($value, $key) => str_starts_with($key, 'attribute_'))
            ->map(function ($value) {
                if (is_string($value) && str_contains($value, ',')) {
                    return explode(',', $value);
                }
                return $value;
            })
            ->toArray();

        // Detect database driver
        $connection = config('database.default');
        $isMySQL = ($connection === 'mysql' || $connection === 'mariadb');
        $isSQLite = ($connection === 'sqlite');

        // Categories for sidebar
        $categories = Category::whereNull('parent_id')
            ->with(['childrenRecursive', 'files'])
            ->orderBy('position')
            ->get();

        // Main ads query
        $adQuery = Ad::with(['images', 'brand', 'category', 'model', 'attributes.attribute'])
            ->where('status', 'active') 
            ->when($selectedCity !== 'pakistan', fn($q) => $q->whereRaw('LOWER(city) = ?', [$selectedCity]));

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
            $adQuery->whereIn('brand_id', explode(',', $brandFilter));
        }

        // Model filter
        if (!empty($modelFilter)) {
            $adQuery->whereIn('brand_model_id', explode(',', $modelFilter));
        }

        // Price filter
        if (!empty($minPrice)) $adQuery->where('price', '>=', $minPrice);
        if (!empty($maxPrice)) $adQuery->where('price', '<=', $maxPrice);

        // Attribute filters
        if (!empty($attributeFilters)) {
            foreach ($attributeFilters as $key => $value) {
                if (!$value) continue;

                $attrId = str_replace('attribute_', '', $key);

                $adQuery->whereHas('attributes', function ($subQ) use ($attrId, $value) {
                    $subQ->where('category_attribute_id', $attrId)
                        ->whereIn('value', (array) $value);
                });
            }
        }

        // Apply global search with word splitting and scoring
        if (!empty($searchTerm)) {
            // Split search term into individual words
            $searchWords = $this->splitIntoWords($searchTerm);
            
            // Expand search words with variations
            $allSearchWords = $this->expandSearchWords($searchWords);
            
            // Build the search query with word matching (database agnostic)
            $adQuery->where(function ($q) use ($allSearchWords, $isMySQL, $isSQLite) {
                foreach ($allSearchWords as $word) {
                    if (strlen($word) < 2) continue;
                    
                    $q->orWhere(function ($subQ) use ($word, $isMySQL, $isSQLite) {
                        // Search in title
                        $subQ->orWhereRaw('LOWER(ad_title) LIKE ?', ["%{$word}%"]);
                        
                        // Search in description
                        $subQ->orWhereRaw('LOWER(description) LIKE ?', ["%{$word}%"]);
                        
                        // Search in brand
                        $subQ->orWhereHas('brand', fn($brandQ) => $brandQ->whereRaw('LOWER(name) LIKE ?', ["%{$word}%"]));
                        
                        // Search in model
                        $subQ->orWhereHas('model', fn($modelQ) => $modelQ->whereRaw('LOWER(name) LIKE ?', ["%{$word}%"]));
                        
                        // Search in attributes
                        $subQ->orWhereHas('attributes', fn($attrQ) => $attrQ->whereRaw('LOWER(value) LIKE ?', ["%{$word}%"]));
                        
                        // Search in search_keywords JSON - handle differently for MySQL vs SQLite
                        if ($isMySQL) {
                            $subQ->orWhereRaw("JSON_SEARCH(LOWER(JSON_UNQUOTE(search_keywords)), 'one', ?) IS NOT NULL", [$word]);
                        } elseif ($isSQLite) {
                            // SQLite doesn't have JSON functions, so we need to search in the JSON string
                            $subQ->orWhereRaw("LOWER(search_keywords) LIKE ?", ["%\"{$word}\"%"]);
                        }
                    });
                }
            });
            
            // Add scoring and sort by relevance if requested
            if ($sortBy === 'relevance') {
                $this->addRelevanceScoring($adQuery, $allSearchWords, $isMySQL, $isSQLite);
                $adQuery->orderByRaw('relevance_score DESC');
            }
        }

        // Apply non-relevance sorting
        if (empty($searchTerm) || $sortBy !== 'relevance') {
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
        $brands = Brand::with(['categories.files', 'models:id,brand_id,name'])->get();

        // Get attributes for filtering
        $attributes = collect();
        if (!empty($categoryFilter) && $category = Category::with(['attributes.group', 'attributes.options', 'parent.attributes.group', 'parent.attributes.options'])->find($categoryFilter)) {
            if ($category->parent && $category->parent->attributes->isNotEmpty()) {
                $attributes = $category->parent->attributes;
            } else {
                $attributes = $category->attributes;
            }
        }

        // Calculate price range
        $priceRange = [
            'min' => (clone $adQuery)->min('price'),
            'max' => (clone $adQuery)->max('price'),
        ];

        // Determine banners for the current category (or global)
        $banners = Banner::active()
            ->where('position', 'floating')
            ->where(function ($q) use ($categoryFilter) {
                if ($categoryFilter) {
                    // Fetch banners assigned to selected category or its parent
                    $category = Category::find($categoryFilter);
                    if ($category) {
                        if ($category->parent) {
                            $q->where('target_category_id', $category->parent->id);
                        }
                        $q->orWhere('target_category_id', $category->id);
                    }
                }
                // Include banners with no category → global
                $q->orWhereNull('target_category_id');
            })
            ->orderBy('sort_order', 'asc')
            ->get();

        return Inertia::render('home/AllItems', [
            'ads' => $ads,
            'categories' => $categories,
            'brands' => $brands,
            'banners' => $banners,
            'attributes' => $attributes,
            'filters' => [
                'filter' => [
                    'global' => $searchTerm,
                    'category' => $categoryFilter,
                    'brand' => $brandFilter,
                    'model' => $modelFilter,
                ],
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'sort_by' => $sortBy,
                'attributeFilters' => $attributeFilters,
            ],
            'totalAds' => $ads->total(),
            'priceRange' => $priceRange,
        ]);
    }

    /**
     * Add relevance scoring to the query
     */
    private function addRelevanceScoring($query, $searchWords, $isMySQL, $isSQLite)
    {
        $selects = ['ads.*'];
        
        foreach ($searchWords as $word) {
            if (strlen($word) < 2) continue;
            
            // Title match (3 points)
            $selects[] = DB::raw("CASE WHEN LOWER(ad_title) LIKE '%{$word}%' THEN 3 ELSE 0 END as title_match_{$this->sanitizeWord($word)}");
            
            // Brand match (2 points)
            $selects[] = DB::raw("CASE WHEN EXISTS (SELECT 1 FROM brands WHERE brands.id = ads.brand_id AND LOWER(brands.name) LIKE '%{$word}%') THEN 2 ELSE 0 END as brand_match_{$this->sanitizeWord($word)}");
            
            // Model match (2 points)
            $selects[] = DB::raw("CASE WHEN EXISTS (SELECT 1 FROM brand_models WHERE brand_models.id = ads.brand_model_id AND LOWER(brand_models.name) LIKE '%{$word}%') THEN 2 ELSE 0 END as model_match_{$this->sanitizeWord($word)}");
            
            // Attributes match (1 point)
            $selects[] = DB::raw("CASE WHEN EXISTS (SELECT 1 FROM ad_attribute_values WHERE ad_attribute_values.ad_id = ads.id AND LOWER(ad_attribute_values.value) LIKE '%{$word}%') THEN 1 ELSE 0 END as attr_match_{$this->sanitizeWord($word)}");
            
            // Description match (1 point)
            $selects[] = DB::raw("CASE WHEN LOWER(description) LIKE '%{$word}%' THEN 1 ELSE 0 END as desc_match_{$this->sanitizeWord($word)}");
        }
        
        // Add all score columns and sum them
        $scoreColumns = [];
        foreach ($searchWords as $word) {
            if (strlen($word) < 2) continue;
            $sanitized = $this->sanitizeWord($word);
            $scoreColumns[] = "title_match_{$sanitized}";
            $scoreColumns[] = "brand_match_{$sanitized}";
            $scoreColumns[] = "model_match_{$sanitized}";
            $scoreColumns[] = "attr_match_{$sanitized}";
            $scoreColumns[] = "desc_match_{$sanitized}";
        }
        
        if (!empty($scoreColumns)) {
            $selects[] = DB::raw("(" . implode(' + ', $scoreColumns) . ") as relevance_score");
        } else {
            $selects[] = DB::raw("0 as relevance_score");
        }
        
        $query->select($selects);
    }

    /**
     * Sanitize word for use in SQL column alias
     */
    private function sanitizeWord($word)
    {
        return preg_replace('/[^a-zA-Z0-9]/', '_', $word);
    }

    /**
     * Split text into individual words (lowercase, remove special characters)
     */
    private function splitIntoWords($text)
    {
        // Convert to lowercase
        $text = strtolower($text);
        
        // Remove special characters and keep only alphanumeric and spaces
        $text = preg_replace('/[^a-z0-9\s]/', '', $text);
        
        // Split by spaces and filter out empty words and short words
        $words = array_filter(explode(' ', $text), function($word) {
            return strlen($word) >= 2;
        });
        
        return array_unique($words);
    }

    /**
     * Expand search words with variations (plural/singular, common synonyms)
     */
    private function expandSearchWords($words)
    {
        $expanded = $words;
        
        $synonyms = [
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
        
        foreach ($words as $word) {
            // Add plural/singular variations
            if (str_ends_with($word, 's') && strlen($word) > 3) {
                $expanded[] = rtrim($word, 's');
            } else {
                $expanded[] = $word . 's';
            }
            
            // Add synonyms
            if (isset($synonyms[$word])) {
                $expanded = array_merge($expanded, $synonyms[$word]);
            }
        }
        
        return array_unique($expanded);
    }

    public function suggestions(Request $request)
    {
        try {
            $query = trim($request->input('query', ''));
            if (strlen($query) < 2) {
                return response()->json([]);
            }
            $word = strtolower($query);

            // Title suggestions
            $titleSuggestions = Ad::where('status', 'active')
                ->whereRaw("LOWER(ad_title) LIKE ?", ["%{$word}%"])
                ->limit(5)
                ->pluck('ad_title');

            // Brand suggestions
            $brandSuggestions = Brand::whereRaw("LOWER(name) LIKE ?", ["{$word}%"])
                ->limit(3)
                ->pluck('name')
                ->map(fn($name) => $name );

            // Category suggestions
            $categorySuggestions = Category::whereRaw("LOWER(name) LIKE ?", ["{$word}%"])
                ->limit(3)
                ->pluck('name')
                ->map(fn($name) => $name);

            $modelSuggestions = BrandModel::whereRaw("LOWER(name) LIKE ?", ["{$word}%"])
                ->limit(3)
                ->pluck('name')
                ->map(fn($name) => $name );
            /*
            |--------------------------------------------------------------------------
            | 4. Keyword Suggestions (works with model's array cast)
            |--------------------------------------------------------------------------
            */
            $keywordSuggestions = Ad::where('status', 'active')
                ->whereNotNull('search_keywords')
                ->limit(20)
                ->pluck('search_keywords')
                ->flatMap(function ($keywords) use ($word) {
                    // Because of the 'array' cast, $keywords is already an array.
                    // If by any chance it's still a JSON string, fall back to json_decode.
                    $arr = is_array($keywords) ? $keywords : json_decode($keywords, true);
                    if (!is_array($arr)) {
                        return [];
                    }
                    return collect($arr)->filter(fn($kw) => str_contains(strtolower($kw), $word));
                })
                ->unique()
                ->values()
                ->take(5);

            $suggestions = $titleSuggestions
                ->merge($brandSuggestions)
                ->merge($categorySuggestions)
                ->merge($modelSuggestions)
                ->merge($keywordSuggestions)
                ->filter()
                ->unique()
                ->values()
                ->take(10);

            return response()->json($suggestions);
        } catch (\Throwable $e) {
            \Log::error('Search suggestions error: ' . $e->getMessage());
            return response()->json([]);
        }
    }
}