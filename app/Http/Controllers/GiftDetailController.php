<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\GiftService;
use Illuminate\Http\Request;

class GiftDetailController extends Controller
{
    protected $giftService;

    public function __construct(GiftService $giftService)
    {
        $this->giftService = $giftService;
    }

    /**
     * Get active campaign info.
     */
    public function getActiveCampaign()
    {
        $campaign = $this->giftService->getActiveCampaign();

        if (!$campaign) {
            return response()->json([
                'success' => false,
                'message' => 'No active campaign found'
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $campaign->id,
                'name' => $campaign->name,
                'start_date' => $campaign->start_date,
                'end_date' => $campaign->end_date,
                'is_active' => $campaign->is_active,
            ]
        ]);
    }

    /**
     * Get all candidates for a campaign.
     */
    public function getCandidates(Request $request)
    {
        $campaignId = $request->campaign_id;

        if (!$campaignId) {
            $campaign = $this->giftService->getActiveCampaign();
            if (!$campaign) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active campaign found'
                ]);
            }
            $campaignId = $campaign->id;
        }

        $candidates = $this->giftService->getCandidates($campaignId);
        $isCurrentUserCandidate = $this->giftService->isCurrentUserCandidate($campaignId);
        $userAssignment = $this->giftService->getUserAssignment($campaignId);

        return response()->json([
            'success' => true,
            'data' => [
                'candidates' => $candidates,
                'total_candidates' => $candidates->count(),
                'is_current_user_candidate' => $isCurrentUserCandidate,
                'user_assignment' => $userAssignment ? [
                    'id' => $userAssignment->id,
                    'gift_name' => $userAssignment->gift->name,
                    'gift_image' => $userAssignment->gift->image,
                    'status' => $userAssignment->status,
                ] : null,
            ]
        ]);
    }

    /**
     * Get delivered users for a campaign.
     */
    public function getDeliveredUsers(Request $request)
    {
        $campaignId = $request->campaign_id;

        if (!$campaignId) {
            $campaign = $this->giftService->getActiveCampaign();
            if (!$campaign) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active campaign found'
                ]);
            }
            $campaignId = $campaign->id;
        }

        $deliveredUsers = $this->giftService->getDeliveredUsers($campaignId);

        return response()->json([
            'success' => true,
            'data' => [
                'delivered_users' => $deliveredUsers,
                'total_delivered' => $deliveredUsers->count(),
            ]
        ]);
    }

    /**
     * Get campaign statistics.
     */
    public function getStatistics(Request $request)
    {
        $campaignId = $request->campaign_id;

        if (!$campaignId) {
            $campaign = $this->giftService->getActiveCampaign();
            if (!$campaign) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active campaign found'
                ]);
            }
            $campaignId = $campaign->id;
        }

        $candidates = $this->giftService->getCandidates($campaignId);
        $delivered = $this->giftService->getDeliveredUsers($campaignId);

        return response()->json([
            'success' => true,
            'data' => [
                'total_candidates' => $candidates->count(),
                'total_delivered' => $delivered->count(),
                'is_current_user_candidate' => $this->giftService->isCurrentUserCandidate($campaignId),
            ]
        ]);
    }
}