<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    protected $fillable = ['name'];
    public function values()
    {
        return $this->hasMany(FeatureValue::class);
    }

    public function ads()
    {
        return $this->belongsToMany(Ad::class, 'ad_feature')
            ->withPivot(['feature_value_id', 'custom_value'])
            ->withTimestamps();
    }
}

