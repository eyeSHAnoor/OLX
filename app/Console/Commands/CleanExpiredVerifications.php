<?php
// app/Console/Commands/CleanExpiredVerifications.php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CleanExpiredVerifications extends Command
{
    protected $signature = 'verifications:clean';
    protected $description = 'Delete users with expired verifications older than 24 hours';

    public function handle()
    {
        $deleted = User::whereNull('email_verified_at')
            ->whereNotNull('verification_code_expires_at')
            ->where('verification_code_expires_at', '<', now()->subHours(24))
            ->delete();

        $this->info("Deleted {$deleted} expired unverified users.");
    }
}