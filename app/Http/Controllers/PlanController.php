<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\SubscriptionPermission;
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
            ->with('permissions') 
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

        $permissions = SubscriptionPermission::orderBy('name')->get();

        return Inertia::render('plans/Index', [
            'plans' => $plans,
            'permissions' => $permissions,
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
            'name'          => 'required|string|max:255|unique:plans,name',
            'price'         => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'description'   => 'nullable|string',
            'features'      => 'nullable|array',
            'is_popular'    => 'nullable|boolean',
            'sort_order'    => 'nullable|integer',
            'permission_ids'=> 'nullable|array',
            'permission_ids.*' => 'exists:subscription_permissions,id',
        ]);

        $plan = Plan::create([
            'name'        => $request->name,
            'price'       => $request->price,
            'discount'    => $request->discount ?? 0,
            'duration_days' => $request->duration_days,
            'description' => $request->description,
            'features'    => $request->features,
            'is_popular'  => $request->boolean('is_popular'),
            'sort_order'  => $request->sort_order ?? 0,
        ]);

        // Sync permissions if provided
        if ($request->has('permission_ids')) {
            $plan->permissions()->sync($request->permission_ids);
        }

        return redirect()->back()->with('success', 'Plan created successfully.');
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Plan $plan)
    {
        $request->validate([
            'name'          => 'required|string|max:255|unique:plans,name,' . $plan->id,
            'price'         => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'description'   => 'nullable|string',
            'features'      => 'nullable|array',
            'is_popular'    => 'nullable|boolean',
            'sort_order'    => 'nullable|integer',
            'permission_ids'=> 'nullable|array',
            'permission_ids.*' => 'exists:subscription_permissions,id',
        ]);

        $plan->update([
            'name'        => $request->name,
            'price'       => $request->price,
            'discount'    => $request->discount ?? 0,
            'duration_days' => $request->duration_days,
            'description' => $request->description,
            'features'    => $request->features,
            'is_popular'  => $request->boolean('is_popular'),
            'sort_order'  => $request->sort_order ?? $plan->sort_order,
        ]);

        // Sync permissions
        if ($request->has('permission_ids')) {
            $plan->permissions()->sync($request->permission_ids);
        } else {
            // If no permissions are selected, detach all
            $plan->permissions()->sync([]);
        }

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