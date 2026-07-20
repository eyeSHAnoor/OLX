<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Commands
|--------------------------------------------------------------------------
*/

// Delete expired unverified users every hour
Schedule::command('verifications:clean')->hourly();

// Expire subscriptions every minute
Schedule::command('subscriptions:expire')->everyMinute();

// Reactivate suspended users every minute
Schedule::command('users:reactivate-suspended')->everyMinute();

// Send scheduled notifications every minute
Schedule::command('notifications:send')->everyMinute();

// Test incoming emails every 5 minutes (or change as needed)
Schedule::command('app:test-incoming-emails')->everyFiveMinutes();