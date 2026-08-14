<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\AdView;
use App\Models\Ad;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PublicProfileController extends Controller
{
    public function show(Request $request, $id)
    {
        $user = User::with(['profile', 'files', 'receivedRatings',  'activeSubscription.plan.permissions'])->findOrFail($id);

        $viewer = auth()->user();
        $isOwner = $viewer && $viewer->id === $user->id;

        // Only expose profile if public OR owner
        $profile = null;

        if ($user->profile) {
            if ($user->profile->is_public || $isOwner) {
                $profile = $user->profile;
            }
        }

        $cityFilter = $request->input('city', 'all');
        $sortBy = $request->input('sort_by', 'newest');

        $adsQuery = Ad::with(['images', 'category', 'brand'])
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->when($cityFilter !== 'all', function ($query) use ($cityFilter) {
                return $query->whereRaw('LOWER(city) = ?', [strtolower($cityFilter)]);
            });

        switch ($sortBy) {
            case 'price_low':
                $adsQuery->orderBy('price', 'asc');
                break;
            case 'price_high':
                $adsQuery->orderBy('price', 'desc');
                break;
            case 'oldest':
                $adsQuery->orderBy('created_at', 'asc');
                break;
            default:
                $adsQuery->orderBy('created_at', 'desc');
        }

        $ads = $adsQuery->paginate(10)->withQueryString();

        $userCities = Ad::where('user_id', $user->id)
            ->select('city')
            ->distinct()
            ->orderBy('city')
            ->pluck('city')
            ->toArray();

           $planPermissions = [];
            if ($user->activeSubscription && $user->activeSubscription->plan) {
                $planPermissions = $user->activeSubscription->plan
                    ->permissions
                    ->pluck('name')
                    ->toArray();
            }

        $totalAdViews = AdView::whereIn('ad_id', Ad::where('user_id', $user->id)->pluck('id'))->count();


        return Inertia::render('home/PublicProfile', [
            'profileUser' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'rank' => $user->rank,
                'avatar' => optional($user->files->first())->file_url,
                'profile' => $profile,
                'ratings' => $user->receivedRatings,
                'orderStats' => $user->orderStats(),
                'plan_permissions' => $planPermissions, 
                'created_at' => $user->created_at,
                'total_ad_views' => $totalAdViews, 
            ],
            'ads' => $ads,
            'filters' => [
                'city' => $cityFilter,
                'sort_by' => $sortBy,
            ],
            'userCities' => $userCities,
            'selectedCity' => session('city', 'Pakistan'),
        ]);
    }
}