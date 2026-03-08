<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the users with subscription info.
     */
    public function index()
    {
        $columns = [
            'name',
            'email',
            'status',
            'created_at',
        ];

        $globalSearch = getGlobalSearchFilter([...$columns]);

        $users = QueryBuilder::for(User::class)
            ->with('subscription') // load subscription relationship
            ->defaultSort('-created_at')
            ->allowedSorts($columns)
            ->allowedFilters([
                $globalSearch,
            ])
            ->paginate(getPaginate())
            ->withQueryString();

        // Map subscription status for each user
        $users->getCollection()->transform(function ($user) {
            $user->subscription_status = $this->getSubscriptionStatus($user);
            return $user;
        });

        return Inertia::render('users/Index', [
            'users' => $users,
        ]);
    }

    /**
     * Show a specific user with subscription info.
     */
    public function show(User $user)
    {
        $user->load('roles', 'subscription');
        $user->subscription_status = $this->getSubscriptionStatus($user);

        return response()->json($user);
    }

    /**
     * Determine subscription status for a user.
     */
    protected function getSubscriptionStatus(User $user): string
    {
        if ($user->subscription?->status === 'active') {
            return 'active';
        }

        if ($user->subscription?->status === 'pending') {
            return 'pending';
        }

        if ($user->subscription?->status === 'expired') {
            return 'expired';
        }

        return 'none';
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'status' => 'nullable|in:active,inactive',
            'role_ids' => 'nullable|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'status' => $request->status ?? 'active',
            ]);

            if ($request->has('role_ids')) {
                $user->roles()->sync($request->role_ids);
            }
        });

        return redirect()->back()->with('success', 'User created successfully.');
    }

    /**
     * Update an existing user.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'status' => 'nullable|in:active,inactive',
            'role_ids' => 'nullable|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        DB::transaction(function () use ($request, $user) {
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'status' => $request->status ?? $user->status,
                'password' => $request->password ? Hash::make($request->password) : $user->password,
            ]);

            if ($request->has('role_ids')) {
                $user->roles()->sync($request->role_ids);
            } else {
                $user->roles()->detach();
            }
        });

        return redirect()->back()->with('success', 'User updated successfully.');
    }

    /**
     * Delete a user.
     */
    public function destroy(User $user)
    {   
           \Log::info('Destroy method called', [
        'auth_id' => auth()->id(),
        'user_id' => $user->id,
        'is_authenticated' => auth()->check(),
        'can_delete' => auth()->user()?->can('delete', $user)
    ]);
        DB::transaction(function () use ($user) {

            // Delete profile
            $user->profile()?->delete();

            // Delete preferences
            $user->preferences()?->delete();

            // Delete notification settings
            $user->notificationSettings()->delete();

            // Delete notifications
            $user->notifications()->delete();

            // Delete subscription
            $user->subscription()?->delete();

            // Delete ratings
            $user->receivedRatings()->delete();
            $user->givenRatings()->delete();

            // Finally delete user
            $user->delete();
        });

        return redirect()->route('home');
    }
}
