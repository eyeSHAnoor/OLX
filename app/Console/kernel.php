<?php

namespace App\Console;

use App\Jobs\CheckLowStockProductsJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        $schedule->job(new CheckLowStockProductsJob)
            ->everyTwoHours()
            ->withoutOverlapping();

        $schedule->command('verifications:clean')->daily();
        $schedule->command('subscriptions:expire')->hourly();
    }

    


    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}