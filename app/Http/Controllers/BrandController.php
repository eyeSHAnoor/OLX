<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Brand::with(['categories' => function ($query) {
            $query->select('categories.id', 'categories.name');
        }]);

        // Search filter
        if ($request->has('filter.global') && $request->input('filter.global')) {
            $search = $request->input('filter.global');
            $query->where('name', 'like', "%{$search}%");
        }

        // Pagination
        $perPage = $request->input('perPage', 10);
        $brands = $query->paginate($perPage);

        // Get all categories for dropdown
        $categories = Category::all();

        return Inertia::render('brands/Index', [
            'brands' => $brands,
            'categories' => $categories,
            'filters' => $request->only(['filter.global']),
            'perPage' => $perPage,
        ]);
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