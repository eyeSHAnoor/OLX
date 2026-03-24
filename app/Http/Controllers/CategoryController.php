<?php

namespace App\Http\Controllers;

use App\Data\CategoryData;
use App\Models\Category;
use App\Models\Ad;
use App\Models\Brand;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\QueryBuilder;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function show(Request $request, $slug = null)
    {
        $selectedCity = strtolower(session('city', 'Pakistan'));

        $category = null;
        if ($slug) {
            $category = Category::where('slug', $slug)
                ->with(['childrenRecursive', 'files'])
                ->firstOrFail();
        }

        // Filters
        $searchTerm = $request->input('filter.global');
        $categoryFilter = $request->input('filter.category', $category?->id);
        $brandFilter = $request->input('filter.brand');
        $minPrice = $request->input('filter.min_price');
        $maxPrice = $request->input('filter.max_price');
        $sort = $request->input('sort', 'newest');
        $city = $request->input('filter.city', $selectedCity);

        // Categories (sidebar)
        $categories = Category::whereNull('parent_id')
            ->with(['childrenRecursive', 'files'])
            ->orderBy('position')
            ->get();

        // --------------------------
        // BASE QUERY (REUSABLE)
        // --------------------------
        $baseQuery = Ad::query()
            ->with(['images', 'brand', 'category'])
            ->when($city !== 'pakistan', fn($q) =>
                $q->whereRaw('LOWER(city) = ?', [strtolower($city)])
            );

        // Search
        if ($searchTerm) {
            $search = strtolower($searchTerm);
            $baseQuery->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(ad_title) LIKE ?', ["%{$search}%"])
                ->orWhereRaw('LOWER(description) LIKE ?', ["%{$search}%"])
                ->orWhereHas('brand', fn($b) =>
                    $b->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                );
            });
        }

        // Filters
        if ($brandFilter) {
            $baseQuery->where('brand_id', $brandFilter);
        }

        if ($minPrice) {
            $baseQuery->where('price', '>=', $minPrice);
        }

        if ($maxPrice) {
            $baseQuery->where('price', '<=', $maxPrice);
        }

        // Sorting
        match ($sort) {
            'price_low' => $baseQuery->orderBy('price', 'asc'),
            'price_high' => $baseQuery->orderBy('price', 'desc'),
            default => $baseQuery->latest(),
        };

        // --------------------------
        // CATEGORY SELECTED
        // --------------------------
        if ($categoryFilter) {
            $selectedCategory = Category::with(['childrenRecursive', 'files'])
                ->find($categoryFilter);

            if ($selectedCategory) {
                $categoryIds = $selectedCategory->getLeafCategoriesEfficient()
                    ->pluck('id')
                    ->push($selectedCategory->id)
                    ->unique();

                $adQuery = (clone $baseQuery)
                    ->whereIn('category_id', $categoryIds);

                // PAGINATION
                $ads = $adQuery->paginate(10)->withQueryString();

                $selectedCategory->ads = $ads;
                $selectedCategory->ads_count = $ads->total();

                // Fix DISTINCT error by removing ORDER BY from the query before pluck
                $brandIds = (clone $adQuery)->reorder()->pluck('brand_id')->unique();
                $availableBrands = Brand::whereIn('id', $brandIds)->get();

                $priceRange = [
                    'min' => (clone $adQuery)->min('price'),
                    'max' => (clone $adQuery)->max('price'),
                ];

                return Inertia::render('home/Category', [
                    'category' => $selectedCategory,
                    'categories' => $categories,
                    'brands' => $availableBrands,
                    'banners' => [],
                    'topBanner' => null,
                    'allBrands' => Brand::select('id', 'name')->get(),
                    'filters' => [
                        'filter' => [
                            'global' => $searchTerm,
                            'category' => $categoryFilter,
                            'brand' => $brandFilter,
                            'min_price' => $minPrice,
                            'max_price' => $maxPrice,
                            'city' => $city,
                        ],
                        'sort' => $sort,
                    ],
                    'priceRange' => $priceRange,
                ]);
            }
        }

        // --------------------------
        // NO CATEGORY
        // --------------------------
        $categories->each(function ($category) use ($baseQuery) {
            $categoryIds = $category->getLeafCategoriesEfficient()
                ->pluck('id')
                ->push($category->id)
                ->unique();

            $query = (clone $baseQuery)
                ->whereIn('category_id', $categoryIds);

            $ads = $query->limit(2)->get();

            $category->ads = $ads;
            $category->ads_count = $query->count();
        });

        return Inertia::render('home/Category', [
            'categories' => $categories,
            'brands' => Brand::select('id', 'name')->get(),
            'filters' => [
                'filter' => [
                    'global' => $searchTerm,
                    'category' => $categoryFilter,
                    'brand' => $brandFilter,
                    'min_price' => $minPrice,
                    'max_price' => $maxPrice,
                    'city' => $city,
                ],
                'sort' => $sort,
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
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        // dd($request->hasFile('image'));
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories')->where(function ($query) use ($request) {
                return $query->where('parent_id', $request->parent_id);
            })],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'position' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'], // new image validation
        ]);

        // Generate slug from name
        $slug = $this->generateUniqueSlug($validated['name'], $validated['parent_id'] ?? null);

        // Create category with slug
        $category = Category::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'parent_id' => $validated['parent_id'] ?? null,
            'position' => $validated['position'] ?? $this->getNextPosition($validated['parent_id'] ?? null),
        ]);

        // Handle category image
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('categories', 'public'); // stores in storage/app/public/categories

            $category->files()->create([
                'file_location' => $path,
                'file_name' => $request->file('image')->getClientOriginalName(),
                'collection' => 'category_images',
            ]);
        }

        $category->load('parent', 'children', 'files');

        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully.');
    }


    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('categories')
                    ->where(function ($query) use ($request, $category) {
                        return $query->where('parent_id', $request->parent_id);
                    })
                    ->ignore($category->id)
            ],
            'parent_id' => [
                'nullable', 'integer', 'exists:categories,id',
                function ($attribute, $value, $fail) use ($category) {
                    // Prevent category from being its own parent
                    if ($value == $category->id) {
                        $fail('A category cannot be its own parent.');
                    }

                    // Prevent circular reference (category cannot be parent of its descendant)
                    if ($this->isDescendant($value, $category)) {
                        $fail('Cannot assign a descendant category as parent.');
                    }
                }
            ],
            'position' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'], // handle image update
        ]);

        // Regenerate slug if name changed
        if ($validated['name'] !== $category->name) {
            $validated['slug'] = $this->generateUniqueSlug($validated['name'], $validated['parent_id'] ?? null, $category->id);
        }

        // Update position if parent changed
        if ($validated['parent_id'] != $category->parent_id) {
            $validated['position'] = $validated['position'] ?? $this->getNextPosition($validated['parent_id'] ?? null);
        }

        // Update category
        $category->update($validated);

        // Handle image update
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('categories', 'public');

            // Delete old image(s) if exists
            foreach ($category->files as $file) {
                if (\Storage::disk('public')->exists($file->file_location)) {
                    \Storage::disk('public')->delete($file->file_location);
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

        $category->load('parent', 'children', 'files');

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully.');
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
     * Generate a unique slug for the category.
     */
    private function generateUniqueSlug(string $name, ?int $parentId = null, ?int $exceptId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (Category::where('slug', $slug)
            ->when($parentId, function ($query) use ($parentId) {
                return $query->where('parent_id', $parentId);
            }, function ($query) {
                return $query->whereNull('parent_id');
            })
            ->when($exceptId, function ($query) use ($exceptId) {
                return $query->where('id', '!=', $exceptId);
            })
            ->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Get the next position for a new category at a given level.
     */
    private function getNextPosition(?int $parentId = null): int
    {
        $lastPosition = Category::where('parent_id', $parentId)
            ->max('position');

        return ($lastPosition ?? 0) + 1;
    }

    /**
     * Check if a category is a descendant of another category.
     */
    private function isDescendant(?int $parentId, Category $potentialDescendant): bool
    {
        if (!$parentId) {
            return false;
        }

        $parent = Category::find($parentId);
        if (!$parent) {
            return false;
        }

        $checkCategory = $parent;
        while ($checkCategory) {
            if ($checkCategory->id === $potentialDescendant->id) {
                return true;
            }
            $checkCategory = $checkCategory->parent;
        }

        return false;
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