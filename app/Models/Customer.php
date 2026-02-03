<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Customer extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * (Optional — Laravel infers this automatically, but you can include it for clarity)
     */
    protected $table = 'customers';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'company_name',
        'tax_id',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'user_id',
    ];

    /**
     * Relationships
     */

    // A customer may belong to a user (if linked)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Example: if a customer has orders
    public function internal_orders()
    {
        return $this->hasMany(InternalOrder::class);
    }
}
