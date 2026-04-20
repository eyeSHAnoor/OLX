<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\AdImage;
use App\Models\AdView;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CategoryAttribute;
use App\Models\AdAttributeValue;
use App\Models\Feature;
use App\Data\CategoryData;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\LaravelData\DataCollection;
use Spatie\QueryBuilder\AllowedFilter;
use Inertia\Inertia;

class AdController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        // Columns allowed to sort
        $columns = ['ad_title', 'price','location','seller_name', 'brand', 'city', 'created_at', 'search_keywords'];

        // Global search filter helper
        $globalSearch = getGlobalSearchFilter([...$columns]);
        $ads = QueryBuilder::for(Ad::class)
            ->with(['brand', 'category', 'images', 'features.values'])
            ->withCount('images')
            ->defaultSort('-created_at')
            ->allowedSorts($columns)
            ->allowedFilters([
                $globalSearch,
                'brand_id',
                'category_id',
                AllowedFilter::callback('min_price', fn($query, $value) => $query->where('price', '>=', $value)),
                AllowedFilter::callback('max_price', fn($query, $value) => $query->where('price', '<=', $value)),
            ])
            ->paginate(getPaginate()) // your helper for pagination
            ->withQueryString();

        // Categories for filter dropdown
        $categories = CategoryData::collect(Category::all());

        // dd($ads);

        return Inertia::render('ads/Index', [
            'ads' => $ads,
            'categories' => $categories,
            'brands' => [],
        ]);
    }

    public function create(){
        // dd('hello');
        return Inertia::render('ads/RecordForm', [
            // 'ads' => $ads,
            'categories' => CategoryData::collect(Category::all()),
            'brands' => Brand::with('categories:id,name')->orderBy('name')->get(),
            'features' => Feature::with('values')->orderBy('name')->get(),
        ]);
    }
    /**
     * Store a newly created resource in storage.
     */
   // Store a new ad
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'model_id' => 'nullable|exists:brand_models,id',
            'ad_title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'location' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'seller_name' => 'required|string|max:255',
            'seller_phone' => 'required|string|max:20',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'search_keywords' => 'nullable|array',
            'search_keywords.*' => 'string|max:50',
            'features' => 'nullable|array',
            'features.*.feature_id' => 'nullable|exists:features,id',
            'features.*.feature_value_id' => 'nullable|exists:feature_values,id',
            'features.*.custom_value' => 'nullable|string|max:255',
            'attributes' => 'nullable|array',
            'attributes.*' => 'nullable',
        ]);

        return DB::transaction(function () use ($request) {

            // ----------------------
            // Create Ad
            // ----------------------
            $ad = Ad::create([
                'user_id' => auth()->id(),
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
                'brand_model_id' => $request->model_id,
                'ad_title' => $request->ad_title,
                'description' => $request->description,
                'price' => $request->price,
                'location' => $request->location,
                'city' => $request->city,
                'seller_name' => $request->seller_name,
                'seller_phone' => $request->seller_phone,
                'search_keywords' => $request->input('search_keywords', []),
            ]);
            // dd($request->attributes);
            // ----------------------
            // Store Attributes
            // ----------------------
            $attributes = $request->input('attributes', []);

            if (!empty($attributes) && is_array($attributes)) {

                // Get category attributes
                $categoryAttributes = CategoryAttribute::where('category_id', $request->category_id)
                    ->pluck('id')
                    ->map(fn($id) => (int)$id)
                    ->toArray();

                foreach ($attributes as $attributeId => $value) {

                    if ($value === null || $value === '') {
                        continue;
                    }

                    AdAttributeValue::create([
                        'ad_id' => $ad->id,
                        'category_attribute_id' => $attributeId,
                        'value' => $value,
                    ]);

                    if (!in_array((int)$attributeId, $categoryAttributes)) {
                        \Log::warning("Ad {$ad->id}: Attribute {$attributeId} does not belong to category {$request->category_id}");
                    }
                }
            }

            // ----------------------
            // Store Images
            // ----------------------
            if ($request->hasFile('images')) {
                $folder = 'ads/images/' . $ad->id;

                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store($folder, 'public');

                    AdImage::create([
                        'ad_id' => $ad->id,
                        'path' => $path,
                        'is_primary' => $index === 0,
                    ]);
                }
            }

            // ----------------------
            // Attach Features
            // ----------------------
            if ($request->filled('features')) {
                foreach ($request->features as $feature) {
                    if (!empty($feature['feature_id'])) {
                        $ad->features()->attach($feature['feature_id'], [
                            'feature_value_id' => $feature['feature_value_id'] ?? null,
                            'custom_value' => $feature['custom_value'] ?? null,
                        ]);
                    }
                }
            }

            // return redirect()->back()->with('success', 'Ad created successfully.');
            $user = auth()->user();

            if ($user->hasRole('super_admin')) {
                return redirect()->route('ads.index')
                    ->with('success', 'Ad created successfully.');
            }

            return redirect()->route('user.profile', $user->id)
                ->with('success', 'Ad created successfully.');
        });
    }

    public function getAttributesByCategory($categoryId)
    {
        $category = Category::with([
            'attributes' => function ($query) {
                $query->orderBy('position')->with('options');
            },
            'parent.attributes' => function ($query) {
                $query->orderBy('position')->with('options');
            }
        ])->findOrFail($categoryId);

        // Get all attributes from parent categories (inheritance)
        $attributes = collect();
        
        // Add current category attributes
        if ($category->attributes && $category->attributes->count() > 0) {
            $attributes = $attributes->merge($category->attributes);
        }
        
        // Add parent category attributes (recursively)
        $parent = $category->parent;
        while ($parent) {
            // Load parent attributes with options if not already loaded
            if (!$parent->relationLoaded('attributes')) {
                $parent->load(['attributes' => function ($query) {
                    $query->orderBy('position')->with('options');
                }]);
            }
            
            if ($parent->attributes && $parent->attributes->count() > 0) {
                $attributes = $attributes->merge($parent->attributes);
            }
            $parent = $parent->parent;
        }
        
        // Remove duplicates and ensure options are loaded
        $uniqueAttributes = $attributes->unique('id')->values();
        
        // Make sure each attribute has its options
        $uniqueAttributes->each(function ($attribute) {
            if (!$attribute->relationLoaded('options')) {
                $attribute->load('options');
            }
        });
        
        return response()->json([
            'attributes' => $uniqueAttributes,
            'children_categories' => $category->children
        ]);
    }

    public function getModelsByBrand($brandId)
    {
        $brand = Brand::with('models')->findOrFail($brandId);
        
        return response()->json([
            'models' => $brand->models
        ]);
    }

    /**
     * Update the specified resource in storage.
     */

    public function edit(Ad $ad)
    {
        // Eager load related category, brand, and images
        $ad->load([
            'category',
            'brand',
            'images',
            'model',
            'features.values' ,
        ]);
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

        return Inertia::render('ads/RecordForm', [
            'ad' => $ad,
            'attributes' =>$attributes,
            'categories' => CategoryData::collect(Category::all()),
            'brands' => Brand::with('categories:id,name')
                ->orderBy('name')
                ->get(),
            'features' => Feature::with('values')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Ad $ad)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'model_id' => 'nullable|exists:brand_models,id',

            'ad_title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',

            'location' => 'required|string|max:255',
            'city' => 'required|string|max:255',

            'seller_name' => 'required|string|max:255',
            'seller_phone' => 'required|string|max:20',

            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',

            'remove_images' => 'nullable|array',
            'remove_images.*' => 'exists:ad_images,id',

            'search_keywords' => 'nullable|array',
            'search_keywords.*' => 'string|max:50',

            'features' => 'nullable|array',
            'features.*.feature_id' => 'required|exists:features,id',
            'features.*.feature_value_id' => 'nullable|exists:feature_values,id',
            'features.*.custom_value' => 'nullable|string|max:255',

            'attributes' => 'nullable|array',
            'attributes.*' => 'nullable',
        ]);

        return DB::transaction(function () use ($request, $ad) {

            // ----------------
            // Update Ad Fields
            // ----------------
            $ad->update([
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
                'brand_model_id' => $request->model_id,
                'ad_title' => $request->ad_title,
                'description' => $request->description,
                'price' => $request->price,
                'location' => $request->location,
                'city' => $request->city,
                'seller_name' => $request->seller_name,
                'seller_phone' => $request->seller_phone,
                'search_keywords' => $request->input('search_keywords', []),
            ]);

            // ----------------
            // Update Attributes
            // ----------------
            $attributes = $request->input('attributes', []);

            // delete old values
            $ad->attributes()->delete();

            if (!empty($attributes)) {

                $categoryAttributes = CategoryAttribute::where('category_id', $request->category_id)
                    ->pluck('id')
                    ->map(fn($id) => (int) $id)
                    ->toArray();

                foreach ($attributes as $attributeId => $value) {

                    if ($value === null || $value === '') {
                        continue;
                    }

                    AdAttributeValue::create([
                        'ad_id' => $ad->id,
                        'category_attribute_id' => $attributeId,
                        'value' => $value,
                    ]);

                    if (!in_array((int)$attributeId, $categoryAttributes)) {
                        \Log::warning("Ad {$ad->id}: Attribute {$attributeId} does not belong to category {$request->category_id}");
                    }
                }
            }

            // ----------------
            // Remove Images
            // ----------------
            if ($request->filled('remove_images')) {
                foreach ($request->remove_images as $imageId) {

                    $image = AdImage::where('ad_id', $ad->id)
                        ->where('id', $imageId)
                        ->first();

                    if ($image) {
                        Storage::disk('public')->delete($image->path);
                        $image->delete();
                    }
                }
            }

            // ----------------
            // Add New Images
            // ----------------
            if ($request->hasFile('images')) {

                $folder = 'ads/images/' . $ad->id;

                foreach ($request->file('images') as $image) {

                    $path = $image->store($folder, 'public');

                    AdImage::create([
                        'ad_id' => $ad->id,
                        'path' => $path,
                    ]);
                }
            }

            // ----------------
            // Sync Features
            // ----------------
            if ($request->has('features')) {

                $sync = collect($request->features)->mapWithKeys(fn ($f) => [
                    $f['feature_id'] => [
                        'feature_value_id' => $f['feature_value_id'] ?? null,
                        'custom_value' => $f['custom_value'] ?? null,
                    ]
                ])->toArray();

                $ad->features()->sync($sync);

            } else {
                $ad->features()->detach();
            }

            $user = auth()->user();

            if ($user->hasRole('super_admin')) {
                return redirect()->route('ads.index')
                    ->with('success', 'Ad created successfully.');
            }

            return redirect()->route('user.profile', $user->id)
                ->with('success', 'Ad updated successfully.');
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ad $ad)
    {
        DB::transaction(function () use ($ad) {
            // Delete all associated images
            foreach ($ad->images as $image) {
                Storage::disk('public')->delete($image->path);
                $image->delete();
            }
            
            $ad->delete();
        });

        return redirect()->back()->with('success', 'Ad deleted successfully.');
    }

    /**
     * Set primary image for ad
     */
    public function setPrimaryImage(Request $request, Ad $ad)
    {
        $request->validate([
            'image_id' => 'required|exists:ad_images,id,ad_id,' . $ad->id,
        ]);

        DB::transaction(function () use ($request, $ad) {
            // Remove primary from all images
            $ad->images()->update(['is_primary' => false]);
            
            // Set selected image as primary
            $ad->images()->where('id', $request->image_id)->update(['is_primary' => true]);
        });

        return redirect()->back()->with('Success','Image is set as Primary or thumbnail');
    }

    public function show(Ad $ad)
    {
        $ad->load([
            'user:id,name,email',
            'images',
            'category:id,name',
            'brand:id,name',
            'ratings.rater',
            'features' => function ($q) {
                $q->withPivot(['feature_value_id', 'custom_value']);
            }
        ]);

            AdView::firstOrCreate([
            'ad_id' => $ad->id,
            'session_id' => session()->getId(),
        ], [
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
        
        
        // Transform features to send selected value only
        $ad->features->transform(function ($feature) {
            $selectedValue = $feature->values
                ->where('id', $feature->pivot->feature_value_id)
                ->first();

            return [
                'id' => $feature->id,
                'name' => $feature->name,
                'value' => $selectedValue?->value ?? $feature->pivot->custom_value
            ];
        });

        // Log::info($ad);
        
        $seller = $ad->user;
        $seller->avg_rating = $seller->receivedRatings()->avg('rating');
        $seller->rating_count = $seller->receivedRatings()->count();

        // Check if the current authenticated user has favorited this ad
        $isFavorited = false;
        if (auth()->check()) {
            $isFavorited = $ad->favoritedBy()
                ->where('user_id', auth()->id())
                ->exists();
        }

        $hasOrdered = false;

        if (auth()->check()) {
            $hasOrdered = Order::where('buyer_id', auth()->id())
                ->where('ad_id', $ad->id)
                ->where('status', '!=', 'rejected')
                ->exists();
        }

        return Inertia::render('home/AdDetail', [
            'ad' => $ad,
            'sellerRating' => [
                'average' => round($seller->avg_rating, 1),
                'count' => $seller->rating_count
            ],
            'isFavorited' => $isFavorited, // Add this to the props
            'hasOrdered' => $hasOrdered
        ]);
    }
}