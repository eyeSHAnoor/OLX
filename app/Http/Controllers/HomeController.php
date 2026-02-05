<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Brand;
use App\Models\Ad;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        // Fetch all top-level categories with children recursively + their files
        $categories = Category::with([
            'childrenRecursive.files', // children and nested children files
            'files',                   // category main files
        ])->whereNull('parent_id')
          ->orderBy('position')
          ->get();

        // Fetch all brands with their categories and files
        $brands = Brand::with([
            'categories.files',
        ])->get();

        // Fetch latest ads with their category, brand, and images
        $ads = Ad::with([
            'category.files',  // category files
            'brand',           // brand details
            'images'           // ad images
        ])->latest()->get();

        // Return to frontend
        return Inertia::render('Home', [
            'categories' => $categories,
            'brands' => $brands,
            'ads' => $ads,
        ]);
    }
}
