<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use Illuminate\Http\Request;

class AdFavoriteController extends Controller
{
    public function toggle(Ad $ad)
    {
        $user = auth()->user();

        if ($user->favoriteAds()->where('ad_id', $ad->id)->exists()) {
            $user->favoriteAds()->detach($ad->id);

            return redirect()->back()->with('Success','Ad is removed from favourites');
        }

        $user->favoriteAds()->attach($ad->id);

        return redirect()->back()->with('Success','Ad is added to favourites');

    }
}
