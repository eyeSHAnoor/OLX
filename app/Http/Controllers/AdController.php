<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\AdImage;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AdController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Ad::with(['brand', 'category', 'images'])
            ->withCount('images')
            ->latest();

        // Search filter
        if ($request->has('filter.global') && $request->input('filter.global')) {
            $search = $request->input('filter.global');
            $query->where(function ($q) use ($search) {
                $q->where('ad_title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('seller_name', 'like', "%{$search}%")
                  ->orWhereHas('brand', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('category', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by category
        if ($request->has('filter.category_id') && $request->input('filter.category_id')) {
            $query->where('category_id', $request->input('filter.category_id'));
        }

        // Filter by brand
        if ($request->has('filter.brand_id') && $request->input('filter.brand_id')) {
            $query->where('brand_id', $request->input('filter.brand_id'));
        }

        // Price range filter
        if ($request->has('filter.min_price') && $request->input('filter.min_price')) {
            $query->where('price', '>=', $request->input('filter.min_price'));
        }
        
        if ($request->has('filter.max_price') && $request->input('filter.max_price')) {
            $query->where('price', '<=', $request->input('filter.max_price'));
        }

        // Pagination
        $perPage = $request->input('perPage', 12);
        $ads = $query->paginate($perPage);

        // Get filters data
        $categories = Category::orderBy('name')->get();
        $brands = Brand::with('categories:id')->orderBy('name')->get();

        return Inertia::render('ads/Index', [
            'ads' => $ads,
            'categories' => $categories,
            'brands' => $brands,
            'filters' => $request->only([
                'filter.global', 
                'filter.category_id', 
                'filter.brand_id',
                'filter.min_price',
                'filter.max_price'
            ]),
            'perPage' => $perPage,
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
            'seller_name' => 'required|string|max:255',
            'seller_phone' => 'required|string|max:20',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        return DB::transaction(function () use ($request) {
            $ad = Ad::create([
                'user_id' => auth()->id(),
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
                'ad_title' => $request->ad_title,
                'description' => $request->description,
                'price' => $request->price,
                'location' => $request->location,
                'seller_name' => $request->seller_name,
                'seller_phone' => $request->seller_phone,
            ]);

            // Handle image uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $folder = 'ads/images/' . $ad->id; // store images per ad
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

    public function update(Request $request, Ad $ad)
    {
        // dd($request->all());
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'ad_title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'location' => 'required|string|max:255',
            'seller_name' => 'required|string|max:255',
            'seller_phone' => 'required|string|max:20',

            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',

            'remove_images' => 'nullable|array',
            'remove_images.*' => 'exists:ad_images,id',
        ]);

        return DB::transaction(function () use ($request, $ad) {

            // ----------------
            // Update Ad Fields
            // ----------------
            $ad->update([
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
                'ad_title' => $request->ad_title,
                'description' => $request->description,
                'price' => $request->price,
                'location' => $request->location,
                'seller_name' => $request->seller_name,
                'seller_phone' => $request->seller_phone,
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

        return response()->json(['success' => true]);
    }
}