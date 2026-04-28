<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $columns = [
            'name',
            'price',
            'duration_days',
            'created_at',
        ];

        // Global search helper
        $globalSearch = getGlobalSearchFilter([...$columns]);

        $plans = QueryBuilder::for(Plan::class)
            ->defaultSort('-created_at')
            ->allowedSorts($columns)
            ->allowedFilters([
                $globalSearch,
                AllowedFilter::exact('duration_days'),
                AllowedFilter::callback('price_range', function ($query, $value) {
                    if (isset($value['min'])) {
                        $query->where('price', '>=', $value['min']);
                    }
                    if (isset($value['max'])) {
                        $query->where('price', '<=', $value['max']);
                    }
                }),
            ])
            ->paginate(getPaginate())   
            ->withQueryString();

        return Inertia::render('plans/Index', [
            'plans' => $plans,
        ]);
    }

    /**
     * Get a specific plan by ID
     */
    public function show(Plan $plan)
    {
        return response()->json($plan);
    }

    /**
     * Get all plans for dropdown/select
     */
    public function getAll(Request $request)
    {
        $plans = Plan::orderBy('price')->get();
        return response()->json($plans);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:plans,name',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'duration_days' => 'required|integer|min:1',

            'description' => 'nullable|string',
            'features' => 'nullable|array',
            'is_popular' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        Plan::create([
            'name' => $request->name,
            'price' => $request->price,
            'discount' => $request->discount ?? 0,
            'duration_days' => $request->duration_days,

            'description' => $request->description,

            // Vue usually sends array already
            'features' => $request->features,

            'is_popular' => $request->boolean('is_popular'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->back()->with('success', 'Plan created successfully.');
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Plan $plan)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:plans,name,' . $plan->id,
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'duration_days' => 'required|integer|min:1',

            'description' => 'nullable|string',
            'features' => 'nullable|array',
            'is_popular' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $plan->update([
            'name' => $request->name,
            'price' => $request->price,
            'discount' => $request->discount ?? 0,
            'duration_days' => $request->duration_days,

            'description' => $request->description,
            'features' => $request->features,

            'is_popular' => $request->boolean('is_popular'),
            'sort_order' => $request->sort_order ?? $plan->sort_order,
        ]);

        return redirect()->back()->with('success', 'Plan updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Plan $plan)
    {
        $plan->delete();
        return redirect()->back()->with('success', 'Plan deleted successfully.');
    }
}