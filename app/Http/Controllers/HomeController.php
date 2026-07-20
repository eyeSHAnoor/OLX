<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Brand;
use App\Models\Ad;
use App\Models\GiftAssignment;
use App\Models\GiftPeriod;
use App\Models\Banner;
use App\Models\AdView;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Log::info('home controller opened');

        // Log::error('Home controller loads', ['city' => session('city'), 'region' => session('region')]);

        $selectedCity   = strtolower($request->cookie('user_city', 'Pakistan'));
        $selectedRegion = $request->cookie('user_region');

        Log::error('Home controller loads', ['city' => $selectedCity, 'region' => $selectedRegion]);

        // Filters
        $searchTerm    = $request->input('filter.global');
        $categoryFilter = $request->input('filter.category');
        $brandFilter   = $request->input('filter.brand');
        $sort          = $request->input('sort', '-created_at');
        $startDate     = $request->input('start_date');
        $endDate       = $request->input('end_date');

        $isSearching = $searchTerm || $categoryFilter || $brandFilter || $startDate || $endDate;

        /*
        |--------------------------------------------------------------------------
        | STEP: Get current user's recently viewed ads (max 10)
        |--------------------------------------------------------------------------
        */
        $recentAds = collect();
        if (Auth::check()) {
            // Get latest view time per ad, ordered, limited to 10 ad IDs
            $recentAdViews = AdView::where('user_id', Auth::id())
                ->select('ad_id', DB::raw('MAX(created_at) as last_viewed'))
                ->groupBy('ad_id')
                ->orderBy('last_viewed', 'desc')
                ->limit(10)
                ->get();

            $adIds = $recentAdViews->pluck('ad_id');
            if ($adIds->isNotEmpty()) {
                // Fetch full ad models with relations
                $ads = Ad::with(['images', 'brand', 'category'])
                    ->whereIn('id', $adIds)
                    ->where('status', 'active')
                    ->get()
                    // Preserve the order from the grouped query
                    ->sortBy(function ($ad) use ($adIds) {
                        return array_search($ad->id, $adIds->toArray());
                    })
                    ->values();

                $recentAds = $ads;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | STEP 1: Load ALL categories once
        |--------------------------------------------------------------------------
        */
        $allCategories = Category::with('files','children')
            ->select('id', 'parent_id', 'name', 'slug', 'position')
            ->orderBy('position')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | STEP 2: Build parent → children map (FAST)
        |--------------------------------------------------------------------------
        */
        $tree = $allCategories->groupBy('parent_id');

        /*
        |--------------------------------------------------------------------------
        | STEP 3: Recursive function (in-memory, NO DB calls)
        |--------------------------------------------------------------------------
        */
        $getAllChildIds = function ($categoryId) use (&$getAllChildIds, $tree) {
            $ids = [$categoryId];

            if (isset($tree[$categoryId])) {
                foreach ($tree[$categoryId] as $child) {
                    $ids = array_merge($ids, $getAllChildIds($child->id));
                }
            }

            return $ids;
        };

        /*
        |--------------------------------------------------------------------------
        | STEP 4: ROOT categories only
        |--------------------------------------------------------------------------
        */
        $categories = $allCategories
            ->whereNull('parent_id')
            ->values();

        /*
        |--------------------------------------------------------------------------
        | CASE 1: CATEGORY FILTER APPLIED
        |--------------------------------------------------------------------------
        */
        if ($categoryFilter) {

            $selectedCategory = $allCategories->firstWhere('id', $categoryFilter);

            if ($selectedCategory) {

                $ids = $getAllChildIds($selectedCategory->id);

                $ads = $this->buildAdQuery(
                    $ids,
                    $selectedCity,
                    $selectedRegion,
                    $searchTerm,
                    $brandFilter,
                    $startDate,
                    $endDate,
                    $sort
                )->get();

                $selectedCategory->ads = $ads;
                $selectedCategory->ads_count = $ads->count();

                $categories = collect([$selectedCategory]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | CASE 2: HOME PAGE (ALL ROOT CATEGORIES)
        |--------------------------------------------------------------------------
        */
        else {

            $categories = $categories->map(function ($category) use (
                $getAllChildIds,
                $selectedCity,
                $selectedRegion,
                $searchTerm,
                $brandFilter,
                $startDate,
                $endDate,
                $sort
            ) {

                $ids = $getAllChildIds($category->id);

                $ads = $this->buildAdQuery(
                    $ids,
                    $selectedCity,
                    $selectedRegion, 
                    $searchTerm,
                    $brandFilter,
                    $startDate,
                    $endDate,
                    $sort
                )
                ->limit(4)
                ->get();

                $category->ads = $ads;

                return $category;
            });
        }

        /*
        |--------------------------------------------------------------------------
        | OTHER DATA
        |--------------------------------------------------------------------------
        */
        $brands = Brand::with(['categories.files'])->get();

        $banners = Banner::where('position', 'homepage')
            ->where('status', true)
            ->where(fn($q) => $q->whereNull('start_date')->orWhere('start_date', '<=', now()))
            ->where(fn($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()))
            ->orderBy('sort_order')
            ->get();

          /*
        |--------------------------------------------------------------------------
        | STEP: Check if current user is a gift candidate
        |--------------------------------------------------------------------------
        */
        $isGiftCandidate = false;
        $activeGiftPeriod = null;
        $userGiftAssignment = null;

        if (Auth::check()) {
            // Find active gift period
            $activeGiftPeriod = GiftPeriod::where('is_active', true)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->first();

            if ($activeGiftPeriod) {
                // Check if user has a candidate assignment in this period
                $userGiftAssignment = GiftAssignment::where('user_id', Auth::id())
                    ->where('gift_period_id', $activeGiftPeriod->id)
                    ->where('status', 'candidate')
                    ->with(['gift', 'giftPeriod'])
                    ->first();

                $isGiftCandidate = $userGiftAssignment !== null;
            }
        }

        return Inertia::render('home/Index', [
            'categories' => $categories,
            'banners' => $banners,
            'recentAds' => $recentAds,  
            'brands' => $brands,
            'filters' => [
                'filter' => [
                    'global' => $searchTerm,
                    'category' => $categoryFilter,
                    'brand' => $brandFilter,
                ],
                'sort' => $sort,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'isSearching' => $isSearching,
            'isGiftCandidate' => $isGiftCandidate,
            'userGiftAssignment' => $userGiftAssignment ? [
                'id' => $userGiftAssignment->id,
                'gift_name' => $userGiftAssignment->gift->name,
                'gift_image' => $userGiftAssignment->gift->image,
                'period_name' => $userGiftAssignment->giftPeriod->name,
                'status' => $userGiftAssignment->status,
            ] : null,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | REUSABLE AD QUERY (CLEAN)
    |--------------------------------------------------------------------------
    */
    private function buildAdQuery($categoryIds, $city,$region,  $searchTerm, $brandFilter, $startDate, $endDate, $sort)
    {
        return Ad::with(['images', 'brand', 'category'])
            ->where('status', 'active') 
            ->whereIn('category_id', $categoryIds)
            ->excludeReportedBy(Auth::id())
            ->when($city !== 'pakistan', function ($q) use ($city, $region) {
                $q->whereRaw('LOWER(city) = ?', [$city]);

                if ($region) {   // region is not null and not empty
                    $q->whereRaw('LOWER(region) = ?', [strtolower($region)]);
                }
            })

            ->when($searchTerm, function ($q) use ($searchTerm) {
                $term = strtolower($searchTerm);

                $q->where(function ($q) use ($term) {
                    $q->whereRaw('LOWER(ad_title) LIKE ?', ["%{$term}%"])
                      ->orWhereRaw('LOWER(description) LIKE ?', ["%{$term}%"])
                      ->orWhereHas('brand', fn($b) =>
                          $b->whereRaw('LOWER(name) LIKE ?', ["%{$term}%"])
                      )
                      ->orWhereHas('category', fn($c) =>
                          $c->whereRaw('LOWER(name) LIKE ?', ["%{$term}%"])
                      );
                });
            })

            ->when($brandFilter, fn($q) =>
                $q->where('brand_id', $brandFilter)
            )

            ->when($startDate, fn($q) =>
                $q->whereDate('created_at', '>=', $startDate)
            )

            ->when($endDate, fn($q) =>
                $q->whereDate('created_at', '<=', $endDate)
            )

            ->orderBy('is_featured', 'desc')

            ->when($sort, function ($q) use ($sort) {
                if (str_starts_with($sort, '-')) {
                    $q->orderByDesc(substr($sort, 1));
                } else {
                    $q->orderBy($sort);
                }
            }, fn($q) => $q->latest());
    }

    public function account()
    {
        return Inertia::render('home/Account');
    }
}