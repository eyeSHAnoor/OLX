<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Brand;
use App\Models\Ad;
use Inertia\Inertia;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $selectedCity = strtolower(session('city', 'Pakistan'));

        // Search / filter inputs
        $searchTerm = $request->input('filter.global', null);
        $categoryFilter = $request->input('filter.category', null);
        $brandFilter = $request->input('filter.brand', null);
        $sort = $request->input('sort', 'created_at');
        $startDate = $request->input('start_date', null);
        $endDate = $request->input('end_date', null);

        $isSearching = !empty($searchTerm) || !empty($categoryFilter) || !empty($brandFilter) || !empty($startDate) || !empty($endDate);

        // Fetch all root categories with their recursive children and files
        $categories = Category::whereNull('parent_id')
            ->with(['childrenRecursive', 'files'])
            ->orderBy('position')
            ->get();

        // Case 1: Category filter is applied
        if (!empty($categoryFilter)) {
            $selectedCategory = Category::with(['childrenRecursive', 'files'])->find($categoryFilter);

            if ($selectedCategory) {
                $adQuery = Ad::with(['images', 'brand', 'category'])
                    ->when($selectedCity !== 'pakistan', fn($q) => $q->whereRaw('LOWER(city) = ?', [$selectedCity]));

                // If main category (has children), include ads from itself + leaf subcategories
                if ($selectedCategory->children()->exists()) {
                    $categoryIds = $selectedCategory->getLeafCategoriesEfficient()->pluck('id')->toArray();
                    $categoryIds[] = $selectedCategory->id;
                    $adQuery->whereIn('category_id', $categoryIds);
                } else {
                    // Subcategory → exact match
                    $adQuery->where('category_id', $categoryFilter);
                }

                // Apply global search
                if (!empty($searchTerm)) {
                    $searchTermLower = strtolower($searchTerm);
                    $adQuery->where(function ($q) use ($searchTermLower) {
                        $q->whereRaw('LOWER(ad_title) LIKE ?', ["%{$searchTermLower}%"])
                          ->orWhereRaw('LOWER(description) LIKE ?', ["%{$searchTermLower}%"])
                          ->orWhereHas('brand', fn($b) => $b->whereRaw('LOWER(name) LIKE ?', ["%{$searchTermLower}%"]))
                          ->orWhereHas('category', fn($c) => $c->whereRaw('LOWER(name) LIKE ?', ["%{$searchTermLower}%"]));
                    });
                }

                // Brand filter
                if (!empty($brandFilter)) {
                    $adQuery->where('brand_id', $brandFilter);
                }

                // Date filters
                if (!empty($startDate)) {
                    $adQuery->whereDate('created_at', '>=', $startDate);
                }
                if (!empty($endDate)) {
                    $adQuery->whereDate('created_at', '<=', $endDate);
                }

                // Sorting
                if ($sort) {
                    if (str_starts_with($sort, '-')) {
                        $adQuery->orderByDesc(substr($sort, 1));
                    } else {
                        $adQuery->orderBy($sort);
                    }
                } else {
                    $adQuery->latest();
                }

                $selectedCategory->ads = $adQuery->get();
                $selectedCategory->ads_count = $selectedCategory->ads->count();

                // Wrap the category in a collection to pass to the frontend
                $categories = collect([$selectedCategory]);
            }
        }
        // Case 2: No category filter → fetch ads for all root categories + their subcategories
        else {
            $categories->each(function ($category) use ($selectedCity, $searchTerm, $brandFilter, $startDate, $endDate, $sort) {
                $leafCategories = $category->getLeafCategoriesEfficient();
                $categoryIds = $leafCategories->pluck('id')->toArray();
                $categoryIds[] = $category->id;

                $adQuery = Ad::with(['images', 'brand', 'category'])
                    ->whereIn('category_id', $categoryIds)
                    ->when($selectedCity !== 'pakistan', fn($q) => $q->whereRaw('LOWER(city) = ?', [$selectedCity]));

                if (!empty($searchTerm)) {
                    $searchTermLower = strtolower($searchTerm);
                    $adQuery->where(function ($q) use ($searchTermLower) {
                        $q->whereRaw('LOWER(ad_title) LIKE ?', ["%{$searchTermLower}%"])
                          ->orWhereRaw('LOWER(description) LIKE ?', ["%{$searchTermLower}%"])
                          ->orWhereHas('brand', fn($b) => $b->whereRaw('LOWER(name) LIKE ?', ["%{$searchTermLower}%"]))
                          ->orWhereHas('category', fn($c) => $c->whereRaw('LOWER(name) LIKE ?', ["%{$searchTermLower}%"]));
                    });
                }

                if (!empty($brandFilter)) {
                    $adQuery->where('brand_id', $brandFilter);
                }

                if (!empty($startDate)) {
                    $adQuery->whereDate('created_at', '>=', $startDate);
                }
                if (!empty($endDate)) {
                    $adQuery->whereDate('created_at', '<=', $endDate);
                }

                if ($sort) {
                    if (str_starts_with($sort, '-')) {
                        $adQuery->orderByDesc(substr($sort, 1));
                    } else {
                        $adQuery->orderBy($sort);
                    }
                } else {
                    $adQuery->latest();
                }

                $category->ads = $adQuery->get();
                $category->ads_count = $category->ads->count();
            });
        }

        // Get all brands for filters
        $brands = Brand::with(['categories.files'])->get();

        return Inertia::render('home/Index', [
            'categories' => $categories,
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

}
