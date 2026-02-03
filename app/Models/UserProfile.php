<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $fillable = [
            'user_id',
            'company_name',
            'address',
            'phone_1',
            'phone_2',
            'contact_person',
            'company_email',
            'company_verified_at',
            'verified_by',
    ];
}
