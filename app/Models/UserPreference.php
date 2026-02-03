<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    protected $fillable = [
        'user_id',
        'language',
        'timezone',
        'date_format',
        'currency',
    ];
}
