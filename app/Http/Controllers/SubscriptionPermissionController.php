<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPermission;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Inertia\Inertia;

class SubscriptionPermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $columns = [
            'name',
            'label',
            'created_at',
        ];

        $globalSearch = getGlobalSearchFilter([...$columns]);

        $permissions = QueryBuilder::for(SubscriptionPermission::class)
            ->defaultSort('-created_at')
            ->allowedSorts($columns)
            ->allowedFilters([
                $globalSearch,
            ])
            ->paginate(getPaginate())
            ->withQueryString();

        return Inertia::render('subscription-permissions/Index', [
            'permissions' => $permissions,
        ]);
    }

    /**
     * Get a specific permission by ID
     */
    public function show(SubscriptionPermission $permission)
    {
        return response()->json($permission);
    }

    /**
     * Get all permissions for dropdown/select
     */
    public function getAll(Request $request)
    {
        $permissions = SubscriptionPermission::orderBy('name')->get();
        return response()->json($permissions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255|unique:subscription_permissions,name',
            'label' => 'required|string|max:255',
        ]);

        SubscriptionPermission::create([
            'name'  => $request->name,
            'label' => $request->label,
        ]);

        return redirect()->back()->with('success', 'Permission created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SubscriptionPermission $permission)
    {
        $request->validate([
            'name'  => 'required|string|max:255|unique:subscription_permissions,name,' . $permission->id,
            'label' => 'required|string|max:255',
        ]);

        $permission->update([
            'name'  => $request->name,
            'label' => $request->label,
        ]);

        return redirect()->back()->with('success', 'Permission updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubscriptionPermission $permission)
    {
        $permission->delete();
        return redirect()->back()->with('success', 'Permission deleted successfully.');
    }
}