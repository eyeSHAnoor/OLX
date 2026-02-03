<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = [
        'name',
        'description',
        'guard_name',
        'tenant_id',
    ];

    protected static function booted(): void
    {
        // Automatically apply tenant scope for authenticated tenant users
        static::addGlobalScope('tenant', function (Builder $query) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $query->where('tenant_id', auth()->user()->tenant_id);
            }
        });

        // Automatically assign tenant_id when creating roles
        static::creating(function ($model) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $model->tenant_id = auth()->user()->tenant_id;
            }
        });

        // Automatically delete related permissions
        static::deleting(function ($model) {
            $model->permissions?->each->delete();
        });
    }
}
