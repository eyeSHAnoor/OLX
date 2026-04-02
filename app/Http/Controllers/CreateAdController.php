<?php
// app/Http/Controllers/Ad/CreateAdController.php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Data\CategoryData;
use App\Models\Brand;
use App\Models\AdImage;
use App\Models\Feature;
use App\Models\Ad;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Facades\DB;

class CreateAdController extends Controller
{
    public function index()
    {
        // Get all top-level categories with their children
        $categories = Category::with(['childrenRecursive', 'brands', 'files'])
            ->whereNull('parent_id')
            ->orderBy('position')
            ->get();

        

        return Inertia::render('ads/create/Index', [
            'categories' => $categories,
             'features' => Feature::with('values')->orderBy('name')->get(),
        ]);
    }

    public function getCategoryData(Category $category)
    {
        // Get brands for this category
        $brands = $category->brands()->get();

        // Get features for this category (you'll need to add relationship)
        // Assuming you have a category_features pivot table
        $features = Feature::whereHas('categories', function($query) use ($category) {
            $query->where('category_id', $category->id);
        })->with('values')->get();

        return response()->json([
            'brands' => $brands,
            'features' => $features,
            'category' => $category->load('parent')
        ]);
    }

    public function edit($id)
    {
        // Find the ad by ID
        $ad = Ad::with(['category', 'features', 'images', 'features.values', 'brand', 'model'])->findOrFail($id);

        $attributes = $ad->attributes->map(function ($attr) {
                    return [
                        'id' => $attr->id,
                        'category_attribute_id' => $attr->category_attribute_id,
                        'name' => $attr->attribute?->name,
                        'type' => $attr->attribute?->type,
                        'is_required' => $attr->attribute?->is_required,
                        'is_filterable' => $attr->attribute?->is_filterable,
                        'value' => $attr->value,
                        'options' => $attr->attribute?->options->map(fn($opt) => [
                            'id' => $opt->id,
                            'value' => $opt->value,
                        ])->toArray() ?? [],
                    ];
                });

        // Get all top-level categories with their children
        return Inertia::render('ads/create/Index', [
            'ad' => $ad,
             'attributes' =>$attributes,
            'categories' => Category::with(['childrenRecursive', 'brands', 'files'])
                            ->whereNull('parent_id')
                            ->orderBy('position')
                            ->get(),
            'brands' => Brand::with('categories:id,name')
                ->orderBy('name')
                ->get(),
            'features' => Feature::with('values')->orderBy('name')->get(),
        ]);
    }

    public function Myads(Request $request)
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
        $status = $request->input('filter.status');

        // --------------------------
        // BASE QUERY
        // --------------------------
        $baseQuery = Ad::where('user_id', $user->id)
            ->with(['images', 'category.parent', 'brand', 'features.values']);

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
            ->when($maxPrice, fn($q) => $q->where('price', '<=', $maxPrice))
            ->when($status, fn($q) => $q->where('status', $status));

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
        $ads = $baseQuery->paginate(12)->withQueryString();

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

        return Inertia::render('ads/public/Index', [
            'ads' => $ads,
            'categories' => $categories,
            'brands' => $brands,
            'filters' => [
                'filter' => [
                    'global' => $searchTerm,
                    'category' => $categoryId,
                    'brand' => $brand,
                    'min_price' => $minPrice,
                    'max_price' => $maxPrice,
                     'status' => $status,
                ],
                'sort' => $sort,
            ],
            'priceRange' => $priceRange,
        ]);
    }

    
    public function updateStatus(Request $request, Ad $ad)
    {
        // Authorize: the ad belongs to the authenticated user
        if ($ad->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:active,inactive,sold'
        ]);

        $newStatus = $request->status;

        // Allow sold -> active transition (reactivate)
        if ($ad->status === 'sold' && $newStatus === 'active') {
            $ad->status = 'active';
            $ad->is_active = true; 
            $ad->save();
            return back()->with('success', 'Ad reactivated successfully.');
        }

        // Normal active/inactive toggle
        if (in_array($ad->status, ['active', 'inactive']) && in_array($newStatus, ['active', 'inactive'])) {
            $ad->status = $newStatus;
            // Update is_active according to status
            $ad->is_active = $newStatus === 'active' ? true : false;
            $ad->save();
            return back()->with('success', 'Ad status updated.');
        }

        return back()->with('error', 'Invalid status transition.');
    }
}