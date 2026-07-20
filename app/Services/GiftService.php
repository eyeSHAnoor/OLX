<?php

namespace App\Services;

use App\Models\GiftAssignment;
use App\Models\GiftPeriod;
use Illuminate\Support\Facades\Auth;

class GiftService
{
    /**
     * Get active gift campaign.
     */
    public function getActiveCampaign()
    {
        return GiftPeriod::where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();
    }

    /**
     * Get all candidates for a campaign.
     */
    public function getCandidates($campaignId)
    {
        return GiftAssignment::with(['user', 'gift', 'assignedBy'])
            ->where('gift_period_id', $campaignId)
            ->where('status', 'candidate')
            ->get()
            ->map(function ($assignment) {
                return [
                    'id' => $assignment->id,
                    'user_name' => $assignment->user->name,
                    'user_email' => $assignment->user->email,
                    'user_avatar' => $assignment->user->profile?->profile_image,
                    'gift_name' => $assignment->gift->name,
                    'gift_image' => $assignment->gift->image,
                    'assigned_at' => $assignment->assigned_at,
                    'assigned_by' => $assignment->assignedBy?->name,
                ];
            });
    }

    /**
     * Get all delivered users for a campaign.
     */
    public function getDeliveredUsers($campaignId)
    {
        return GiftAssignment::with(['user', 'gift', 'assignedBy'])
            ->where('gift_period_id', $campaignId)
            ->where('status', 'delivered')
            ->orWhere('status', 'received')
            ->get()
            ->map(function ($assignment) {
                return [
                    'id' => $assignment->id,
                    'user_name' => $assignment->user->name,
                    'user_email' => $assignment->user->email,
                    'user_avatar' => $assignment->user->profile?->profile_image,
                    'gift_name' => $assignment->gift->name,
                    'gift_image' => $assignment->gift->image,
                    'status' => $assignment->status,
                    'delivered_at' => $assignment->updated_at,
                ];
            });
    }

    /**
     * Check if current user is a candidate.
     */
    public function isCurrentUserCandidate($campaignId)
    {
        if (!Auth::check()) {
            return false;
        }

        return GiftAssignment::where('user_id', Auth::id())
            ->where('gift_period_id', $campaignId)
            ->where('status', 'candidate')
            ->exists();
    }

    /**
     * Get current user's gift assignment.
     */
    public function getUserAssignment($campaignId)
    {
        if (!Auth::check()) {
            return null;
        }

        return GiftAssignment::with(['gift', 'giftPeriod'])
            ->where('user_id', Auth::id())
            ->where('gift_period_id', $campaignId)
            ->first();
    }
}