<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Brand;
use App\Models\Ad;
use App\Models\Banner;
use App\Models\BrandModel;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function allItems(Request $request)
    {
        $selectedCity = strtolower($request->cookie('user_city', 'Pakistan'));
        $selectedCity = $selectedCity === 'pakistan' ? 'all' : $selectedCity;

        $searchTerm     = trim($request->input('filter.global', ''));
        $categoryFilter = $request->input('filter.category', null);
        $brandFilter    = $request->input('filter.brand', null);
        $modelFilter    = $request->input('filter.model', null);
        $minPrice       = $request->input('min_price', null);
        $maxPrice       = $request->input('max_price', null);
        $sortBy         = $request->input('sort_by', 'newest');
        $priceType = $request->input('filter.wholesale');

        $attributeFilters = collect($request->input('filter', []))
            ->filter(fn ($v, $k) => str_starts_with($k, 'attribute_'))
            ->map(fn ($v) => is_string($v) && str_contains($v, ',') ? explode(',', $v) : $v)
            ->toArray();

        $connection = config('database.default');
        $isMySQL    = in_array($connection, ['mysql', 'mariadb']);
        $isSQLite   = ($connection === 'sqlite');

        $categories = Category::whereNull('parent_id')
            ->with(['childrenRecursive', 'files'])
            ->orderBy('position')
            ->get();

        // ── Base query ────────────────────────────────────────────────────
        $adQuery = Ad::with(['images', 'brand', 'category', 'model', 'attributes.attribute'])
            ->where('status', 'active');

        if ($selectedCity !== 'all') {
            $adQuery->whereRaw('LOWER(city) = ?', [$selectedCity]);
        }

      

        if (!empty($categoryFilter) && $category = Category::find($categoryFilter)) {
            if ($category->children()->exists()) {
                $ids   = $category->getLeafCategoriesEfficient()->pluck('id')->toArray();
                $ids[] = $category->id;
                $adQuery->whereIn('category_id', $ids);
            } else {
                $adQuery->where('category_id', $category->id);
            }
        }

        if (!empty($brandFilter))  $adQuery->whereIn('brand_id',       explode(',', $brandFilter));
        if (!empty($modelFilter))  $adQuery->whereIn('brand_model_id', explode(',', $modelFilter));
        if (!empty($minPrice))     $adQuery->where('price', '>=', $minPrice);
        if (!empty($maxPrice))     $adQuery->where('price', '<=', $maxPrice);

        foreach ($attributeFilters as $key => $value) {
            if (!$value) continue;
            $attrId = str_replace('attribute_', '', $key);
            $adQuery->whereHas('attributes', fn ($q) =>
                $q->where('category_attribute_id', $attrId)->whereIn('value', (array) $value)
            );
        }

        // ── Search ────────────────────────────────────────────────────────
        if (!empty($searchTerm)) {
            $words         = $this->splitIntoWords($searchTerm);
            $expandedWords = $this->expandSearchWords($words);

            // Inclusion filter: ad must match at least one word somewhere
            $adQuery->where(function ($q) use ($expandedWords, $isMySQL, $isSQLite) {
                foreach ($expandedWords as $word) {
                    if (strlen($word) < 2) continue;
                    $q->orWhere(function ($sub) use ($word, $isMySQL, $isSQLite) {
                        $sub->orWhereRaw('LOWER(ad_title) LIKE ?',    ["%{$word}%"])
                            ->orWhereRaw('LOWER(description) LIKE ?', ["%{$word}%"])
                            ->orWhereHas('brand',      fn ($b) => $b->whereRaw('LOWER(name) LIKE ?', ["%{$word}%"]))
                            ->orWhereHas('model',      fn ($m) => $m->whereRaw('LOWER(name) LIKE ?', ["%{$word}%"]))
                            ->orWhereHas('attributes', fn ($a) => $a->whereRaw('LOWER(value) LIKE ?', ["%{$word}%"]));

                        if ($isMySQL) {
                            $sub->orWhereRaw(
                                "JSON_SEARCH(LOWER(JSON_UNQUOTE(search_keywords)), 'one', ?) IS NOT NULL",
                                ["%{$word}%"]
                            );
                        } elseif ($isSQLite) {
                            $sub->orWhereRaw('LOWER(search_keywords) LIKE ?', ["%\"{$word}\"%"]);
                        }
                    });
                }
            });

            // Build one fully-inlined relevance expression (no alias references)
            $relevanceSql = $this->buildRelevanceExpression($searchTerm, $words, $expandedWords);

            $adQuery->selectRaw("ads.*, ({$relevanceSql}) AS relevance_score")
                    ->orderBy('is_featured', 'desc') 
                    ->orderByRaw('relevance_score DESC')
                    ->orderBy('created_at', 'desc');

        } else {
             $adQuery->orderBy('is_featured', 'desc'); 
            match ($sortBy) {
                'price_low'  => $adQuery->orderBy('price', 'asc'),
                'price_high' => $adQuery->orderBy('price', 'desc'),
                default      => $adQuery->orderBy('created_at', 'desc'),
            };
        }

        \Log::info('Price type: ' . ($priceType ?? 'null'));
        \Log::info($adQuery->toSql(), $adQuery->getBindings());

        if ($priceType === 'wholesale') {
            $adQuery->where('price_type', 'wholesale');
        } else {
            $adQuery->where('price_type', 'retail');
        }

        $ads = $adQuery->paginate(24)->withQueryString();

        // ── Supporting data ───────────────────────────────────────────────
        $brands = Brand::with(['categories.files', 'models:id,brand_id,name'])->get();

        $attributes = collect();
        if (!empty($categoryFilter) && $cat = Category::with([
            'attributes.group', 'attributes.options',
            'parent.attributes.group', 'parent.attributes.options',
        ])->find($categoryFilter)) {
            $attributes = ($cat->parent && $cat->parent->attributes->isNotEmpty())
                ? $cat->parent->attributes
                : $cat->attributes;
        }

        $priceRange = [
            'min' => Ad::where('status', 'active')->min('price'),
            'max' => Ad::where('status', 'active')->max('price'),
        ];

        $banners = Banner::active()
            ->where('position', 'floating')
            ->where(function ($q) use ($categoryFilter) {
                if ($categoryFilter && $cat = Category::find($categoryFilter)) {
                    if ($cat->parent) $q->where('target_category_id', $cat->parent->id);
                    $q->orWhere('target_category_id', $cat->id);
                }
                $q->orWhereNull('target_category_id');
            })
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('home/AllItems', [
            'ads'        => $ads,
            'categories' => $categories,
            'brands'     => $brands,
            'banners'    => $banners,
            'attributes' => $attributes,
            'filters'    => [
                'filter'           => [
                    'global'   => $searchTerm,
                    'category' => $categoryFilter,
                    'brand'    => $brandFilter,
                    'model'    => $modelFilter,
                    'wholesale'=> $priceType,
                ],
                'min_price'        => $minPrice,
                'max_price'        => $maxPrice,
                'sort_by'          => $sortBy,
                'attributeFilters' => $attributeFilters,
            ],
            'totalAds'   => $ads->total(),
            'priceRange' => $priceRange,
        ]);
    }

    private function buildRelevanceExpression(string $rawSearch, array $words, array $expandedWords): string
    {
        // Escape for safe inline SQL (no user input goes in unescaped)
        $esc  = fn(string $v): string => str_replace(["'", "\\", '%', '_'], ["''", "\\\\", '\%', '\_'], strtolower($v));

        $phrase = $esc($rawSearch);
        $parts  = [];

        // 1. Exact phrase in title → 10
        $parts[] = "(CASE WHEN LOWER(ad_title) LIKE '%{$phrase}%' THEN 10 ELSE 0 END)";

        // 2. Exact phrase in search_keywords → 8
        $parts[] = "(CASE WHEN LOWER(search_keywords) LIKE '%{$phrase}%' THEN 8 ELSE 0 END)";

        // 3. All individual words present in title → 6
        if (count($words) > 1) {
            $allConds = implode(' AND ', array_map(
                fn($w) => "LOWER(ad_title) LIKE '%" . $esc($w) . "%'",
                $words
            ));
            $parts[] = "(CASE WHEN {$allConds} THEN 6 ELSE 0 END)";
        }

        // 4. Brand + model both matched (first two original words) → 5
        if (count($words) >= 2) {
            $w0 = $esc($words[0]);
            $w1 = $esc($words[1]);
            $parts[] =
                "(CASE WHEN "
                . "EXISTS(SELECT 1 FROM brands WHERE brands.id = ads.brand_id AND LOWER(brands.name) LIKE '%{$w0}%') "
                . "AND EXISTS(SELECT 1 FROM brand_models WHERE brand_models.id = ads.brand_model_id AND LOWER(brand_models.name) LIKE '%{$w1}%') "
                . "THEN 5 ELSE 0 END)";
        }

        // 5. Per-word scoring for every expanded word
        foreach ($expandedWords as $word) {
            if (strlen($word) < 2) continue;
            $w = $esc($word);

            // title → 3
            $parts[] = "(CASE WHEN LOWER(ad_title) LIKE '%{$w}%' THEN 3 ELSE 0 END)";

            // brand → 2
            $parts[] = "(CASE WHEN EXISTS(SELECT 1 FROM brands "
                . "WHERE brands.id = ads.brand_id AND LOWER(brands.name) LIKE '%{$w}%') THEN 2 ELSE 0 END)";

            // model → 2
            $parts[] = "(CASE WHEN EXISTS(SELECT 1 FROM brand_models "
                . "WHERE brand_models.id = ads.brand_model_id AND LOWER(brand_models.name) LIKE '%{$w}%') THEN 2 ELSE 0 END)";

            // attribute → 1
            $parts[] = "(CASE WHEN EXISTS(SELECT 1 FROM ad_attribute_values "
                . "WHERE ad_attribute_values.ad_id = ads.id AND LOWER(ad_attribute_values.value) LIKE '%{$w}%') THEN 1 ELSE 0 END)";

            // description → 1
            $parts[] = "(CASE WHEN LOWER(description) LIKE '%{$w}%' THEN 1 ELSE 0 END)";
        }

        return implode("\n    + ", $parts);
    }

    // ═════════════════════════════════════════════════════════════════════
    //  SUGGESTIONS
    // ═════════════════════════════════════════════════════════════════════
    public function suggestions(Request $request)
    {
        try {
            $query = trim($request->input('query', ''));
            if (strlen($query) < 2) return response()->json([]);

            $word = strtolower($query);

            $titleSuggestions = Ad::where('status', 'active')
                ->whereRaw('LOWER(ad_title) LIKE ?', ["%{$word}%"])
                ->orderByRaw('CASE WHEN LOWER(ad_title) LIKE ? THEN 0 ELSE 1 END', ["{$word}%"])
                ->limit(5)->pluck('ad_title');

            $brandSuggestions    = Brand::whereRaw('LOWER(name) LIKE ?', ["{$word}%"])->limit(3)->pluck('name');
            $categorySuggestions = Category::whereRaw('LOWER(name) LIKE ?', ["{$word}%"])->limit(3)->pluck('name');
            $modelSuggestions    = BrandModel::whereRaw('LOWER(name) LIKE ?', ["{$word}%"])->limit(3)->pluck('name');

            $keywordSuggestions = Ad::where('status', 'active')
                ->whereNotNull('search_keywords')
                ->limit(20)->pluck('search_keywords')
                ->flatMap(function ($kws) use ($word) {
                    $arr = is_array($kws) ? $kws : json_decode($kws, true);
                    if (!is_array($arr)) return [];
                    return collect($arr)->filter(fn($kw) => str_contains(strtolower($kw), $word));
                })
                ->unique()->values()->take(5);

            return response()->json(
                $titleSuggestions
                    ->merge($brandSuggestions)->merge($categorySuggestions)
                    ->merge($modelSuggestions)->merge($keywordSuggestions)
                    ->filter()->unique()->values()->take(10)
            );
        } catch (\Throwable $e) {
            \Log::error('Search suggestions error: ' . $e->getMessage());
            return response()->json([]);
        }
    }

    // ═════════════════════════════════════════════════════════════════════
    //  HELPERS
    // ═════════════════════════════════════════════════════════════════════
    private function splitIntoWords(string $text): array
    {
        $text  = strtolower(preg_replace('/[^a-z0-9\s]/i', '', $text));
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        return array_values(array_unique(array_filter($words, fn($w) => strlen($w) >= 2)));
    }

    private function expandSearchWords(array $words): array
    {
        $synonyms = [
            'mobile'       => ['phone', 'cellphone', 'smartphone'],
            'phone'        => ['mobile', 'cellphone', 'smartphone'],
            'laptop'       => ['notebook', 'computer'],
            'notebook'     => ['laptop', 'computer'],
            'tv'           => ['television'],
            'television'   => ['tv'],
            'bike'         => ['bicycle', 'cycle'],
            'bicycle'      => ['bike', 'cycle'],
            'cycle'        => ['bike', 'bicycle'],
            'fridge'       => ['refrigerator'],
            'refrigerator' => ['fridge'],
            'ac'           => ['airconditioner'],
            'car'          => ['automobile', 'vehicle'],
            'automobile'   => ['car', 'vehicle'],
            'vehicle'      => ['car', 'automobile'],
            'house'        => ['home', 'property'],
            'home'         => ['house', 'property'],
            'property'     => ['house', 'home'],
        ];

        $expanded = $words;
        foreach ($words as $word) {
            $expanded[] = str_ends_with($word, 's') && strlen($word) > 3
                ? rtrim($word, 's')
                : $word . 's';
            if (isset($synonyms[$word])) {
                $expanded = array_merge($expanded, $synonyms[$word]);
            }
        }

        return array_values(array_unique(array_filter($expanded, fn($w) => strlen($w) >= 2)));
    }
}
