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
        // Convert amount to paisa (multiply by 100) and pad to 12 digits
        $ppAmount = str_pad((int)($plan->price * 100), 12, "0", STR_PAD_LEFT);

        $ppTxnRefNo = 'T' . time() . rand(100, 999);
        $ppTxnDateTime = now()->format('YmdHis');

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
            'pp_TxnDesc' => 'Subscription',
            'pp_Description' => 'Subscription',
            'pp_BillReference' => 'SUB' . $subscriptionId,
            'pp_ReturnURL' => $this->returnUrl,
            'ppmpf_1' => (string)$subscriptionId,
            'ppmpf_2' => (string)$user->id,
            'ppmpf_3' => (string)$plan->id,
            'ppmpf_4' => $user->email,
            'ppmpf_5' => $user->name,
        ];

        $data['pp_SecureHash'] = $this->calculateSecureHash($data);

        Log::info('JazzCash Payment Request Prepared', [
            'subscription_id' => $subscriptionId,
            'amount' => $plan->price,
            'txn_ref' => $ppTxnRefNo
        ]);

        return $data;
    }

    /**
     * Calculate secure hash for outgoing requests
     */
    protected function calculateSecureHash(array $data)
    {
        $ppFields = [];
        foreach ($data as $key => $value) {
                if (str_starts_with(strtolower($key), 'pp_') && 
                $key !== 'pp_SecureHash' && 
                strtolower($key) !== 'pp_password') {  // ADD THIS
                $ppFields[$key] = $value;
            }
        }

        ksort($ppFields, SORT_STRING);

        $hashString = $this->integeritySalt;
        foreach ($ppFields as $value) {
            $hashString .= '&' . $value;
        }

        Log::error('JazzCash Request Hash String', [
            'hash_string' => $hashString,
            'fields' => array_keys($ppFields)
        ]);

        // Apply encoding as per documentation
        $utf8String = mb_convert_encoding($hashString, 'UTF-8');
        $isoString = mb_convert_encoding($utf8String, 'ISO-8859-1');

        return strtoupper(hash_hmac('sha256', $isoString, $this->integeritySalt));
    }

    /**
     * Verify hash from JazzCash callback response
     */
    public function verifyPaymentResponse(array $response)
    {
        Log::error($response);
        $receivedHash = $response['pp_SecureHash'] ?? null;
        if (!$receivedHash) {
            Log::error('JazzCash: No secure hash in response');
            return false;
        }

        // Get ALL PP fields including empty ones (case-insensitive)
        $ppFields = [];
        foreach ($response as $key => $value) {
            if (str_starts_with(strtolower($key), 'pp_') && strtolower($key) !== 'pp_securehash') {
                // Include even empty values, convert to string
                $ppFields[$key] = (string)($value ?? '');
            }
        }

        // Log fields received from JazzCash
        Log::error('JazzCash Response Fields', [
            'fields_with_values' => $ppFields,
            'field_count' => count($ppFields)
        ]);

        // Sort alphabetically by key (case-sensitive as per original keys)
        ksort($ppFields, SORT_STRING);

        // Build hash string: SALT&value1&value2&value3...
        $hashString = $this->integeritySalt;
        foreach ($ppFields as $value) {
            $hashString .= '&' . $value;
        }

        // Apply exact same encoding as calculateSecureHash
        $utf8String = mb_convert_encoding($hashString, 'UTF-8');
        $isoString = mb_convert_encoding($utf8String, 'ISO-8859-1');

        // Calculate hash
        $calculatedHash = strtoupper(hash_hmac('sha256', $isoString, $this->integeritySalt));

        // Detailed logging for debugging
        Log::error('JazzCash Hash Verification', [
            'received_hash' => $receivedHash,
            'calculated_hash' => $calculatedHash,
            'hash_string' => $hashString,
            'fields_count' => count($ppFields),
            'fields_list' => array_keys($ppFields),
            'match' => hash_equals($receivedHash, $calculatedHash) ? 'YES' : 'NO'
        ]);

        return hash_equals($receivedHash, $calculatedHash);
    }

    /**
     * Process successful payment from callback
     */
    public function processSuccessfulPayment($response)
    {
        $subscriptionId = $response['ppmpf_1'] ?? null;
        $txnRefNo = $response['pp_TxnRefNo'] ?? null;
        $responseCode = $response['pp_ResponseCode'] ?? null;

        if (!$subscriptionId) {
            Log::error('JazzCash: No subscription ID in successful payment response');
            return false;
        }

        $subscription = Subscription::find($subscriptionId);
        if (!$subscription) {
            Log::error('JazzCash: Subscription not found for ID: ' . $subscriptionId);
            return false;
        }

        if ($subscription->payment_status === 'completed') {
            Log::info('JazzCash: Payment already completed for subscription: ' . $subscriptionId);
            return true;
        }

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

        if ($subscription->user) {
            $subscription->user->update(['subscription_status' => 'active']);
        }

        Log::info('JazzCash: Payment processed successfully', [
            'subscription_id' => $subscriptionId,
            'txn_ref_no' => $txnRefNo,
            'response_code' => $responseCode
        ]);

        return true;
    }

    /**
     * Process failed payment from callback
     */
    public function processFailedPayment($response)
    {
        $subscriptionId = $response['ppmpf_1'] ?? null;
        $responseCode = $response['pp_ResponseCode'] ?? 'unknown';
        $responseMessage = $response['pp_ResponseMessage'] ?? 'Unknown error';

        if (!$subscriptionId) {
            Log::error('JazzCash: No subscription ID in failed payment response');
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

            // Delete subscription if payment failed
            $subscription->delete();

            Log::warning('JazzCash: Payment failed and subscription deleted', [
                'subscription_id' => $subscriptionId,
                'response_code' => $responseCode,
                'response_message' => $responseMessage
            ]);
        }

        return true;
    }

    /**
     * Get human-readable response message for response code
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