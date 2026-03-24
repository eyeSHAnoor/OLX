<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class ExpireSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire';
    protected $description = 'Expire subscriptions that have passed their end date';


    public function handle()
    {
        Subscription::where('ends_at', '<', now())
            ->where('status', 'active')
            ->update([
                'status' => 'expired'
            ]);

        $this->info('Expired subscriptions updated.');
    }
}
