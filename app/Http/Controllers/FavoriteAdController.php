<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FavoriteAdController extends Controller
{
    /**
     * Display the user's favorite ads.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Start building the query for favorite ads
        $query = $user->favoriteAds()
            ->with(['images', 'category', 'brand', 'user']);
        
        // Apply search filter - using ad_title instead of title
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ad_title', 'like', "%{$search}%")  // Changed from 'title' to 'ad_title'
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('location', 'like', "%{$search}%");
            });
        }
        
        // Apply category filter
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        
        // Apply brand filter
        if ($request->filled('brand')) {
            $query->where('brand_id', $request->brand);
        }
        
        // Apply price range filters
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        
        // Get filtered favorite ads
        $favoriteAds = $query->latest()
            ->paginate(12)
            ->withQueryString();

        // Get categories and brands for filtering
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        $brands = Brand::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('home/FavoriteAds', [
            'favoriteAds' => $favoriteAds,
            'categories' => $categories,
            'brands' => $brands,
            'filters' => $request->only(['search', 'category', 'brand', 'min_price', 'max_price'])
        ]);
    }

    /**
     * Toggle favorite status for an ad.
     */
    public function toggle(Ad $ad)
    {
        $user = auth()->user();
        
        if ($user->favoriteAds()->where('ad_id', $ad->id)->exists()) {
            $user->favoriteAds()->detach($ad->id);
            $message = 'Ad removed from favorites';
            $isFavorited = false;
        } else {
            $user->favoriteAds()->attach($ad->id);
            $message = 'Ad added to favorites';
            $isFavorited = true;
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'is_favorited' => $isFavorited
        ]);
    }

    /**
     * API endpoint for filtered favorite ads (if needed for AJAX).
     */
    public function apiIndex(Request $request)
    {
        $user = auth()->user();
        
        $query = $user->favoriteAds()
            ->with(['images', 'category', 'brand', 'user']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ad_title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('brand')) {
            $query->where('brand_id', $request->brand);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $favoriteAds = $query->latest()->paginate(12)->withQueryString();

        return response()->json($favoriteAds);
    }

    /**
     * Remove a specific ad from favorites.
     */
    public function destroy(Ad $ad)
    {
        $user = auth()->user();
        
        $user->favoriteAds()->detach($ad->id);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Ad removed from favorites'
            ]);
        }

        return redirect()->back()->with('success', 'Ad removed from favorites');
    }

    /**
     * Clear all favorite ads.
     */
    public function clearAll()
    {
        $user = auth()->user();
        
        $user->favoriteAds()->detach();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'All favorites cleared'
            ]);
        }

        return redirect()->back()->with('success', 'All favorites cleared');
    }
}