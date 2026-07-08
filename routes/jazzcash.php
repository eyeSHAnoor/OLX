<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::any('/jazzcash-bridge', function(Request $request) {
    // \Log::info('JazzCash Bridge Route Hit', [
    //     'method' => $request->method(),
    //     'all' => $request->all(),
    //     'headers' => $request->headers->all()
    // ]);
    
    return response()->json([
        'status' => 'received',
        'data' => $request->all()
    ]);
});

Route::any('/payment-status', [App\Http\Controllers\JazzCashController::class, 'callback']);
Route::post('/payment-ipn', [App\Http\Controllers\JazzCashController::class, 'ipn']);