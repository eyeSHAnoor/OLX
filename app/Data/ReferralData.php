<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Carbon\Carbon;

class ReferralData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $referral_code,
        public readonly int $points_balance,
        public readonly ?int $referred_by,
        public readonly bool $can_assign_code, 
        public readonly ?string $referrer_name,
        public readonly int $total_referrals_count,
        public readonly int $total_points_earned,
        public readonly int $points_per_referral,
        public readonly ?Carbon $created_at,
        public readonly ?UserReferralScoreData $referral_score = null, // Add this line
    ) {}

    public static function fromModel($user): self
    {
        // Get the points awarded from the latest referral they made
        // This assumes all their referrals use the same point value
        $latestReferral = $user->referralsMade()
            ->where('status', 'completed')
            ->latest()
            ->first();

        $pointsPerReferral = $latestReferral ? $latestReferral->points_awarded : 200;
        $totalReferrals = $user->referralsMade()->where('status', 'completed')->count();
        $totalPointsEarned = $user->referralsMade()
            ->where('status', 'completed')
            ->sum('points_awarded');

        // Get referral score data if it exists
        $referralScore = null;
        if ($user->relationLoaded('referralScore') && $user->referralScore) {
            $referralScore = UserReferralScoreData::fromModel($user->referralScore);
        } elseif ($user->referralScore) {
            // If relation is not loaded but exists, load it
            $user->load('referralScore');
            if ($user->referralScore) {
                $referralScore = UserReferralScoreData::fromModel($user->referralScore);
            }
        }

        return new self(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            referral_code: $user->referral_code,
            points_balance: $user->points_balance,
            referred_by: $user->referred_by,
            referrer_name: $user->referrer?->name,
            total_referrals_count: $totalReferrals,
            total_points_earned: $totalPointsEarned,
            points_per_referral: $pointsPerReferral,
            created_at: $user->created_at,
            can_assign_code: $user->can_assign_code,
            referral_score: $referralScore, // Add this
        );
    }
}