<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Models\PageContent;
use App\Data\CategoryData;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AboutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('about/Index', [
        ]);
    }

    public function show($pageKey)
    {
        $page = PageContent::where('page_key', $pageKey)
        ->where('is_active', true)
        ->firstOrFail();
        
        if($page->page_key == 'about') {
            return Inertia::render('about/Index', [
                'page' => $page
            ]);
        }
         return Inertia::render('about/Contact', [
                'page' => $page
            ]);
    }

}
