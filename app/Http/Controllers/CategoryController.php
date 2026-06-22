<?php

namespace App\Http\Controllers;

use App\Data\CategoryData;
use App\Models\Category;
use App\Models\Ad;
use App\Models\Brand;
use App\Models\AttributeGroup;
use App\Models\CategoryAttribute;
use App\Models\AttributeOption;
use App\Models\Banner;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function show(Request $request, $slug = null)
    {
        // $selectedCitySession = strtolower(session('city', 'Pakistan'));
        $selectedCitySession = strtolower($request->cookie('user_city', 'Pakistan'));
        $selectedCitySession = $selectedCitySession === 'pakistan' ? 'all' : $selectedCitySession;
        $selectedRegion = $request->cookie('user_region') 
                      ?? session('region');

        $category = $slug
            ? Category::where('slug', $slug)
                ->with(['childrenRecursive', 'files'])
                ->firstOrFail()
            : null;

        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        $cityInput = $request->input('filter.city', $selectedCitySession);
        $cityInput = is_string($cityInput) ? strtolower(trim($cityInput)) : '';
        if ($cityInput === 'pakistan' || $cityInput === 'all' || $cityInput === '') {
            $cityInput = 'all';
        }

        $filters = [
            'global'     => $request->input('filter.global'),
            'category'   => $request->input('filter.category', $category?->id),
            'brand'      => $request->input('filter.brand'),
            'model'      => $request->input('filter.model'),
            'min_price'  => $request->input('filter.min_price'),
            'max_price'  => $request->input('filter.max_price'),
            'city'       => $cityInput,
            'region'     => $selectedRegion,
        ];

        $sort = $request->input('sort', 'newest');

        /*
        |--------------------------------------------------------------------------
        | Attribute Filters
        |--------------------------------------------------------------------------
        */

        $attributeFilters = collect($request->input('filter', []))
            ->filter(fn ($value, $key) => str_starts_with($key, 'attribute_'))
            ->map(function ($value) {
                if (is_string($value) && str_contains($value, ',')) {
                    return explode(',', $value);
                }
                return $value;
            })
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Base Ads Query
        |--------------------------------------------------------------------------
        */

        $baseQuery = Ad::query()
            ->where('status', 'active')
            ->with(['images', 'brand', 'category', 'model'])

            ->when(
                !in_array($filters['city'], ['pakistan', 'all', ''], true),
                fn ($q) => $q->whereRaw('LOWER(city) = ?', [$filters['city']])
            )
            ->when(
                $filters['city'] !== 'all' && !empty($filters['region']),
                fn ($q) => $q->whereRaw('LOWER(region) = ?', [strtolower($filters['region'])])
            )

            ->when($filters['global'], function ($q) use ($filters) {
                $search = strtolower($filters['global']);

                $q->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(ad_title) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(description) LIKE ?', ["%{$search}%"])
                        ->orWhereHas('brand', fn ($b) =>
                            $b->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                        );
                });
            })

            ->when(
                $filters['brand'],
                fn ($q) => $q->whereIn('brand_id', explode(',', $filters['brand']))
            )

            ->when(
                $filters['model'],
                fn ($q) => $q->whereIn('brand_model_id', explode(',', $filters['model']))
            )

            ->when(
                $filters['min_price'],
                fn ($q) => $q->where('price', '>=', $filters['min_price'])
            )

            ->when(
                $filters['max_price'],
                fn ($q) => $q->where('price', '<=', $filters['max_price'])
            )

            ->when(!empty($attributeFilters), function ($q) use ($attributeFilters) {
                foreach ($attributeFilters as $key => $value) {

                    if (!$value) continue;

                    $attrId = str_replace('attribute_', '', $key);

                    $q->whereHas('attributes', function ($subQ) use ($attrId, $value) {
                        $subQ->where('category_attribute_id', $attrId)
                            ->whereIn('value', (array) $value);
                    });
                }
            })
            
            ->orderBy('is_featured', 'desc')
            ->when($sort === 'price_low', fn ($q) => $q->orderBy('price', 'asc'))
            ->when($sort === 'price_high', fn ($q) => $q->orderBy('price', 'desc'))
            ->when(!in_array($sort, ['price_low', 'price_high']), fn ($q) => $q->latest());

        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */

        $categories = Category::whereNull('parent_id')
            ->with(['childrenRecursive', 'files'])
            ->orderBy('position')
            ->get();

        $allBrands = Brand::with('models:id,brand_id,name')
            ->select('id', 'name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | When Category Selected
        |--------------------------------------------------------------------------
        */

        if ($filters['category'] &&
            $selectedCategory = Category::with([
                'childrenRecursive',
                'files',
                'attributes.group',
                'attributes.options',
                'brands.models',
                'parent',
                'parent.attributes.group',
                'parent.attributes.options',
                'parent.brands.models'
            ])->find($filters['category']))
        {

            /*
            |--------------------------------------------------------------------------
            | Category IDs (include children)
            |--------------------------------------------------------------------------
            */

            $categoryIds = $selectedCategory
                ->getLeafCategoriesEfficient()
                ->pluck('id')
                ->push($selectedCategory->id)
                ->unique();

            $adQuery = (clone $baseQuery)->whereIn('category_id', $categoryIds);

            $ads = $adQuery->paginate(10)->withQueryString();

            $selectedCategory->ads = $ads;
            $selectedCategory->ads_count = $ads->total();

            /*
            |--------------------------------------------------------------------------
            | Attributes (Parent's for children, own for parent)
            |--------------------------------------------------------------------------
            */

            $attributes = collect();

            if ($selectedCategory->parent && $selectedCategory->parent->attributes->isNotEmpty()) {
                $attributes = $selectedCategory->parent->attributes;
            } else {
                $attributes = $selectedCategory->attributes;
            }

            /*
            |--------------------------------------------------------------------------
            | Brands (Child + Parent)
            |--------------------------------------------------------------------------
            */

            $brands = collect();

            if ($selectedCategory->brands->isNotEmpty()) {
                $brands = $brands->merge($selectedCategory->brands);
            }

            if ($selectedCategory->parent && $selectedCategory->parent->brands->isNotEmpty()) {
                $brands = $brands->merge($selectedCategory->parent->brands);
            }

            $brands = $brands->unique('id')->values();

            /*
            |--------------------------------------------------------------------------
            | If still empty → derive from ads
            |--------------------------------------------------------------------------
            */

            if ($brands->isEmpty()) {

                $brands = Brand::whereIn(
                    'id',
                    (clone $adQuery)->reorder()->pluck('brand_id')->unique()
                )->with('models:id,brand_id,name')->get();
            }

            // Fetch banners for category page
        $banners = Banner::active()
            ->where('position', 'category')
            ->where(function ($q) use ($selectedCategory) {
                // Banner assigned to parent category
                if ($selectedCategory->parent) {
                    $q->where('target_category_id', $selectedCategory->parent->id);
                }

                // Banner assigned to current category
                $q->orWhere('target_category_id', $selectedCategory->id);

                // Banner with no category → global
                $q->orWhereNull('target_category_id');
            })
            ->orderBy('sort_order', 'asc')
            ->get();

            // dd($banners);

            return Inertia::render('home/Category', [
                'category' => $selectedCategory,
                'categories' => $categories,
                'brands' => $brands,
                 'banners' => $banners,
                'attributes' => $attributes,
                // 'banners' => [],
                'topBanner' => null,
                'filters' => [
                    'filter' => $filters,
                    'sort' => $sort,
                    'attributeFilters' => $attributeFilters
                ],
                'priceRange' => [
                    'min' => (clone $adQuery)->min('price'),
                    'max' => (clone $adQuery)->max('price'),
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | No Category Selected
        |--------------------------------------------------------------------------
        */

        $categories->each(function ($cat) use ($baseQuery) {

            $catIds = $cat->getLeafCategoriesEfficient()
                ->pluck('id')
                ->push($cat->id)
                ->unique();

            $query = (clone $baseQuery)->whereIn('category_id', $catIds);

            $cat->ads = $query->limit(2)->get();
            $cat->ads_count = $query->count();
        });

        return Inertia::render('home/Category', [
            'categories' => $categories,
            'brands' => $allBrands,
            'filters' => [
                'filter' => $filters,
                'sort' => $sort,
                'attributeFilters' => $attributeFilters
            ],
        ]);
    }
    /**
     * Display a listing of categories.
     */
    public function index()
    {
        // Columns allowed for sorting and filtering
        $columns = ['name', 'parent_id', 'position'];

        // Example global search helper (you can define it in a helper file)
        $globalSearch = getGlobalSearchFilter([...$columns]);

        // Fetch top-level categories with their children recursively
        $data = QueryBuilder::for(Category::query())
            ->whereNull('parent_id')
            ->with(['childrenRecursive', 'files'])
            ->defaultSort('-created_at')
            ->allowedSorts($columns)
            ->allowedFilters([...$columns, $globalSearch])
            ->get();

        // dd($data);

        // Fetch all categories (for dropdowns etc.)
        $allCategories = Category::with(['childrenRecursive', 'files'])->get();

        return Inertia::render('categories/Index', [
            // 'categories' => CategoryData::collect($data),
            'categories'=>$data,
            'allCategories' => CategoryData::collect($allCategories),
        ]);
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        $allCategories = Category::with('files')->get();
        $allBrands = Brand::orderBy('name')->get();
        $attributeGroups = AttributeGroup::with('attributes')->orderBy('name')->get();

        return inertia('categories/RecordForm', [
            'allCategories' => $allCategories,
            'allBrands' => $allBrands,
            'attributeGroups' => $attributeGroups,
        ]);
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
        'name' => ['required', 'string', 'max:255', Rule::unique('categories')->where(function ($query) use ($request) {
        return $query->where('parent_id', $request->parent_id);
        })],
        'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
        'position' => ['nullable', 'integer', 'min:0'],
        'image' => ['nullable', 'image', 'max:2048'],
        'brand_ids' => ['nullable', 'array'],
        'brand_ids.*' => ['exists:brands,id'],
        'attributes' => ['nullable', 'array'],
        'attributes.*.name' => ['required', 'string', 'max:255'],
        'attributes.*.type' => ['required', 'in:text,number,select,radio,checkbox,date'],
        'attributes.*.attribute_group_id' => ['nullable', 'exists:attribute_groups,id'],
        'attributes.*.is_required' => ['boolean'],
        'attributes.*.is_filterable' => ['boolean'],
        'attributes.*.options' => ['nullable','array'],
        'attributes.*.options.*.value' => ['nullable','string','max:255']
        ]);

        DB::beginTransaction();

        try {
            // Generate slug from name
            $slug = $this->generateUniqueSlug($validated['name'], $validated['parent_id'] ?? null);

            // Create category
            $category = Category::create([
                'name' => $validated['name'],
                'slug' => $slug,
                'parent_id' => $validated['parent_id'] ?? null,
                'position' => $validated['position'] ?? $this->getNextPosition($validated['parent_id'] ?? null),
            ]);

            // Handle category image
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('categories', 'public');
                $category->files()->create([
                    'file_location' => $path,
                    'file_name' => $request->file('image')->getClientOriginalName(),
                    'collection' => 'category_images',
                ]);
            }

            // Sync brands
            if ($request->has('brand_ids')) {
                $category->brands()->sync($validated['brand_ids']);
            }

            // Create attributes with their options
            if (!empty($validated['attributes'])){
                foreach ($validated['attributes'] as $index => $attrData) {
                    // Create the attribute
                    $attribute = CategoryAttribute::create([
                        'category_id' => $category->id,
                        'attribute_group_id' => $attrData['attribute_group_id'] ?? null,
                        'name' => $attrData['name'],
                        'type' => $attrData['type'],
                        'is_required' => $attrData['is_required'] ?? false,
                        'is_filterable' => $attrData['is_filterable'] ?? false,
                        'position' => $index,
                    ]);

                    // Create options for select type attributes
                    if (in_array($attrData['type'], ['select','radio','checkbox']) && !empty($attrData['options'])) {
                        foreach ($attrData['options'] as $optIndex => $option) {
                            if (!empty($option['value'])) {
                                AttributeOption::create([
                                    'category_attribute_id' => $attribute->id,
                                    'value' => $option['value'],
                                    'position' => $optIndex,
                                ]);
                            }
                        }
                    }
                }
            }

            DB::commit();

            return redirect()->route('categories.index')
                ->with('success', 'Category created successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create category: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category)
    {
        // Load all relationships with proper ordering
        $category->load([
            'files', 
            'brands', 
            'attributes' => function($query) {
                $query->orderBy('position');
            },
            'attributes.options' => function($query) {
                $query->orderBy('position');
            },
            'attributes.group',
            'parent'
        ]);
        
        $allCategories = Category::with('files')
            ->where('id', '!=', $category->id)
            ->get();
            
        $allBrands = Brand::orderBy('name')->get();
        $attributeGroups = AttributeGroup::with('attributes')->orderBy('name')->get();

        return inertia('categories/RecordForm', [
            'category' => $category,
            'allCategories' => $allCategories,
            'allBrands' => $allBrands,
            'attributeGroups' => $attributeGroups,
        ]);
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('categories')
                    ->where(function ($query) use ($request) {
                        return $query->where('parent_id', $request->parent_id);
                    })
                    ->ignore($category->id)
            ],
            'parent_id' => [
                'nullable', 'integer', 'exists:categories,id',
                function ($attribute, $value, $fail) use ($category) {
                    if ($value == $category->id) {
                        $fail('A category cannot be its own parent.');
                    }
                    if ($this->isDescendant($value, $category)) {
                        $fail('Cannot assign a descendant category as parent.');
                    }
                }
            ],
            'position' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
            'remove_image' => ['boolean'],
            'brand_ids' => ['nullable', 'array'],
            'brand_ids.*' => ['exists:brands,id'],
            'attributes' => ['nullable', 'array'],
            'attributes.*.id' => ['nullable', 'exists:category_attributes,id'],
            'attributes.*.name' => ['required', 'string', 'max:255'],
            'attributes.*.type' => ['required', 'in:text,number,select,radio,checkbox,date'],
            'attributes.*.attribute_group_id' => ['nullable', 'exists:attribute_groups,id'],
            'attributes.*.is_required' => ['boolean'],
            'attributes.*.is_filterable' => ['boolean'],
            'attributes.*.options' => ['nullable','array'],
            'attributes.*.options.*.value' => ['nullable','string','max:255']
  ]);

        DB::beginTransaction();

        try {
            // Update basic info
            $updateData = [
                'name' => $validated['name'],
                'parent_id' => $validated['parent_id'] ?? null,
            ];

            // Regenerate slug if name changed
            if ($validated['name'] !== $category->name) {
                $updateData['slug'] = $this->generateUniqueSlug($validated['name'], $validated['parent_id'] ?? null, $category->id);
            }

            // Update position if parent changed
            if (($validated['parent_id'] ?? null) != $category->parent_id) {
                $updateData['position'] = $validated['position'] ?? $this->getNextPosition($validated['parent_id'] ?? null);
            } elseif (isset($validated['position'])) {
                $updateData['position'] = $validated['position'];
            }

            $category->update($updateData);

            // Handle image
            if ($request->has('remove_image') && $request->remove_image) {
                foreach ($category->files as $file) {
                    if (Storage::disk('public')->exists($file->file_location)) {
                        Storage::disk('public')->delete($file->file_location);
                    }
                    $file->delete();
                }
            } elseif ($request->hasFile('image')) {
                $path = $request->file('image')->store('categories', 'public');
                
                // Delete old images
                foreach ($category->files as $file) {
                    if (Storage::disk('public')->exists($file->file_location)) {
                        Storage::disk('public')->delete($file->file_location);
                    }
                    $file->delete();
                }
                
                // Save new image
                $category->files()->create([
                    'file_location' => $path,
                    'file_name' => $request->file('image')->getClientOriginalName(),
                    'collection' => 'category_images',
                ]);
            }

            // Sync brands
            $category->brands()->sync($validated['brand_ids'] ?? []);

            // Handle attributes with their options
            $existingAttributeIds = [];
            
            if (!empty($validated['attributes'])) {
                foreach ($validated['attributes'] as $attrData) {
                    if (isset($attrData['id'])) {
                        // Update existing attribute
                        $attribute = CategoryAttribute::find($attrData['id']);

                        if (empty($attrData['name']) || empty($attrData['type'])) {
                            continue;
                        }
                        
                        if ($attribute && $attribute->category_id == $category->id) {
                            $attribute->update([
                                'attribute_group_id' => $attrData['attribute_group_id'] ?? null,
                                'name' => $attrData['name'],
                                'type' => $attrData['type'],
                                'is_required' => $attrData['is_required'] ?? false,
                                'is_filterable' => $attrData['is_filterable'] ?? false,
                                'position' => $attrData['position'] ?? 0,
                            ]);
                            
                            $existingAttributeIds[] = $attribute->id;
                            
                            // Handle options for select type
                            if (in_array($attrData['type'], ['select','radio','checkbox'])) {

            $existingOptionIds = [];

            if (!empty($attrData['options'])) {
                foreach ($attrData['options'] as $optIndex => $optData) {

                    if (empty($optData['value'])) {
                        continue;
                    }

                    if (isset($optData['id'])) {

                        $option = AttributeOption::find($optData['id']);

                        if ($option && $option->category_attribute_id == $attribute->id) {
                            $option->update([
                                'value' => $optData['value'],
                                'position' => $optIndex,
                            ]);

                            $existingOptionIds[] = $option->id;
                        }

                    } else {

                        $option = AttributeOption::create([
                            'category_attribute_id' => $attribute->id,
                            'value' => $optData['value'],
                            'position' => $optIndex,
                        ]);

                        $existingOptionIds[] = $option->id;
                    }
                }
            }

            AttributeOption::where('category_attribute_id', $attribute->id)
                ->whereNotIn('id', $existingOptionIds)
                ->delete();

        } else {

            AttributeOption::where('category_attribute_id', $attribute->id)->delete();
        }
                                }
                            } else {
                                // Create new attribute
                                $attribute = CategoryAttribute::create([
                                    'category_id' => $category->id,
                                    'attribute_group_id' => $attrData['attribute_group_id'] ?? null,
                                    'name' => $attrData['name'],
                                    'type' => $attrData['type'],
                                    'is_required' => (bool) ($attrData['is_required'] ?? false),
                                    'is_filterable' => (bool) ($attrData['is_filterable'] ?? false),
                                    'position' => $attrData['position'] ?? 0,
                                ]);
                                
                                $existingAttributeIds[] = $attribute->id;
                                
                                // Create options for select type
                                if ($attrData['type'] === 'select' && !empty($attrData['options'])) {
                                    foreach ($attrData['options'] as $optIndex => $optData) {
                                        if (!empty($optData['value'])) {
                                            AttributeOption::create([
                                                'category_attribute_id' => $attribute->id,
                                                'value' => $optData['value'],
                                                'position' => $optIndex,
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                    
            // Delete attributes that were removed (including their options - cascading will handle)
            CategoryAttribute::where('category_id', $category->id)
                ->whereNotIn('id', $existingAttributeIds)
                ->delete();

            DB::commit();

            return redirect()->route('categories.index')
                ->with('success', 'Category updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update category: ' . $e->getMessage());
        }
    }

    /**
     * Helper methods
     */
    private function generateUniqueSlug($name, $parentId = null, $ignoreId = null)
    {
        $slug = \Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;
        
        $query = Category::where('slug', $slug);
        
        if ($parentId) {
            $query->where('parent_id', $parentId);
        } else {
            $query->whereNull('parent_id');
        }
        
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }
        
        while ($query->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $query = Category::where('slug', $slug);
            if ($parentId) {
                $query->where('parent_id', $parentId);
            } else {
                $query->whereNull('parent_id');
            }
            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }
            $counter++;
        }
        
        return $slug;
    }

    private function getNextPosition($parentId = null)
    {
        $query = Category::where('parent_id', $parentId);
        
        if ($parentId === null) {
            $query->whereNull('parent_id');
        }
        
        $maxPosition = $query->max('position');
        return $maxPosition !== null ? $maxPosition + 1 : 0;
    }

    private function isDescendant($parentId, Category $category)
    {
        if (!$parentId) return false;
        
        $parent = Category::find($parentId);
        if (!$parent) return false;
        
        $descendants = $parent->descendantsAndSelf()->pluck('id')->toArray();
        return in_array($category->id, $descendants);
    }



    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category)
    {
        // Check if category has brands
        if ($category->brands()->exists()) {
            return redirect()->route('categories.index')
                ->with('error', 'Cannot delete category with associated brands.');
        }

        // Delete related files (physical files handled in File model's deleting hook)
        foreach ($category->files as $file) {
            $file->delete();
        }

        // Delete category and update positions of siblings
        $parentId = $category->parent_id;
        $category->delete();

        // Reorder remaining categories at the same level
        $this->reorderCategories($parentId);

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully.');
    }


    /**
     * Reorder categories at a given level after deletion.
     */
    private function reorderCategories(?int $parentId = null): void
    {
        $categories = Category::where('parent_id', $parentId)
            ->orderBy('position')
            ->orderBy('created_at')
            ->get();

        $position = 1;
        foreach ($categories as $category) {
            $category->position = $position;
            $category->save();
            $position++;
        }
    }

     public function topCategories(): JsonResponse
    {
        $categories = Category::with('files')
            ->whereNull('parent_id')
            ->orderBy('position', 'asc')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'image_url' => $category->files->first()?->file_url ?? null,
                ];
            });

        return response()->json($categories);
    }
}