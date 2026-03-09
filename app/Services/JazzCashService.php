<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\Plan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JazzCashService
{
    protected $merchantId;
    protected $password;
    protected $integeritySalt;
    protected $returnUrl;
    protected $ipnUrl;
    protected $endpoint;
    
    public function __construct()
    {
        $this->merchantId = config('jazzcash.merchant_id');
        $this->password = config('jazzcash.password');
        $this->integeritySalt = config('jazzcash.integerity_salt');
        $this->returnUrl = config('jazzcash.return_url');
        $this->ipnUrl = config('jazzcash.ipn_url');
        
        $environment = config('jazzcash.environment');
        $this->endpoint = config('jazzcash.endpoints.' . $environment);
    }
    
    /**
     * Prepare payment request data
     */
    public function preparePaymentRequest(Plan $plan, $user, $subscriptionId)
    {
        $ppAmount = str_pad((int)($plan->price * 100), 12, "0", STR_PAD_LEFT);
        $ppTxnRefNo = 'T' . time() . rand(100, 999);
        $ppTxnDateTime = now()->format('YmdHis');
        
        $data = [
            'pp_Version' => '2.0',
            'pp_TxnType' => 'MPAY',
            'pp_Language' => config('jazzcash.language'),
            'pp_MerchantID' => $this->merchantId,
            'pp_SubMerchantID' => '',
            'pp_Password' => $this->password,
            'pp_TxnRefNo' => $ppTxnRefNo,
            'pp_TxnDateTime' => $ppTxnDateTime,
            'pp_TxnExpiryDateTime' => now()->addHours(2)->format('YmdHis'),
            'pp_Amount' => $ppAmount,
            'pp_TxnCurrency' => config('jazzcash.currency'),
            'pp_TxnDesc' => 'Subscription: ' . $plan->name,
            'pp_BillReference' => 'SUB' . $subscriptionId,
            'pp_ReturnURL' => $this->returnUrl,
            'pp_SecureHash' => '', // Will be calculated
            'ppmpf_1' => $subscriptionId,
            'ppmpf_2' => $user->id,
            'ppmpf_3' => $plan->id,
            'ppmpf_4' => $user->email,
            'ppmpf_5' => $user->name,
        ];
        
        // Calculate secure hash
        $data['pp_SecureHash'] = $this->calculateSecureHash($data);
        
        return $data;
    }
    
    /**
     * Calculate secure hash for JazzCash
     */
    protected function calculateSecureHash($data)
    {
        $hashString = $this->integeritySalt . '&' .
                     $data['pp_Amount'] . '&' .
                     $data['pp_BillReference'] . '&' .
                     $data['pp_Language'] . '&' .
                     $data['pp_MerchantID'] . '&' .
                     $data['pp_Password'] . '&' .
                     $data['pp_ReturnURL'] . '&' .
                     $data['pp_TxnCurrency'] . '&' .
                     $data['pp_TxnDateTime'] . '&' .
                     $data['pp_TxnExpiryDateTime'] . '&' .
                     $data['pp_TxnRefNo'] . '&' .
                     $data['pp_TxnType'] . '&' .
                     $this->integeritySalt;
        
        return hash_hmac('sha256', $hashString, $this->integeritySalt);
    }
    
    /**
     * Verify payment response
     */
    public function verifyPaymentResponse($response)
    {
        if (!isset($response['pp_SecureHash'])) {
            return false;
        }
        
        $receivedHash = $response['pp_SecureHash'];
        
        // Recalculate hash to verify
        $hashString = $this->integeritySalt . '&' .
                     $response['pp_TxnRefNo'] . '&' .
                     $response['pp_ResponseCode'] . '&' .
                     $response['pp_ResponseMessage'] . '&' .
                     $response['pp_AuthCode'] . '&' .
                     $response['pp_RetreivalReferenceNo'] . '&' .
                     $response['pp_SecureHash'];
        
        $calculatedHash = hash_hmac('sha256', $hashString, $this->integeritySalt);
        
        return $receivedHash === $calculatedHash;
    }
    
    /**
     * Process successful payment
     */
    public function processSuccessfulPayment($response)
    {
        $subscriptionId = $response['ppmpf_1'] ?? null;
        $txnRefNo = $response['pp_TxnRefNo'] ?? null;
        $authCode = $response['pp_AuthCode'] ?? null;
        $amount = $response['pp_Amount'] ?? 0;
        
        if (!$subscriptionId) {
            Log::error('JazzCash: No subscription ID in response', $response);
            return false;
        }
        
        $subscription = Subscription::find($subscriptionId);
        
        if (!$subscription) {
            Log::error('JazzCash: Subscription not found', ['id' => $subscriptionId]);
            return false;
        }
        
        // Update subscription
        $subscription->update([
            'payment_status' => 'completed',
            'transaction_id' => $txnRefNo,
            'payment_gateway' => 'jazzcash',
            'payment_data' => array_merge($subscription->payment_data ?? [], [
                'auth_code' => $authCode,
                'response' => $response
            ]),
            'starts_at' => now(),
            'ends_at' => now()->addDays($subscription->plan->duration_days),
        ]);
        
        // Update user status if needed
        $subscription->user->update([
            'status' => 'active'
        ]);
        
        Log::info('JazzCash: Payment successful', [
            'subscription_id' => $subscriptionId,
            'txn_ref_no' => $txnRefNo
        ]);
        
        return true;
    }
    
    /**
     * Process failed payment
     */
    public function processFailedPayment($response)
    {
        $subscriptionId = $response['ppmpf_1'] ?? null;
        
        if (!$subscriptionId) {
            Log::error('JazzCash: No subscription ID in failed response', $response);
            return false;
        }
        
        $subscription = Subscription::find($subscriptionId);
        
        if ($subscription) {
            $subscription->update([
                'payment_status' => 'failed',
                'payment_data' => array_merge($subscription->payment_data ?? [], [
                    'error_response' => $response
                ])
            ]);
            
            Log::warning('JazzCash: Payment failed', [
                'subscription_id' => $subscriptionId,
                'response_code' => $response['pp_ResponseCode'] ?? 'unknown'
            ]);
        }
        
        return true;
    }
}