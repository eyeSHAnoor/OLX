<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'username',
        'profile_image',
        'cover_image',
        'bio',
        'location',
        'website',
        'is_public'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
