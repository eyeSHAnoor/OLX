<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GiftPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean'
    ];

    public function assignments()
    {
        return $this->hasMany(GiftAssignment::class);
    }
    
    public function campaignGifts()
    {
        return $this->hasMany(CampaignGift::class, 'gift_period_id');
    }
}
