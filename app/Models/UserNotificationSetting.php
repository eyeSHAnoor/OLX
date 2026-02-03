<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotificationSetting extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'methods',
        'timing',
        'frequency',
    ];

    protected $casts = [
        'methods' => 'array',

    ];
}
