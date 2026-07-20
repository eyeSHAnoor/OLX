<?php

namespace App\Data;

use App\Models\UserReferralScore;
use Spatie\LaravelData\Data;

class UserReferralScoreData extends Data
{
    public function __construct(
        public readonly int $total_earned,
        public readonly int $total_withdrawn,
        public readonly int $available,
        public readonly int $pending,
        public readonly string $status,
        public readonly bool $has_pending_withdrawal,
    ) {}

    public static function fromModel(UserReferralScore $score): self
    {
        return new self(
            total_earned: $score->total_earned ?? 0,
            total_withdrawn: $score->total_withdrawn ?? 0,
            available: $score->available ?? 0,
            pending: $score->pending ?? 0,
            status: $score->status ?? 'active',
            has_pending_withdrawal: $score->hasPendingWithdrawal(),
        );
    }
}