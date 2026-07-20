<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserReferralScore extends Model
{
    protected $table = 'user_referral_scores';

    protected $fillable = [
        'user_id',
        'total_earned',
        'total_withdrawn',
        'available',
        'pending',
        'requested_amount',
        'status',
        'payment_method',
        'payment_details',
        'proof_images',
        'transaction_id',
        'admin_notes',
        'last_earning_at',
        'last_withdrawal_at',
        'processed_at',
        'confirmed_at',
    ];

    protected $casts = [
        'payment_details' => 'array',
        'proof_images' => 'array',
        'last_earning_at' => 'datetime',
        'last_withdrawal_at' => 'datetime',
        'processed_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'requested_amount' => 'decimal:2',
    ];

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_COMPLETED = 'completed';
    const STATUS_REJECTED = 'rejected';

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ========== EARNING METHODS ==========
    
    public function addEarnedPoints(int $points): self
    {
        $this->total_earned += $points;
        $this->available += $points;
        $this->last_earning_at = now();
        $this->save();
        
        return $this;
    }

    // ========== WITHDRAWAL METHODS ==========
    
    public function requestWithdrawal(int $points, float $amount, string $method, array $details): self
    {
        // $this->requested_amount = $points;
        $this->requested_amount = $points;
        $this->pending = $points;
        $this->available -= $points;
        $this->payment_method = $method;
        $this->payment_details = $details;
        $this->status = self::STATUS_PENDING;
        $this->save();
        
        return $this;
    }

    public function approveWithdrawal(string $transactionId, ?array $proofs = null, ?string $notes = null): self
    {
        $this->status = self::STATUS_APPROVED;
        $this->transaction_id = $transactionId;
        $this->proof_images = $proofs ?? $this->proof_images;
        $this->admin_notes = $notes ?? $this->admin_notes;
        $this->processed_at = now();
        $this->save();
        
        return $this;
    }

    public function completeWithdrawal(): self
    {
        $this->status = self::STATUS_COMPLETED;
        $this->total_withdrawn += $this->requested_amount;
        $this->pending = 0;
        $this->last_withdrawal_at = now();
        $this->confirmed_at = now();
        $this->save();
        
        // Reset for next withdrawal
        $this->resetRequest();
        
        return $this;
    }

    public function rejectWithdrawal(string $reason): self
    {
        $this->status = self::STATUS_REJECTED;
        $this->available += $this->requested_amount; // Return points
        $this->pending = 0;
        $this->admin_notes = $reason;
        $this->processed_at = now();
        $this->save();
        
        // Reset for next withdrawal
        $this->resetRequest();
        
        return $this;
    }

    public function confirmReceipt(): self
    {
        $this->confirmed_at = now();
        $this->save();
        
        return $this;
    }

    // ========== HELPER METHODS ==========
    
    public function resetRequest(): self
    {
        $this->requested_amount = null;
        $this->requested_amount = null;
        $this->payment_method = null;
        $this->payment_details = null;
        $this->status = self::STATUS_ACTIVE;
        $this->save();
        
        return $this;
    }

    public function hasPendingWithdrawal(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_APPROVED]);
    }

    public function getAvailablePoints(): int
    {
        return $this->available;
    }

    public function getTotalEarnedPoints(): int
    {
        return $this->total_earned;
    }

    public function getTotalWithdrawnPoints(): int
    {
        return $this->total_withdrawn;
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeHasAvailable($query, int $minPoints = 100)
    {
        return $query->where('available', '>=', $minPoints);
    }
}