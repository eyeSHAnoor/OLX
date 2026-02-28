<?php
// app/Http/Controllers/ReceiptController.php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ReceiptController extends Controller
{
    public function show(Subscription $subscription)
    {
        $this->authorize('view', $subscription);

        if (!$subscription->receipt_image) {
            abort(404, 'Receipt not found');
        }

        $path = storage_path('app/private/' . $subscription->receipt_image);

        if (!file_exists($path)) {
            abort(404, 'Receipt file not found');
        }

        return response()->file($path);
    }

    public function download(Subscription $subscription)
    {
        // Authorization: Check if user can download this receipt
        $this->authorize('view', $subscription);
        
        if (!$subscription->receipt_image) {
            abort(404, 'Receipt not found');
        }

        $path = storage_path('app/private/' . $subscription->receipt_image);
        
        if (!file_exists($path)) {
            abort(404, 'Receipt file not found');
        }

        return response()->download($path, basename($subscription->receipt_image));
    }
}