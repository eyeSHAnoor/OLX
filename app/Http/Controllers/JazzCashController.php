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
        
        // ADD THIS DEBUG LOG
        Log::debug('JazzCash Callback Full Data', [
            'all' => $data,
            'secure_hash' => $data['pp_SecureHash'] ?? 'missing'
        ]);
        
        // Verify the response
        $isValid = $this->jazzCashService->verifyPaymentResponse($data);
        
        // ADD THIS DEBUG LOG
        Log::debug('JazzCash Verification Result', ['isValid' => $isValid]);
        
        if ($isValid) {
            $responseCode = $request->input('pp_ResponseCode');
            
            if ($responseCode === '000' || $responseCode === '100') {
                $this->jazzCashService->processSuccessfulPayment($data);
                
                // Check if the Vue component exists
                if (!file_exists(resource_path('js/pages/home/Success.vue'))) {
                    Log::error('Success.vue component not found');
                    return response()->json(['error' => 'Success page not found'], 500);
                }
                
                return Inertia::render('home/Success', [
                    'message' => 'Payment completed successfully!',
                    'transaction_id' => $request->input('pp_TxnRefNo')
                ]);
            } else {
                $this->jazzCashService->processFailedPayment($data);
                $errorMessage = $this->jazzCashService->getResponseMessage($responseCode);
                
                // Check if the Vue component exists
                if (!file_exists(resource_path('js/pages/home/Failed.vue'))) {
                    Log::error('Failed.vue component not found');
                    return response()->json(['error' => 'Failed page not found'], 500);
                }
                
                return Inertia::render('home/Failed', [
                    'message' => 'Payment failed: ' . $errorMessage,
                    'error_code' => $responseCode,
                    'error_message' => $request->input('pp_ResponseMessage', $errorMessage)
                ]);
            }
        }
        
        Log::error('JazzCash: Invalid hash in callback', $data);
        
        // Check if the Vue component exists
        if (!file_exists(resource_path('js/pages/home/Failed.vue'))) {
            Log::error('Failed.vue component not found');
            return response()->json(['error' => 'Failed page not found'], 500);
        }
        
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