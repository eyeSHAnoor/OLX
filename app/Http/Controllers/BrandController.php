<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Data\BrandData;
use App\Data\CategoryData;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        $columns = [
            'name',
            'created_at',
        ];

        // Global search helper (same as products)
        $globalSearch = getGlobalSearchFilter([...$columns]);

        $brands = QueryBuilder::for(Brand::class)
            ->with([
                'categories:id,name',
            ])
            ->defaultSort('-created_at')
            ->allowedSorts($columns)
            ->allowedFilters([
                $globalSearch,
            ])
            ->paginate(getPaginate())   
            ->withQueryString();

        return Inertia::render('brands/Index', [
            'brands'     => $brands,
            'categories' => CategoryData::collect(Category::all()),
        ]);
    }

    // Get a specific brand by ID
    public function show(Brand $brand)
    {
        $brand->load('categories');
        return response()->json($brand);
    }

    // Alternative: Get brands with optional category filter
    public function getName(Request $request)
    {
        $query = Brand::with('categories');
        
        if ($request->has('category_id')) {
            $query->whereHas('categories', function($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }
        
        return response()->json($query->orderBy('name')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:brands,name',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        DB::transaction(function () use ($request) {
            $brand = Brand::create([
                'name' => $request->name,
            ]);

            if ($request->has('category_ids')) {
                $brand->categories()->sync($request->category_ids);
            }
        });

        return redirect()->back()->with('success', 'Brand created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Brand $brand)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:brands,name,' . $brand->id,
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        DB::transaction(function () use ($request, $brand) {
            $brand->update([
                'name' => $request->name,
            ]);

            if ($request->has('category_ids')) {
                $brand->categories()->sync($request->category_ids);
            } else {
                $brand->categories()->detach();
            }
        });

        return redirect()->back()->with('success', 'Brand updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand)
    {
        DB::transaction(function () use ($brand) {
            $brand->categories()->detach();
            $brand->delete();
        });

        return redirect()->back()->with('success', 'Brand deleted successfully.');
    }
}