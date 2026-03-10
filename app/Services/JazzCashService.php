<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\Plan;
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
     * Prepare payment request data for JazzCash
     */
    public function preparePaymentRequest(Plan $plan, $user, $subscriptionId)
    {
        // Convert amount to paisa (multiply by 100) and pad to 12 digits with leading zeros
        $ppAmount = str_pad((int)($plan->price * 100), 12, "0", STR_PAD_LEFT);
        
        // Generate unique transaction reference
        $ppTxnRefNo = 'T' . time() . rand(100, 999);
        $ppTxnDateTime = now()->format('YmdHis');
        
        // Prepare all required fields as per JazzCash documentation
        $data = [
            'pp_Version' => '2.0',
            'pp_TxnType' => 'MWALLET',
            'pp_Language' => 'EN',
            'pp_MerchantID' => $this->merchantId,
            'pp_Password' => $this->password,
            'pp_TxnRefNo' => $ppTxnRefNo,
            'pp_TxnDateTime' => $ppTxnDateTime,
            'pp_TxnExpiryDateTime' => now()->addHours(2)->format('YmdHis'),
            'pp_Amount' => $ppAmount,
            'pp_TxnCurrency' => 'PKR',
            'pp_TxnDesc' => 'Subscription: ' . $plan->name,
            'pp_Description' => 'Subscription: ' . $plan->name, // ADDED: Required field
            'pp_BillReference' => 'SUB' . $subscriptionId,
            'pp_ReturnURL' => $this->returnUrl,
            'ppmpf_1' => (string)$subscriptionId,
            'ppmpf_2' => (string)$user->id,
            'ppmpf_3' => (string)$plan->id,
            'ppmpf_4' => $user->email,
            'ppmpf_5' => $user->name,
        ];
        
        // Calculate secure hash
        $data['pp_SecureHash'] = $this->calculateSecureHash($data);
        
        Log::info('JazzCash Payment Request Prepared', [
            'subscription_id' => $subscriptionId,
            'amount' => $plan->price,
            'txn_ref' => $ppTxnRefNo
        ]);
        
        return $data;
    }
    
    /**
     * Calculate secure hash for JazzCash according to documentation
     * 
     * The SHA-256 HMAC calculation includes all PP fields
     * All transaction fields are concatenated in alphabetical order with '&' after every field except the last field
     * Shared Secret is PREPENDED to this concatenated string
     * String is converted to UTF-8 then ISO-8859-1
     * Hashed using HMAC-SHA256 with UTF-8 encoded Shared Secret as key
     * Result is converted to hexadecimal
     */
    protected function calculateSecureHash(array $data)
    {
        // 1. Get all fields starting with 'pp_' (case insensitive)
        $ppFields = [];
        foreach ($data as $key => $value) {
            if (str_starts_with(strtolower($key), 'pp_') && $key !== 'pp_SecureHash') {
                $ppFields[$key] = $value;
            }
        }
        
        // 2. Sort fields in ascending alphabetical order by field name
        ksort($ppFields, SORT_STRING);
        
        // 3. Concatenate values with '&' separator
        $concatenatedString = '';
        foreach ($ppFields as $value) {
            $concatenatedString .= $value . '&';
        }
        // Remove trailing '&' (last field doesn't have & after it)
        $concatenatedString = rtrim($concatenatedString, '&');
        
        // 4. PREPEND the shared secret (integeritySalt) with '&'
        $hashString = $this->integeritySalt . '&' . $concatenatedString;
        
        Log::debug('JazzCash Pre-Hash String', [
            'hash_string' => $hashString,
            'fields' => array_keys($ppFields)
        ]);
        
        // 5. Convert to UTF-8 bytes then to ISO-8859-1 encoding
        $hash = hash_hmac('sha256', $hashString, $this->integeritySalt);
        
        // 7. Convert to uppercase
        return strtoupper($hash);
    }

    public function verifyPaymentResponse(array $response)
    {
        $receivedHash = $response['pp_SecureHash'] ?? null;

        if (!$receivedHash) {
            Log::warning('JazzCash: No secure hash in response');
            return false;
        }

        $ppFields = [];

        foreach ($response as $key => $value) {
            if (
                str_starts_with(strtolower($key), 'pp_') &&
                $key !== 'pp_SecureHash'
            ) {
                $ppFields[$key] = $value;
            }
        }

        ksort($ppFields, SORT_STRING);

        $concatenatedString = '';

        foreach ($ppFields as $value) {
            $concatenatedString .= $value . '&';
        }

        $concatenatedString = rtrim($concatenatedString, '&');

        $hashString = $this->integeritySalt . '&' . $concatenatedString;

        $calculatedHash = strtoupper(hash_hmac('sha256', $hashString, $this->integeritySalt));

        Log::error('JazzCash Hash Debug', [
            'received_hash' => $receivedHash,
            'calculated_hash' => $calculatedHash,
            'hash_string' => $hashString
        ]);

        return hash_equals(strtoupper($receivedHash), $calculatedHash);
    }
    
    /**
     * Process successful payment
     */
    public function processSuccessfulPayment($response)
    {
        $subscriptionId = $response['ppmpf_1'] ?? null;
        $txnRefNo = $response['pp_TxnRefNo'] ?? null;
        $responseCode = $response['pp_ResponseCode'] ?? null;
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
        
        // Check if already processed to avoid duplicates
        if ($subscription->payment_status === 'completed') {
            Log::warning('JazzCash: Subscription already completed', [
                'subscription_id' => $subscriptionId
            ]);
            return true;
        }
        
        // Update subscription
        $subscription->update([
            'payment_status' => 'completed',
            'transaction_id' => $txnRefNo,
            'payment_gateway' => 'jazzcash',
            'payment_data' => array_merge($subscription->payment_data ?? [], [
                'auth_code' => $response['pp_AuthCode'] ?? null,
                'retrieval_ref_no' => $response['pp_RetreivalReferenceNo'] ?? null,
                'response_code' => $responseCode,
                'response_message' => $response['pp_ResponseMessage'] ?? null,
                'bank_id' => $response['pp_BankID'] ?? null,
                'full_response' => $response
            ]),
            'starts_at' => now(),
            'ends_at' => now()->addDays($subscription->plan->duration_days),
        ]);
        
        // Update user status
        if ($subscription->user) {
            $subscription->user->update([
                'subscription_status' => 'active'
            ]);
        }
        
        Log::info('JazzCash: Payment processed successfully', [
            'subscription_id' => $subscriptionId,
            'txn_ref_no' => $txnRefNo,
            'response_code' => $responseCode
        ]);
        
        return true;
    }
    
    /**
     * Process failed payment
     */
    public function processFailedPayment($response)
    {
        $subscriptionId = $response['ppmpf_1'] ?? null;
        $responseCode = $response['pp_ResponseCode'] ?? 'unknown';
        $responseMessage = $response['pp_ResponseMessage'] ?? 'Unknown error';
        
        if (!$subscriptionId) {
            Log::error('JazzCash: No subscription ID in failed response', $response);
            return false;
        }
        
        $subscription = Subscription::find($subscriptionId);
        
        if ($subscription) {
            $subscription->update([
                'payment_status' => 'failed',
                'payment_data' => array_merge($subscription->payment_data ?? [], [
                    'error_response' => $response,
                    'error_code' => $responseCode,
                    'error_message' => $responseMessage,
                    'failed_at' => now()->toDateTimeString()
                ])
            ]);
            
            Log::warning('JazzCash: Payment failed', [
                'subscription_id' => $subscriptionId,
                'response_code' => $responseCode,
                'response_message' => $responseMessage
            ]);
        } else {
            Log::error('JazzCash: Subscription not found for failed payment', [
                'subscription_id' => $subscriptionId
            ]);
        }
        
        return true;
    }
    
    /**
     * Get response message based on response code
     */
    public function getResponseMessage($code)
    {
        $messages = [
            '000' => 'Transaction Successful',
            '001' => 'Transaction Declined',
            '002' => 'Transaction Reversed',
            '003' => 'Transaction Pending',
            '004' => 'Invalid Amount',
            '005' => 'Invalid Merchant ID',
            '006' => 'Invalid Transaction Reference',
            '007' => 'Invalid Date/Time',
            '008' => 'Invalid Currency',
            '009' => 'Invalid Description',
            '010' => 'Invalid Return URL',
            '011' => 'Invalid Hash',
            '012' => 'Duplicate Transaction',
            '013' => 'Transaction Not Found',
            '014' => 'Account Blocked',
            '015' => 'Insufficient Balance',
            '016' => 'Invalid Account',
            '017' => 'Transaction Expired',
            '018' => 'Invalid Response Code',
            '019' => 'Invalid Secure Hash',
            '020' => 'Invalid IP',
            '100' => 'Payment Successful',
            '101' => 'Payment Pending',
            '102' => 'Payment Failed',
            '103' => 'Payment Cancelled',
            '104' => 'Payment Expired',
            '105' => 'Payment Declined by Bank',
            '106' => 'Payment Declined by JazzCash',
            '107' => 'Invalid Payment Method',
            '108' => 'Mobile Account Not Active',
            '109' => 'Mobile Number Not Registered',
            '110' => 'Invalid Description',
            '111' => 'Invalid Amount Format',
            '112' => 'Invalid Bill Reference',
        ];
        
        return $messages[$code] ?? 'Unknown Response Code: ' . $code;
    }
}