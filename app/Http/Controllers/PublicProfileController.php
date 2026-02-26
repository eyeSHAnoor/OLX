<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Ad;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PublicProfileController extends Controller
{
    public function show(Request $request, $id)
    {
        // Find the user
        $user = User::with(['profile', 'files'])->findOrFail($id);

        // Get selected city from session or default to 'Pakistan'
        $selectedCity = strtolower(session('city', 'Pakistan'));

        // Get filter inputs
        $cityFilter = $request->input('city', 'all');
        $sortBy = $request->input('sort_by', 'newest');

        // Base query for user's ads
        $adsQuery = Ad::with(['images', 'category', 'brand'])
            ->where('user_id', $user->id)
            ->when($cityFilter !== 'all', function ($query) use ($cityFilter) {
                return $query->whereRaw('LOWER(city) = ?', [strtolower($cityFilter)]);
            });

        // Apply sorting
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
            case 'newest':
            default:
                $adsQuery->orderBy('created_at', 'desc');
                break;
        }

        // Paginate results (10 per page)
        $ads = $adsQuery->paginate(10)->withQueryString();

        // Get unique cities where user has ads
        $userCities = Ad::where('user_id', $user->id)
            ->select('city')
            ->distinct()
            ->orderBy('city')
            ->pluck('city')
            ->toArray();

        // Get user's avatar or create initials
        $avatar = null;
        $initial = strtoupper(substr($user->name, 0, 1));

        if ($user->files && $user->files->isNotEmpty()) {
            $avatar = $user->files[0]->file_url ?? null;
        }

        // Stats
        $totalAds = Ad::where('user_id', $user->id)->count();
        $totalViews = Ad::where('user_id', $user->id)->sum('views') ?? 0;
        $memberSince = $user->created_at->format('F Y');

        return Inertia::render('home/PublicProfile', [
            'profileUser' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar' => $avatar,
                'initial' => $initial,
                'bio' => $user->profile->bio ?? null,
                'location' => $user->profile->location ?? null,
                'member_since' => $memberSince,
                'total_ads' => $totalAds,
                'total_views' => $totalViews,
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