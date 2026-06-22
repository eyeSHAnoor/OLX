<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPermission extends Model
{
    protected $table = 'subscription_permissions';

    protected $fillable = [
        'name',
        'label',
    ];

    public function plans()
    {
        return $this->belongsToMany(
            Plan::class,
            'plan_permission',
            'subscription_permission_id',
            'plan_id'
        );
    }
}