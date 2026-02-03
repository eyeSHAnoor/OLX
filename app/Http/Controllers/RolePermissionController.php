<?php

namespace App\Http\Controllers;

use App\Data\PermissionData;
use App\Data\RoleData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRolePermissionRequest;
use App\Http\Requests\UpdateUserRolePermissionRequest;
use App\Models\Permission;
use App\Models\Role;
use Spatie\QueryBuilder\QueryBuilder;

class RolePermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //        $this->authorize('viewAny', UserLogistic::class);

        $columns = [
            'name',
            'guard_name',
            'tenant_id',
        ];

        $globalSearch = getGlobalSearchFilter([...$columns]);

        $roles = QueryBuilder::for(Role::class)
            ->with(['permissions'])
            ->withCount('users')
            ->defaultSort('-created_at')
            ->allowedSorts($columns)
            ->allowedFilters([$globalSearch])
            ->get();

        return inertia('settings/roles_permissions/Index', [
            'roles' => RoleData::collect($roles),
            'permissions' => PermissionData::collect(Permission::get()),
        ]);
    }

    /**
     * UserLogistic a newly created resource in storage.
     */
    public function store(StoreUserRolePermissionRequest $request)
    {
        // dd($request);
        $request->save();

        return back()->with('success', __("roles_permissions.controller.created"));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRolePermissionRequest $request, Role $role)
    {
        // dd($request);
        $request->update($role);

        return back()->with('success', __('roles_permissions.controller.updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $role->delete();

        return back()->with('success', __('roles_permissions.controller.deleted'));
    }

}
