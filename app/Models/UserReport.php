<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'reported_user_id',
        'reported_by',
        'ad_id',
        'reason',
        'message',
        'status'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_REVIEWED = 'reviewed';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_REJECTED = 'rejected';

    // Reason constants (optional)
    const REASON_SCAM = 'scam';
    const REASON_SPAM = 'spam';
    const REASON_ABUSIVE = 'abusive';
    const REASON_FAKE_LISTING = 'fake_listing';
    const REASON_INAPPROPRIATE = 'inappropriate';
    const REASON_OTHER = 'other';

    /**
     * Get the reported user
     */
    public function reportedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    /**
     * Get the user who reported
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    /**
     * Get the associated ad (if any)
     */
    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }

    /**
     * Scope for pending reports
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for reviewed reports
     */
    public function scopeReviewed($query)
    {
        return $query->where('status', self::STATUS_REVIEWED);
    }

    /**
     * Check if report is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Get all possible reasons
     */
    public static function getReasons(): array
    {
        return [
            self::REASON_SCAM => 'Scam or Fraud',
            self::REASON_SPAM => 'Spam',
            self::REASON_ABUSIVE => 'Abusive Behavior',
            self::REASON_FAKE_LISTING => 'Fake Listing',
            self::REASON_INAPPROPRIATE => 'Inappropriate Content',
            self::REASON_OTHER => 'Other',
        ];
    }

    /**
     * Get all possible statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_REVIEWED => 'Reviewed',
            self::STATUS_RESOLVED => 'Resolved',
            self::STATUS_REJECTED => 'Rejected',
        ];
    }
}