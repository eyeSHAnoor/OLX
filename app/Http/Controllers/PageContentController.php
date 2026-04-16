<?php

namespace App\Http\Controllers;

use App\Models\PageContent;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PageContentController extends Controller
{
    /**
     * Display a listing of page contents.
     */
    public function index()
    {
        $columns = [
            'page_key',
            'title',
            'is_active',
            'created_at',
        ];

        // Global search: page_key, title, subtitle
        $globalSearch = getGlobalSearchFilter([...$columns, 'subtitle']);

        $pageContents = QueryBuilder::for(PageContent::class)
            ->defaultSort('-created_at')
            ->allowedSorts($columns)
            ->allowedFilters([
                $globalSearch,
                AllowedFilter::exact('page_key'),
                AllowedFilter::exact('is_active'),
            ])
            ->paginate(getPaginate())
            ->withQueryString();

        return Inertia::render('pageContents/Index', [
            'pageContents' => $pageContents,
        ]);
    }

    public function create()
    {
        return Inertia::render('pageContents/RecordForm');
    }

    public function edit(PageContent $pageContent)
    {
        return Inertia::render('pageContents/RecordForm', [
            'pageContent' => $pageContent
        ]);
    }

    /**
     * Show a specific page content.
     */
    public function show(PageContent $pageContent)
    {
        return response()->json($pageContent);
    }

    /**
     * Store a new page content.
     */
    public function store(Request $request)
    {
        $request->validate([
            'page_key'  => 'required|string|max:255|unique:page_contents,page_key',
            'title'     => 'nullable|string|max:255',
            'subtitle'  => 'nullable|string|max:255',
            'content'   => 'nullable|array',          // from frontend as array, stored as JSON
            'is_active' => 'boolean',
        ]);

        DB::transaction(function () use ($request) {
            PageContent::create([
                'page_key'  => $request->page_key,
                'title'     => $request->title,
                'subtitle'  => $request->subtitle,
                'content'   => $request->content,     // cast to JSON automatically
                'is_active' => $request->boolean('is_active', true),
            ]);
        });

        return redirect()->back()->with('success', 'Page content created successfully.');
    }

    /**
     * Update an existing page content.
     */
    public function update(Request $request, PageContent $pageContent)
    {
        $request->validate([
            'page_key'  => 'required|string|max:255|unique:page_contents,page_key,' . $pageContent->id,
            'title'     => 'nullable|string|max:255',
            'subtitle'  => 'nullable|string|max:255',
            'content'   => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        DB::transaction(function () use ($request, $pageContent) {
            $pageContent->update([
                'page_key'  => $request->page_key,
                'title'     => $request->title,
                'subtitle'  => $request->subtitle,
                'content'   => $request->content,
                'is_active' => $request->boolean('is_active'),
            ]);
        });

        return redirect()->back()->with('success', 'Page content updated successfully.');
    }

    /**
     * Delete a page content.
     */
    public function destroy(PageContent $pageContent)
    {
        DB::transaction(function () use ($pageContent) {
            $pageContent->delete();
        });

        return redirect()->back()->with('success', 'Page content deleted successfully.');
    }

    /**
     * Toggle the active status of a page content.
     */
    public function toggleStatus(PageContent $pageContent)
    {
        $pageContent->update([
            'is_active' => !$pageContent->is_active,
        ]);

        return redirect()->back()->with('success', 'Page content status updated successfully.');
    }

    /**
     * Optional: Retrieve active content by page_key (API / frontend helper)
     */
    public function getByPageKey($pageKey)
    {
        $content = PageContent::where('page_key', $pageKey)
            ->where('is_active', true)
            ->first();

        if (!$content) {
            return response()->json(['message' => 'Content not found'], 404);
        }

        return response()->json($content);
    }
}