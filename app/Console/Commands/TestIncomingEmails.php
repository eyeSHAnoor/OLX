<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Webklex\IMAP\Facades\Client;

class TestIncomingEmails extends Command
{
    protected $signature = 'app:test-incoming-emails';
    protected $description = 'Test fetching latest email and printing details';

    public function handle()
    {
        $this->info('🔄 Starting test of latest emails...');

        try {
            $client = Client::account('default');
            $client->connect();
            $this->info('✅ IMAP connected successfully');

            $inbox = $client->getFolder('INBOX');

            // Method 1: Simple approach - get only the latest email
            $messages = $inbox->messages()->all()->setFetchOrder('desc')->limit(1)->get();

            if ($messages->count() === 0) {
                $this->info('📭 No messages found.');
                return;
            }

            $latestMessage = $messages->first();

            $this->info("📨 Latest email details:");
            $this->info("From: " . $latestMessage->getFrom()[0]->mail);
            $this->info("Subject: " . $latestMessage->getSubject());
            $this->info("Date: " . $latestMessage->getDate()->format('Y-m-d H:i:s'));
            $this->info("UID: " . $latestMessage->getUid());

            // Check for attachments
            $attachments = $latestMessage->getAttachments();
            if ($attachments->count() === 0) {
                $this->info("📎 No attachments found.");
            } else {
                $this->info("📎 Attachments found: {$attachments->count()}");
                foreach ($attachments as $attachment) {
                    $this->info("   - Name: {$attachment->name}, Size: " . strlen($attachment->content) . " bytes");
                }
            }

            $this->info('🎉 Latest email processed successfully.');

        } catch (\Exception $e) {
            $this->error('❌ IMAP connection failed: ' . $e->getMessage());
        }
    }
}