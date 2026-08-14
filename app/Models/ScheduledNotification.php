<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledNotification extends Model
{
    protected $fillable = ['title', 'message', 'url', 'scheduled_at', 'is_sent', 'is_email'];
    
    protected $casts = [
        'scheduled_at' => 'datetime',
        'is_sent' => 'boolean',
        'is_email' => 'boolean',
    ];
}