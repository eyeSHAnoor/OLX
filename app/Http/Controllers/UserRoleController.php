<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;
use Inertia\Inertia;
use App\Data\UserData;
use App\Data\RoleData;

class UserRoleController extends Controller
{
    // INDEX
    public function index(Request $request)
    {
        $columns = ['name', 'email', 'role', 'status'];
        $globalSearch = getGlobalSearchFilter($columns);

        // Build query with eager loading for roles and permissions
        $users = QueryBuilder::for(User::class)
            ->with(['roles', 'permissions']) // eager load roles and permissions
            ->when(auth()->user()->role !== 'super_admin', function ($query) {
                // Exclude super admin for non-super admin users
                $query->whereDoesntHave('roles', function ($q) {
                    $q->where('name', 'super_admin');
                });
            })
            ->defaultSort('-created_at')
            ->allowedSorts($columns)
            ->allowedFilters([...$columns, $globalSearch])
            ->paginate(getPaginate())
            ->withQueryString();

        // Fetch all roles with permissions
        $roles = Role::with('permissions')->get();

        // Basic stats
        $data = [
            'totalRoles' => $roles->count(),
            'totalUsers' => $users->total(),
        ];

        // Return to Inertia page
        return Inertia::render('settings/user/Index', [
            'users' => UserData::collect($users),
            'roles' => RoleData::collect($roles),
            'data' => $data,
        ]);
    }


    // STORE
    public function store(StoreUserRequest $request)
    {
        // dd($request);
        $request->save();

        return back()->with('success', __('tenant_users.controller.created'));
    }

    // UPDATE
    public function update(UpdateUserRequest $request, User $user)
    {
        $request->update($user);

        return back()->with('success', __('tenant_users.controller.updated'));
    }

    // DESTROY
    public function destroy(User $user)
    {
        DB::transaction(function () use ($user) {
            // Remove all roles first
            $user->syncRoles([]); // clears all roles assigned to the user

            // Delete the user
            $user->delete(); // hard delete, or forceDelete() if using SoftDeletes
        });

        return redirect()
            ->route('user-roles.index')
            ->with('success', __('tenant_users.controller.deleted'));
    }

}
