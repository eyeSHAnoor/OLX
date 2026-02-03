<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProformaToAccounting extends Mailable
{
    use Queueable, SerializesModels;

    public $orderNumber;
    public $supplierName;
    public $userOptionalNote;
    public $requestedBy;
    public $uploadedBy;
    public $uploadDate;
    public $filePath;
    public $fileName;
    public $uploadPaymentUrl;

    public function __construct($orderNumber, $supplierName, $userOptionalNote, $requestedBy, $uploadedBy, $uploadDate, $filePath, $fileName, $uploadPaymentUrl)
    {
        $this->orderNumber = $orderNumber;
        $this->supplierName = $supplierName;
        $this->userOptionalNote = $userOptionalNote;
        $this->requestedBy = $requestedBy;
        $this->uploadedBy = $uploadedBy;
        $this->uploadDate = $uploadDate;
        $this->filePath = $filePath;
        $this->fileName = $fileName;
        $this->uploadPaymentUrl = $uploadPaymentUrl;
    }

    public function build()
    {
        return $this->subject('New payment request #' . $this->orderNumber)
            ->view('emails.proforma-to-accounting')
            ->attach(storage_path('app/public/' . $this->filePath), [
                'as' => $this->fileName
            ]);
    }
}