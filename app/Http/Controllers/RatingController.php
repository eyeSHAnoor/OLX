<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rating;
use App\Models\User;
use App\Notifications\NewRatingNotification;

class RatingController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'rated_user_id' => 'required|exists:users,id',
            'ad_id' => 'required|exists:ads,id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
        ]);

        if(auth()->id() == $request->rated_user_id){
            return back()->with('error','You cannot rate yourself.');
        }

        $rating = Rating::updateOrCreate(
            [
                'rater_id' => auth()->id(),
                'rated_user_id' => $request->rated_user_id,
                'ad_id' => $request->ad_id
            ],
            [
                'rating' => $request->rating,
                'review' => $request->review
            ]
        );

        $ratedUser = User::find($request->rated_user_id);

        $ratedUser->notify(
            new NewRatingNotification(auth()->user(), $rating, $request->ad_id)
        );

        return back()->with('success','Rating submitted successfully.');
    }
}