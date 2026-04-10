<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdView extends Model
{
    protected $fillable = [
        'ad_id',
        'user_id',
        'ip_address',
        'session_id',
        'user_agent',
    ];

    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}