<?php

namespace App\Http\Controllers;

use App\Data\CategoryData;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\QueryBuilder;
use Inertia\Inertia;

class CategoryController extends Controller
{
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