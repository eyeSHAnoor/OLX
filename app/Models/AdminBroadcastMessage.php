<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminBroadcastMessage extends Model
{
    protected $fillable = ['title', 'body', 'is_active'];
}   
