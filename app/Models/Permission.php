<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    protected $fillable = [
        'name',
        'guard_name',
        'group',
        //        'tenant_id',
    ];

    protected static function booted(): void
    {
        //        static::addGlobalScope('tenant', function (Builder $query) {
//            if (auth()->check() && auth()->user()->tenant_id) {
//                $query->where('tenant_id', auth()->user()->tenant_id);
//            }
//        });
//
//        static::creating(function ($model) {
//            if (auth()->check() && auth()->user()->tenant_id) {
//                $model->tenant_id = auth()->user()->tenant_id;
//            }
//        });

    }
}
