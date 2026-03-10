<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class JazzCashService
{
    protected $merchantId;
    protected $password;
    protected $integritySalt;
    protected $returnUrl;

    public function __construct()
    {
        $this->merchantId = config('jazzcash.merchant_id');
        $this->password = config('jazzcash.password');
        $this->integritySalt = config('jazzcash.integrity_salt');
        $this->returnUrl = config('jazzcash.return_url');
    }

    /**
     * Prepare JazzCash payment request
     */
    public function preparePaymentRequest($amount, $subscriptionId, $user)
    {
        $txnRef = 'T' . time();
        $dateTime = now()->format('YmdHis');

        $data = [

            'pp_Version' => '2.0',
            'pp_TxnType' => 'MWALLET',
            'pp_Language' => 'EN',

            'pp_MerchantID' => $this->merchantId,
            'pp_Password' => $this->password,

            'pp_TxnRefNo' => $txnRef,
            'pp_TxnDateTime' => $dateTime,
            'pp_TxnExpiryDateTime' => now()->addHours(2)->format('YmdHis'),

            'pp_BillReference' => 'SUB' . $subscriptionId,

            'pp_Amount' => str_pad($amount * 100, 12, "0", STR_PAD_LEFT),

            'pp_TxnCurrency' => 'PKR',

            'pp_TxnDesc' => 'Subscription Payment',

            'pp_ReturnURL' => $this->returnUrl,

            'ppmpf_1' => $subscriptionId,
            'ppmpf_2' => $user->id,
        ];

        $data['pp_SecureHash'] = $this->generateHash($data);

        return $data;
    }

    /**
     * Generate JazzCash Hash
     */
    public function generateHash($data)
    {
        $filtered = [];

        foreach ($data as $key => $value) {

            if (str_starts_with($key, 'pp_') && $key !== 'pp_SecureHash') {
                $filtered[$key] = $value;
            }

        }

        ksort($filtered);

        $hashString = $this->integritySalt;

        foreach ($filtered as $value) {
            $hashString .= '&' . $value;
        }

        Log::info('JazzCash Hash String', ['string' => $hashString]);

        return strtoupper(
            hash_hmac('sha256', $hashString, $this->integritySalt)
        );
    }

    /**
     * Verify JazzCash response
     */
    public function verifyResponse($response)
    {
        $receivedHash = $response['pp_SecureHash'] ?? null;

        if (!$receivedHash) {
            return false;
        }

        $fields = [];

        foreach ($response as $key => $value) {

            if (str_starts_with($key, 'pp_') && $key !== 'pp_SecureHash') {
                $fields[$key] = $value;
            }

        }

        ksort($fields);

        $hashString = $this->integritySalt;

        foreach ($fields as $value) {
            $hashString .= '&' . $value;
        }

        $calculatedHash = strtoupper(
            hash_hmac('sha256', $hashString, $this->integritySalt)
        );

        Log::info('JazzCash Hash Verification', [
            'received' => $receivedHash,
            'calculated' => $calculatedHash
        ]);

        return hash_equals($receivedHash, $calculatedHash);
    }
}