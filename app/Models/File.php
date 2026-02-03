<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

class File extends Model
{
    use HasFactory;

    protected $fillable = ['fileable_id', 'fileable_type', 'file_location', 'collection', 'file_name', 'meta'];
    protected $appends = ['file_url'];

    protected $casts = [
        'meta' => 'array',
    ];

    public function files()
    {
        return $this->hasMany(TrackingOrder::class);
    }


    public function fileable()
    {
        return $this->morphTo();
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($file) {
            if (Storage::disk('public')->exists($file->file_location)) {
                Storage::disk('public')->delete($file->file_location);
            }
        });
    }

    protected function fileUrl(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => $attributes['file_location']
            ? (
                filter_var($attributes['file_location'], FILTER_VALIDATE_URL)
                ? $attributes['file_location'] // already a full URL
                : Storage::disk('public')->url($attributes['file_location']) // local disk file
            )
            : null
        );
    }
}
