<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Models\Subscription;
use Zfhassaan\jazzcash\JazzCash;

class JazzCashController extends Controller
{
    public function callback(Request $request)
    {
        $data = $request->all();
        Log::info('JazzCash Callback received', $data);
        
        // Debug log
        Log::debug('JazzCash Callback Full Data', [
            'all' => $data,
            'secure_hash' => $data['pp_SecureHash'] ?? 'missing'
        ]);
        
        // METHOD 1: Extract subscription ID from bill reference (pp_BillReference)
        $billReference = $data['pp_BillReference'] ?? '';
        $subscriptionId = null;
        
        if (preg_match('/SUB-(\d+)-/', $billReference, $matches)) {
            $subscriptionId = (int) $matches[1];
        }
        
        // METHOD 2: Fallback to session
        if (!$subscriptionId) {
            $subscriptionId = session('jazzcash_subscription_id');
        }
        
        // METHOD 3: Try ppmpf_1 if available (some packages use this)
        if (!$subscriptionId && isset($data['ppmpf_1'])) {
            $subscriptionId = (int) $data['ppmpf_1'];
        }

        if (!$subscriptionId) {
            Log::error('Subscription ID missing in callback', $data);
            return Inertia::render('home/Failed', [
                'message' => 'Subscription ID missing in callback'
            ]);
        }

        // Find the subscription
        $subscription = Subscription::find($subscriptionId);
        if (!$subscription) {
            Log::error('Subscription not found', ['id' => $subscriptionId]);
            return Inertia::render('home/Failed', [
                'message' => 'Subscription not found'
            ]);
        }

        // Check if payment was successful (JazzCash success code is '000')
        $responseCode = $request->input('pp_ResponseCode');
        $responseMessage = $request->input('pp_ResponseMessage', '');
        $transactionRef = $request->input('pp_TxnRefNo', '');
        
        Log::debug('JazzCash Payment Verification', [
            'response_code' => $responseCode,
            'transaction_ref' => $transactionRef,
            'subscription_id' => $subscriptionId
        ]);

        if ($responseCode === '000') {
            // Payment successful
            $subscription->update([
                'payment_status' => 'active', // or 'completed' based on your status
                'transaction_id' => $transactionRef,
                'payment_data' => array_merge($subscription->payment_data ?? [], [
                    'jazzcash_response' => $data,
                    'completed_at' => now()
                ])
            ]);
            
            Log::info('Payment successful for subscription', [
                'subscription_id' => $subscriptionId,
                'transaction_ref' => $transactionRef
            ]);

            return Inertia::render('home/Success', [
                'message' => 'Payment completed successfully!',
                'transaction_id' => $transactionRef
            ]);
            
        } else {
            // Payment failed - update status instead of deleting
            $subscription->update([
                'payment_status' => 'failed',
                'payment_data' => array_merge($subscription->payment_data ?? [], [
                    'error_code' => $responseCode,
                    'error_message' => $responseMessage,
                    'failed_at' => now()->toDateTimeString(),
                    'jazzcash_response' => $data
                ])
            ]);
            
            Log::warning('Payment failed for subscription', [
                'subscription_id' => $subscriptionId,
                'response_code' => $responseCode,
                'response_message' => $responseMessage
            ]);

            // Map response codes to user-friendly messages
            $errorMessage = $this->getJazzCashErrorMessage($responseCode, $responseMessage);

            return Inertia::render('home/Failed', [
                'message' => 'Payment failed: ' . $errorMessage,
                'error_code' => $responseCode,
                'error_message' => $responseMessage
            ]);
        }
    }
    
    /**
     * Helper method to get user-friendly error messages
     */
    private function getJazzCashErrorMessage($code, $defaultMessage = '')
    {
        $messages = [
            '101' => 'Invalid amount',
            '102' => 'Invalid merchant ID',
            '103' => 'Invalid password',
            '104' => 'Invalid hash key',
            '105' => 'Transaction expired',
            '106' => 'Transaction already completed',
            '107' => 'Transaction cancelled by user',
            '108' => 'Transaction declined by bank',
            '109' => 'Insufficient balance',
            '110' => 'Invalid account number',
            '111' => 'Transaction limit exceeded',
            '112' => 'Invalid transaction reference',
            '113' => 'System error, please try again',
            '114' => 'Invalid return URL',
        ];
        
        return $messages[$code] ?? ($defaultMessage ?: 'Payment processing failed');
    }

    public function ipn(Request $request)
    {
        Log::info('JazzCash IPN Received', $request->all());
        
        // Process IPN similarly to callback but don't return Inertia views
        $data = $request->all();
        $subscriptionId = $data['ppmpf_1'] ?? null;
        $responseCode = $data['pp_ResponseCode'] ?? null;
        
        if ($subscriptionId && $responseCode === '000') {
            $subscription = Subscription::find($subscriptionId);
            if ($subscription && $subscription->payment_status === 'pending') {
                $subscription->update([
                    'payment_status' => 'completed',
                    'transaction_id' => $data['pp_TxnRefNo'] ?? null,
                    'payment_data' => array_merge($subscription->payment_data ?? [], [
                        'ipn_response' => $data,
                        'ipn_received_at' => now()
                    ])
                ]);
                
                $subscription->user->update(['status' => 'active']);
            }
        }
        
        // Always return success to JazzCash
        return response()->json(['status' => 'OK']);
    }
}