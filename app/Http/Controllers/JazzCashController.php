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
        Log::info('JazzCash Callback received', $request->all());
        
        // Verify the response
        if ($this->jazzCashService->verifyPaymentResponse($request->all())) {
            $responseCode = $request->input('pp_ResponseCode');
            
            // 000 means successful transaction
            if ($responseCode === '000') {
                $this->jazzCashService->processSuccessfulPayment($request->all());
                return Inertia::render('home/Success', [
                    'message' => 'Payment completed successfully!',
                    'transaction_id' => $request->input('pp_TxnRefNo')
                ]);
            } else {
                $this->jazzCashService->processFailedPayment($request->all());
                return Inertia::render('home/Failed', [
                    'message' => 'Payment failed. Please try again.',
                    'error_code' => $responseCode,
                    'error_message' => $request->input('pp_ResponseMessage')
                ]);
            }
        }
        
        Log::error('JazzCash: Invalid hash in callback', $request->all());
        return Inertia::render('home/Failed', [
            'message' => 'Invalid payment response'
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