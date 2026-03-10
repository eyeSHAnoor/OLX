<?php

namespace App\Http\Controllers;

use App\Services\JazzCashService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class JazzCashController extends Controller
{
    protected $jazzCashService;
    
    public function __construct(JazzCashService $jazzCashService)
    {
        $this->jazzCashService = $jazzCashService;
    }
    
    /**
     * Handle JazzCash payment callback
     */
    public function callback(Request $request)
    {
        $data = $request->all();
        Log::info('JazzCash Callback received', $data);
        
        // Debug log
        Log::debug('JazzCash Callback Full Data', [
            'all' => $data,
            'secure_hash' => $data['pp_SecureHash'] ?? 'missing'
        ]);
        
        // Get subscription ID from ppmpf_1 and cast to integer
        $subscriptionId = isset($data['ppmpf_1']) ? (int) $data['ppmpf_1'] : null;

        if (!$subscriptionId) {
            Log::error('Subscription ID missing in callback', $data);
            return Inertia::render('home/Failed', [
                'message' => 'Subscription ID missing in callback'
            ]);
        }

        // Find the subscription
        $subscription = \App\Models\Subscription::find($subscriptionId);
        if (!$subscription) {
            Log::error('Subscription not found', ['id' => $subscriptionId]);
            return Inertia::render('home/Failed', [
                'message' => 'Subscription not found'
            ]);
        }

        // Verify the response hash
        $isValid = $this->jazzCashService->verifyPaymentResponse($data);
        Log::debug('JazzCash Verification Result', ['isValid' => $isValid]);

        if ($isValid) {
            $responseCode = $request->input('pp_ResponseCode');

            if ($responseCode === '000' || $responseCode === '100') {
                // Payment successful
                $this->jazzCashService->processSuccessfulPayment($data);

                return Inertia::render('home/Success', [
                    'message' => 'Payment completed successfully!',
                    'transaction_id' => $request->input('pp_TxnRefNo')
                ]);
            } else {
                // Payment failed → delete subscription
                $subscription->delete();
                Log::warning('Subscription deleted due to failed payment', [
                    'subscription_id' => $subscriptionId,
                    'response_code' => $responseCode
                ]);

                $errorMessage = $this->jazzCashService->getResponseMessage($responseCode) ?? 'Payment failed';

                return Inertia::render('home/Failed', [
                    'message' => 'Payment failed: ' . $errorMessage,
                    'error_code' => $responseCode,
                    'error_message' => $request->input('pp_ResponseMessage', $errorMessage)
                ]);
            }
        }

        // Invalid hash → consider it as failed payment
        $subscription->delete();
        Log::error('Subscription deleted due to invalid hash', ['subscription_id' => $subscriptionId , 'request' => $request]);

        return Inertia::render('home/Failed', [
            'message' => 'Invalid payment response - Security verification failed'
        ]);
    }
    
    /**
     * Handle Instant Payment Notification (IPN)
     */
    public function ipn(Request $request)
    {
        Log::info('JazzCash IPN received', $request->all());
        
        // Similar verification as callback
        if ($this->jazzCashService->verifyPaymentResponse($request->all())) {
            if ($request->input('pp_ResponseCode') === '000') {
                $this->jazzCashService->processSuccessfulPayment($request->all());
            } else {
                $this->jazzCashService->processFailedPayment($request->all());
            }
        }
        
        // Always return 200 OK for IPN
        return response()->json(['status' => 'ok']);
    }
}