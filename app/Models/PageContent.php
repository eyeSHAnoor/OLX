<?php
// app/Models/PageContent.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageContent extends Model
{
    protected $fillable = ['page_key', 'title', 'subtitle', 'content', 'is_active'];

    protected $casts = [
        'content' => 'array',
    ];

    public static function getPage($key)
    {
        return static::where('page_key', $key)->where('is_active', true)->first();
    }
}