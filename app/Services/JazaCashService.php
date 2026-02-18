<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JazaCashService
{
    private $baseUrl;
    private $merchantId;
    private $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.jazacash.base_url', 'https://api.jazacash.com');
        $this->merchantId = config('services.jazacash.merchant_id');
        $this->apiKey = config('services.jazacash.api_key');
    }

    public function initiatePayment(array $paymentData)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/api/v1/payments/initiate', [
                'merchant_id' => $this->merchantId,
                'amount' => $paymentData['amount'],
                'currency' => 'PKR',
                'customer_email' => $paymentData['email'],
                'customer_phone' => $paymentData['phone'],
                'order_id' => $paymentData['order_id'],
                'description' => $paymentData['description'],
                'callback_url' => route('payment.callback'),
                'redirect_url' => route('payment.success'),
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'payment_url' => $response->json('payment_url'),
                    'transaction_id' => $response->json('transaction_id'),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('message', 'Payment initiation failed'),
            ];
        } catch (\Exception $e) {
            Log::error('JazaCash payment initiation error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Payment service unavailable',
            ];
        }
    }

    public function verifyPayment($transactionId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->get($this->baseUrl . '/api/v1/payments/verify/' . $transactionId);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'status' => $response->json('status'),
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error' => 'Payment verification failed',
            ];
        } catch (\Exception $e) {
            Log::error('JazaCash payment verification error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Payment verification service unavailable',
            ];
        }
    }
}