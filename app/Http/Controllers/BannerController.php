<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Data\CategoryData;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BannerController extends Controller
{
    /**
     * Display a listing of banners.
     */
    public function index()
    {
        $columns = [
            'title',
            'position',
            'status',
            'created_at',
        ];

        // Global search helper (search title)
        $globalSearch = getGlobalSearchFilter([...$columns]);

        $banners = QueryBuilder::for(Banner::class)
            ->with([
                'category:id,name',
            ])
            ->defaultSort('-created_at')
            ->allowedSorts($columns)
            ->allowedFilters([
                $globalSearch,
                AllowedFilter::exact('position'),
                AllowedFilter::exact('status'),
            ])
            ->paginate(getPaginate())
            ->withQueryString();

        return Inertia::render('banners/Index', [
            'banners'    => $banners,
            'categories' => CategoryData::collect(Category::all()),
        ]);
    }

    /**
     * Show a specific banner
     */
    public function show(Banner $banner)
    {
        $banner->load('category');
        return response()->json($banner);
    }

    /**
     * Store a new banner
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'              => 'required|string|max:255',
            'image_url'          => 'required|string|max:1024',
            'link'               => 'nullable|url|max:1024',
            'position'           => 'required|in:homepage,category,sidebar,floating',
            'target_category_id' => 'nullable|exists:categories,id',
            'start_date'         => 'nullable|date',
            'end_date'           => 'nullable|date|after_or_equal:start_date',
            'status'             => 'required|boolean',
        ]);

        DB::transaction(function () use ($request) {
            Banner::create($request->only([
                'title',
                'image_url',
                'link',
                'position',
                'target_category_id',
                'start_date',
                'end_date',
                'status',
            ]));
        });

        return redirect()->back()->with('success', 'Banner created successfully.');
    }

    /**
     * Update an existing banner
     */
    public function update(Request $request, Banner $banner)
    {
        $request->validate([
            'title'              => 'required|string|max:255',
            'image_url'          => 'required|string|max:1024',
            'link'               => 'nullable|url|max:1024',
            'position'           => 'required|in:homepage,category,sidebar,floating',
            'target_category_id' => 'nullable|exists:categories,id',
            'start_date'         => 'nullable|date',
            'end_date'           => 'nullable|date|after_or_equal:start_date',
            'status'             => 'required|boolean',
        ]);

        DB::transaction(function () use ($request, $banner) {
            $banner->update($request->only([
                'title',
                'image_url',
                'link',
                'position',
                'target_category_id',
                'start_date',
                'end_date',
                'status',
            ]));
        });

        return redirect()->back()->with('success', 'Banner updated successfully.');
    }

    /**
     * Delete a banner
     */
    public function destroy(Banner $banner)
    {
        DB::transaction(function () use ($banner) {
            $banner->delete();
        });

        return redirect()->back()->with('success', 'Banner deleted successfully.');
    }

    /**
     * Optional: Get banners by position and category (API)
     */
    public function getActive(Request $request)
    {
        $query = Banner::active();

        if ($request->filled('position')) {
            $query->where('position', $request->position);
        }

        if ($request->filled('category_id')) {
            $query->where('target_category_id', $request->category_id);
        }

        return response()->json($query->orderBy('created_at', 'desc')->get());
    }

    public function toggleStatus(Banner $banner)
    {
        $banner->update([
            'status' => !$banner->status
        ]);

        return redirect()->back()->with('success', 'Banner status updated successfully.');
    }
}
