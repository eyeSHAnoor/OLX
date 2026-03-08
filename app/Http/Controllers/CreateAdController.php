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
        $ad = Ad::with(['category', 'features', 'images'])->findOrFail($id);

        // Get all top-level categories with their children
        $categories = Category::with(['childrenRecursive', 'brands'])
            ->whereNull('parent_id')
            ->orderBy('position')
            ->get();

        // Get all features with their values
        $features = Feature::with('values')->orderBy('name')->get();

        return Inertia::render('ads/public/RecordForm', [
            'ad' => $ad,
            'categories' => $categories,
            'features' => $features,
        ]);
    }

    public function Myads(Request $request)
    {
        $user = auth()->user();
        
        // Columns allowed to sort
        $columns = ['ad_title', 'price', 'location', 'seller_name', 'brand_id', 'category_id', 'city', 'created_at', 'status'];

        // Global search filter helper
        $globalSearch = getGlobalSearchFilter([...$columns]);
        
        $ads = QueryBuilder::for(Ad::class)
            ->where('user_id', $user->id) 
            ->with(['brand', 'category', 'images', 'features.values'])
            ->withCount('images')
            ->defaultSort('-created_at')
            ->allowedSorts($columns)
            ->allowedFilters([
                $globalSearch,
                AllowedFilter::exact('category_id'),
                AllowedFilter::exact('brand_id'),
                AllowedFilter::exact('status'),
                AllowedFilter::callback('min_price', fn($query, $value) => $query->where('price', '>=', $value)),
                AllowedFilter::callback('max_price', fn($query, $value) => $query->where('price', '<=', $value)),
            ])
            ->paginate(getPaginate()) // your helper for pagination
            ->withQueryString();

        // Get categories and brands for filter dropdowns
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        $brands = Brand::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('ads/public/Index', [
            'ads' => $ads,
            'categories' => $categories,
            'brands' => $brands,
            'filters' => $request->only(['search', 'category_id', 'brand_id', 'status', 'min_price', 'max_price'])
        ]);
    }
}