<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Facades\Storage;
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

    public function store(Request $request)
    {
        $request->validate([
            'title'              => 'required|string|max:255',
            'image_url'          => 'nullable|string|max:1024',   // now nullable if file present
            'image'              => 'nullable|image|max:2048',     // new file field
            'link'               => 'nullable|url|max:1024',
            'position'           => 'required|in:homepage,category,sidebar,floating',
            'target_category_id' => 'nullable|exists:categories,id',
            'start_date'         => 'nullable|date',
            'end_date'           => 'nullable|date|after_or_equal:start_date',
            'status'             => 'required|boolean',
        ]);

        // Ensure at least one image source is provided
        if (!$request->filled('image_url') && !$request->hasFile('image')) {
            return back()->withErrors(['image_url' => 'Please provide either an image URL or upload an image.']);
        }

        $data = $request->only([
            'title', 'link', 'position', 'target_category_id', 'start_date', 'end_date', 'status'
        ]);

        // Handle image
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('banners', 'public');
            $data['image_url'] = Storage::url($path);
        } else {
            $data['image_url'] = $request->input('image_url');
        }

        DB::transaction(function () use ($data) {
            Banner::create($data);
        });

        return redirect()->back()->with('success', 'Banner created successfully.');
    }

    public function update(Request $request, Banner $banner)
    {
        $request->validate([
            'title'              => 'required|string|max:255',
            'image_url'          => 'nullable|string|max:1024',
            'image'              => 'nullable|image|max:2048',
            'link'               => 'nullable|url|max:1024',
            'position'           => 'required|in:homepage,category,sidebar,floating',
            'target_category_id' => 'nullable|exists:categories,id',
            'start_date'         => 'nullable|date',
            'end_date'           => 'nullable|date|after_or_equal:start_date',
            'status'             => 'required|boolean',
        ]);

        $data = $request->only([
            'title', 'link', 'position', 'target_category_id', 'start_date', 'end_date', 'status'
        ]);

        // If a new file is uploaded, use it; otherwise keep existing or fallback to URL
        if ($request->hasFile('image')) {
            // Optionally delete old file if it exists on disk
            if ($banner->image_url && \Storage::disk('public')->exists(str_replace('/storage/', '', $banner->image_url))) {
                \Storage::disk('public')->delete(str_replace('/storage/', '', $banner->image_url));
            }
            $path = $request->file('image')->store('banners', 'public');
            $data['image_url'] = Storage::url($path);
        } elseif ($request->filled('image_url')) {
            $data['image_url'] = $request->input('image_url');
        }

        DB::transaction(function () use ($banner, $data) {
            $banner->update($data);
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

    public function getBanner(Request $request)
    {
        $request->validate([
            'position' => 'required|string|in:homepage,category,sidebar,floating',
            'category_id' => 'nullable|integer|exists:categories,id',
        ]);

        $query = Banner::active()
            ->where('position', $request->position)
            ->orderBy('sort_order', 'asc');

        \Log::info('Fetching banners', [
            'position' => $request->position,
            'category_id' => $request->category_id,
        ]);

        // If category_id is provided, filter by category or show global banners
        if ($request->filled('category_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('target_category_id', $request->category_id)
                  ->orWhereNull('target_category_id');
            });
        }

        return response()->json($query->get());
    }

}
