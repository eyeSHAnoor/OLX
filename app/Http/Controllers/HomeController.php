<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Brand;
use App\Models\Ad;
use App\Models\Banner;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        Log::info('home controller opened');

        $selectedCity = strtolower(session('city', 'Pakistan'));

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
        | STEP 1: Load ALL categories once
        |--------------------------------------------------------------------------
        */
        $allCategories = Category::with('files')
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
                    $searchTerm,
                    $brandFilter,
                    $startDate,
                    $endDate,
                    $sort
                )
                ->limit(4) // ✅ ONLY 4 ADS
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

        return Inertia::render('home/Index', [
            'categories' => $categories,
            'banners' => $banners,
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
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | REUSABLE AD QUERY (CLEAN)
    |--------------------------------------------------------------------------
    */
    private function buildAdQuery($categoryIds, $city, $searchTerm, $brandFilter, $startDate, $endDate, $sort)
    {
        return Ad::with(['images', 'brand', 'category'])
            ->where('status', 'active') 
            ->whereIn('category_id', $categoryIds)
            ->excludeReportedBy(Auth::id())
            ->when($city !== 'pakistan', fn($q) =>
                $q->whereRaw('LOWER(city) = ?', [$city])
            )

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