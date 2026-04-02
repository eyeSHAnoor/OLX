<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\BrandModel;
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
                'models:id,brand_id,name',
            ])
            ->withCount('models')
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

    /**
     * Get a specific brand by ID
     */
    public function show(Brand $brand)
    {
        $brand->load(['categories', 'models']);
        return response()->json($brand);
    }

    /**
     * Get brands with optional filters
     */
    public function getName(Request $request)
    {
        $query = Brand::with(['categories', 'models']);
        
        if ($request->has('category_id')) {
            $query->whereHas('categories', function($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }
        
        return response()->json($query->orderBy('name')->get());
    }

     /**
     * Show the form for creating a new brand.
     */
    public function create()
    {
        return Inertia::render('brands/Create', [
            'categories' => CategoryData::collect(Category::all()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:brands,name',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'models' => 'nullable|array',
            'models.*.name' => 'required|string|max:255|distinct',
        ]);

        DB::transaction(function () use ($validated) {
            $brand = Brand::create([
                'name' => $validated['name'],
            ]);

            // Sync categories if provided
            if (!empty($validated['category_ids'])) {
                $brand->categories()->sync($validated['category_ids']);
            }

            // Create models if provided
            if (!empty($validated['models'])) {
                $models = collect($validated['models'])->map(function ($model) use ($brand) {
                    return [
                        'brand_id' => $brand->id,
                        'name' => $model['name'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })->toArray();
                
                BrandModel::insert($models);
            }
        });

        return redirect()->back()->with('success', 'Brand created successfully.');
    }

    public function edit(Brand $brand)
    {
        $branddata = $brand->load(['categories', 'models']);
        
        return Inertia::render('brands/Edit', [
            'brand'      => $branddata,
            'categories' => CategoryData::collect(Category::all()),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:brands,name,' . $brand->id,
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'models' => 'nullable|array',
            'models.*.id' => 'nullable|exists:brand_models,id',
            'models.*.name' => 'required|string|max:255|distinct',
        ]);

        DB::transaction(function () use ($validated, $brand) {
            // Update brand name
            $brand->update([
                'name' => $validated['name'],
            ]);

            // Sync categories
            if (isset($validated['category_ids'])) {
                $brand->categories()->sync($validated['category_ids']);
            } else {
                $brand->categories()->detach();
            }

            // Handle models (sync, create, delete)
            if (isset($validated['models'])) {
                $this->syncModels($brand, $validated['models']);
            } else {
                // If no models provided, delete all existing models
                $brand->models()->delete();
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
            // Delete associated models first
            $brand->models()->delete();
            
            // Detach categories
            $brand->categories()->detach();
            
            // Delete the brand
            $brand->delete();
        });

        return redirect()->back()->with('success', 'Brand deleted successfully.');
    }

    /**
     * Sync models for a brand
     * 
     * @param Brand $brand
     * @param array $models
     * @return void
     */
    private function syncModels(Brand $brand, array $models): void
    {
        $existingModelIds = $brand->models()->pluck('id')->toArray();
        $updatedModelIds = [];
        
        foreach ($models as $model) {
            if (isset($model['id']) && in_array($model['id'], $existingModelIds)) {
                // Update existing model
                BrandModel::where('id', $model['id'])
                    ->update([
                        'name' => $model['name'],
                        'updated_at' => now(),
                    ]);
                $updatedModelIds[] = $model['id'];
            } elseif (!isset($model['id'])) {
                // Create new model
                $newModel = $brand->models()->create([
                    'name' => $model['name'],
                ]);
                $updatedModelIds[] = $newModel->id;
            }
        }
        
        // Delete models that are no longer present
        $modelsToDelete = array_diff($existingModelIds, $updatedModelIds);
        if (!empty($modelsToDelete)) {
            BrandModel::whereIn('id', $modelsToDelete)->delete();
        }
    }

    /**
     * Get models for a specific brand (API endpoint)
     */
    public function getModels(Brand $brand)
    {
        return response()->json([
            'models' => $brand->models()->orderBy('name')->get(),
        ]);
    }

    /**
     * Store models for a brand
     */
    public function storeModels(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'models' => 'required|array',
            'models.*.name' => 'required|string|max:255|distinct',
        ]);

        DB::transaction(function () use ($validated, $brand) {
            $models = collect($validated['models'])->map(function ($model) use ($brand) {
                return [
                    'brand_id' => $brand->id,
                    'name' => $model['name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();
            
            BrandModel::insert($models);
        });

        return redirect()->back()->with('success', 'Models added successfully.');
    }

    /**
     * Update a specific model
     */
    public function updateModel(Request $request, BrandModel $model)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:brand_models,name,' . $model->id,
        ]);

        $model->update($validated);

        return redirect()->back()->with('success', 'Model updated successfully.');
    }

    /**
     * Delete a specific model
     */
    public function destroyModel(BrandModel $model)
    {
        $model->delete();
        
        return redirect()->back()->with('success', 'Model deleted successfully.');
    }
}