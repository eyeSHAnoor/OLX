<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Webklex\IMAP\Facades\Client;
use App\Models\TrackingOrder;
use App\Models\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessIncomingPaymentEmails extends Command
{
    protected $signature = 'app:process-incoming-payment-emails';
    protected $description = 'Processes incoming emails and extracts payment confirmation attachments in real-time';

    public function __construct()
    {
        parent::__construct();
        ini_set('memory_limit', '256M');
    }
    public function handle()
    {
        $this->info('🔄 Starting latest payment confirmation email processing...');

        try {
            $client = Client::account('default');
            $client->connect();
            $this->info('✅ IMAP connected successfully');

            $inbox = $client->getFolder('INBOX');

            // Only get the latest message
            // $messages = $inbox->messages()->unseen()->get();
            $messages = $inbox->messages()->all()->setFetchOrder('desc')->limit(1)->get();
            if ($messages->count() === 0) {
                $this->info('📭 No new messages found.');
                return;
            }

            $message = $messages->first();
            $this->processEmailMessage($message);

            $this->info('🎉 Latest email processed.');

        } catch (\Exception $e) {
            $this->error('❌ IMAP connection failed: ' . $e->getMessage());
            Log::error('IMAP connection failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }


    private function processEmailMessage($message)
    {
        try {
            $subject = $message->getSubject();
            $from = $message->getFrom()[0]->mail;

            $this->info("📨 Processing email from: {$from}");
            $this->info("📝 Subject: {$subject}");

            // Extract order number like: ORD-25-00001
            $orderNumber = $this->extractOrderNumber($subject);

            if (!$orderNumber) {
                $this->warn("❌ No order number found in subject: {$subject}");
                $message->setFlag('Seen');
                return false;
            }

            $this->info("🔍 Found order number: {$orderNumber}");

            // Find TrackingOrder by order_number
            $order = TrackingOrder::where('order_number', $orderNumber)->first();

            if (!$order) {
                $this->error("❌ Order not found: {$orderNumber}");
                $message->setFlag('Seen');
                return false;
            }

            $this->info("✅ Order found: {$order->id}");

            $attachments = $message->getAttachments();
            $this->info("📎 Found {$attachments->count()} attachments");

            $attachmentsProcessed = 0;

            // Process each attachment
            foreach ($attachments as $attachment) {
                if ($this->isValidPaymentConfirmation($attachment)) {
                    $this->processPaymentConfirmationAttachment($order, $attachment, $from, $message);
                    $attachmentsProcessed++;
                }
            }

            if ($attachmentsProcessed > 0) {
                // Update order status to payment confirmation verification
                $order->update([
                    'status' => 'payment_confirmation_verification',
                    'comments' => $order->comments . "\n\n[System] Payment confirmation received via email from {$from} on " . now()->format('Y-m-d H:i:s') . " - {$attachmentsProcessed} file(s) attached"
                ]);

                $this->info("✅ Successfully processed {$attachmentsProcessed} payment confirmation(s) for order {$orderNumber}");

                // Mark email as seen only if we processed attachments
                $message->setFlag('Seen');
                return true;
            } else {
                $this->warn("⚠️ No valid payment confirmation attachments found for order {$orderNumber}");
                $message->setFlag('Seen');
                return false;
            }

        } catch (\Exception $e) {
            $this->error("💥 Error processing email: " . $e->getMessage());
            Log::error('Error processing payment confirmation email', [
                'subject' => $subject ?? 'unknown',
                'from' => $from ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    private function extractOrderNumber($subject)
    {
        // Match patterns like: ORD-25-00001, Re: ORD-25-00001, Fwd: ORD-25-00001 Proforma, etc.
        preg_match('/ORD-\d{2}-\d{5}/', $subject, $matches);
        return $matches[0] ?? null;
    }

    private function isValidPaymentConfirmation($attachment)
    {
        $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
        $extension = strtolower(pathinfo($attachment->name, PATHINFO_EXTENSION));

        $isValid = in_array($extension, $allowedExtensions);

        if (!$isValid) {
            $this->warn("⚠️ Skipping invalid attachment: {$attachment->name} (extension: {$extension})");
        }

        return $isValid;
    }

    private function processPaymentConfirmationAttachment(TrackingOrder $order, $attachment, $fromEmail, $message)
    {
        $originalName = $attachment->name;
        $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        $this->info("📄 Processing attachment: {$originalName}");

        // Generate safe filename with timestamp to avoid conflicts
        $safeName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
        $fileName = 'payment_confirmation_' . time() . '_' . $safeName . '.' . $fileExtension;

        // Save to: tracking-orders/{order_id}/payment-confirmation/{filename}
        $path = "tracking-orders/{$order->id}/payment-confirmation/";
        $fullPath = $path . $fileName;

        // Create directory if it doesn't exist
        Storage::disk('public')->makeDirectory($path);

        // Save file to storage
        Storage::disk('public')->put($fullPath, $attachment->content);

        $this->info("💾 File saved: {$fullPath}");

        // Check if this is a duplicate (same order, same original filename recently)
        $recentDuplicate = File::where('fileable_id', $order->id)
            ->where('fileable_type', TrackingOrder::class)
            ->where('collection', 'payment_confirmation')
            ->where('file_name', $originalName)
            ->where('created_at', '>', now()->subHours(24))
            ->exists();

        // Create file record with payment confirmation metadata
        $file = File::create([
            'fileable_id' => $order->id,
            'fileable_type' => TrackingOrder::class,
            'file_location' => $fullPath,
            'file_name' => $originalName,
            'collection' => 'payment_confirmation',
            'meta' => [
                'type' => 'payment_confirmation',
                'source' => 'email_reply',
                'sender' => $fromEmail,
                'email_subject' => $message->getSubject(),
                'received_at' => now()->toISOString(),
                'file_size' => strlen($attachment->content),
                'mime_type' => $this->getMimeType($fileExtension),
                'confirmation_status' => 'pending_verification',
                'is_duplicate' => $recentDuplicate,
                'email_message_id' => $message->getMessageId(),
            ],
        ]);

        $this->info("✅ Payment confirmation recorded in database: {$originalName}");

        if ($recentDuplicate) {
            $this->warn("⚠️ Possible duplicate file detected: {$originalName}");
        }

        return $file;
    }

    private function getMimeType($extension)
    {
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
}