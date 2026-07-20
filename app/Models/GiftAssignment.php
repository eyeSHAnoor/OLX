<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GiftAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'gift_period_id',
        'gift_id',
        'user_id',
        'assigned_by',
        'assigned_at',
        'status',
        'notes'
    ];

    protected $casts = [
        'assigned_at' => 'datetime'
    ];

    public function period()
    {
        return $this->belongsTo(GiftPeriod::class, 'gift_period_id');
    }

    public function gift()
    {
        return $this->belongsTo(Gift::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function giftPeriod()
    {
        return $this->belongsTo(GiftPeriod::class, 'gift_period_id');
    }
}