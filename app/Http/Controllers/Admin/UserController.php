<?php

namespace App\Http\Controllers\Admin;

use App\Data\BranchData;
use App\Data\RoleData;
use App\Data\UserData;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;
use Spatie\QueryBuilder\QueryBuilder;

class UserController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', User::class);

        $columns = ['name', 'location', 'phone', 'email'];

        $globalSearch = getGlobalSearchFilter([...$columns]);

        $users = QueryBuilder::for(User::class)
            ->whereHas('roles', function ($q) {
                $q->where('name', '!=', 'super_admin');
            })
            ->with('roles', 'files')
            ->defaultSort('-created_at')
            ->allowedSorts($columns)
            ->allowedFilters([$globalSearch])
            ->paginate(getPaginate())
            ->withQueryString();

        return inertia('users/Index', [
            'users' => UserData::collect($users),
        ]);
    }

    public function create()
    {
        Gate::allows('create', User::class);

        $roles = Role::where('name', '!=', 'super_admin')->get();
        $branches = Branch::get();

        return inertia('users/RecordForm', [
            'roles' => RoleData::collect($roles),
            'branches' => BranchData::collect($branches),
            'users' => UserData::collect(User::get()),
        ]);
    }

    public function store(UserRequest $request)
    {
        $request->saveRecord();

        return redirect()->route('users.index')->with('success', __('controllers.user_created'));
    }

    public function edit(User $user)
    {
        Gate::allows('update', $user);

        $roles = Role::where('name', '!=', 'super_admin')->get();
        $branches = Branch::get();

        return inertia('users/RecordForm', [
            'user' => UserData::from($user->load('bankDetails', 'salaries', 'files', 'roles')),
            'roles' => RoleData::collect($roles),
            'branches' => BranchData::collect($branches),
            'users' => UserData::collect(User::get()),
        ]);
    }

    public function update(UserRequest $request, User $user)
    {
        $request->updateRecord($user);

        return redirect()->back()->with('success', __('controllers.user_updated'));
    }

    public function delete(User $user)
    {
        Gate::allows('delete', $user);

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User has been deleted');
    }
}
