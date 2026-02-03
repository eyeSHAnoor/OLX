<?php

// app/Console/Commands/CheckLowStockCommand.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\CheckLowStockProductsJob;

class CheckLowStockCommand extends Command
{
    protected $signature = 'products:check-low-stock';
    protected $description = 'Check low stock products';

    public function handle()
    {
        CheckLowStockProductsJob::dispatch(); // runs immediately
        $this->info('Low stock check job dispatched!');
    }
}
