<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\AdImage;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Feature;
use App\Data\CategoryData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
    public function store(Request $request)
    {
        // dd($request->all());
        // Log everything to see what's coming through
        
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
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
        ]);

        return DB::transaction(function () use ($request) {

            // ----------------------
            // Create Ad (model handles keywords merge automatically)
            // ----------------------

            // dd(auth()->id());
            $ad = Ad::create([
                'user_id' => auth()->id(),
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
                'ad_title' => $request->ad_title,
                'description' => $request->description,
                'price' => $request->price,
                'location' => $request->location,
                'city' => $request->city,
                'seller_name' => $request->seller_name,
                'seller_phone' => $request->seller_phone,
                'search_keywords' => $request->input('search_keywords', []), // pass frontend keywords
            ]);

            // ----------------------
            // Handle Images
            // ----------------------
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

            if ($request->filled('features')) {

                foreach ($request->features as $feature) {

                    $ad->features()->attach($feature['feature_id'], [
                        'feature_value_id' => $feature['feature_value_id'] ?? null,
                        'custom_value' => $feature['custom_value'] ?? null,
                    ]);
                }
            }

            return redirect()->back()->with('success', 'Ad created successfully.');
        });
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
            'features.values' 
        ]);

        return Inertia::render('ads/RecordForm', [
            'ad' => $ad,
            'categories' => CategoryData::collect(Category::all()),
            'brands' => Brand::with('categories:id,name')
                ->orderBy('name')
                ->get(),
            'features' => Feature::with('values')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Ad $ad)
    {
        // dd($request->all());
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
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

        ]);

        return DB::transaction(function () use ($request, $ad) {

            // ----------------
            // Update Ad Fields (pass frontend keywords)
            // ----------------
            $ad->update([
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
                'ad_title' => $request->ad_title,
                'description' => $request->description,
                'price' => $request->price,
                'location' => $request->location,
                'city' => $request->city,
                'seller_name' => $request->seller_name,
                'seller_phone' => $request->seller_phone,
                'search_keywords' => $request->input('search_keywords', []), // model merges automatically
            ]);

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

            return redirect()
                ->back()
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

        return redirect()->route('ads.index')->with('success', 'Ad deleted successfully.');
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

        return Inertia::render('home/AdDetail', [
            'ad' => $ad,
            'sellerRating' => [
                'average' => round($seller->avg_rating, 1),
                'count' => $seller->rating_count
            ],
            'isFavorited' => $isFavorited, // Add this to the props
        ]);
    }
}