<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackingOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'comments',
        'status',
        'carrier',
        'payment_status',
        'parcel_status',
        'created_by',
        'supplier',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function items()
    {
        return $this->hasMany(TrackingOrderItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function files()
    {
        return $this->morphMany(File::class, 'fileable');
    }

    public function carriers()
    {
        return $this->hasMany(Carrier::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Generate a formatted order number: ORD-YY-00001
     */
    public static function generateOrderNumber()
    {
        $year = now()->format('y');
        $last = static::whereYear('created_at', now()->year)->latest('id')->first();
        $next = $last ? intval(substr($last->order_number, -5)) + 1 : 1;
        return 'ORD-' . $year . '-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Automatically set order number and created_by on creation
     */
    protected static function booted()
    {
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }

            if (auth()->check() && empty($order->created_by)) {
                $order->created_by = auth()->id();
            }
        });
    }
}