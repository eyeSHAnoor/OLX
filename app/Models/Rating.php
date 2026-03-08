<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $fillable = [
        'rater_id',
        'rated_user_id',
        'ad_id',
        'rating',
        'review'
    ];

     // Add this accessor for star display
    public function getStarRatingAttribute()
    {
        return str_repeat('⭐', $this->rating);
    }
    
    public function rater()
    {
        return $this->belongsTo(User::class,'rater_id');
    }

    public function ratedUser()
    {
        return $this->belongsTo(User::class,'rated_user_id');
    }

    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }
}
