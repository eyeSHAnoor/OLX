<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\AdImage;
use App\Models\Brand;
use App\Models\Category;
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
            ->with(['brand', 'category', 'images'])
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

        return Inertia::render('ads/Index', [
            'ads' => $ads,
            'categories' => $categories,
            'brands' => [],
        ]);
    }

    public function create(){
        return Inertia::render('ads/RecordForm', [
            // 'ads' => $ads,
            'categories' => CategoryData::collect(Category::all()),
            'brands' => Brand::with('categories:id,name')->orderBy('name')->get(),
        ]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
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
        ]);

        return DB::transaction(function () use ($request) {

            // ----------------------
            // Create Ad (model handles keywords merge automatically)
            // ----------------------
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

            return redirect()->route('ads.index')->with('success', 'Ad created successfully.');
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
        ]);

        return Inertia::render('ads/RecordForm', [
            'ad' => $ad,
            'categories' => CategoryData::collect(Category::all()),
            'brands' => Brand::with('categories:id,name')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(Request $request, Ad $ad)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
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

            return redirect()
                ->route('ads.index')
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
}