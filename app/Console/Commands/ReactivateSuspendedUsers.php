<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;

class ReactivateSuspendedUsers extends Command
{
    // Command signature (how you'll call it)
    protected $signature = 'users:reactivate-suspended';

    // Command description
    protected $description = 'Check suspended users and reactivate them if suspension period has ended';

    public function handle()
    {
        $now = Carbon::now();

        // Find all users with status suspended and suspended_until <= now
        $users = User::where('status', 'suspended')
            ->whereNotNull('suspended_until')
            ->where('suspended_until', '<=', $now)
            ->get();

        if ($users->isEmpty()) {
            $this->info('No suspended users to reactivate.');
            return 0;
        }

        foreach ($users as $user) {
            $user->update([
                'status' => 'active',
                'suspended_until' => null
            ]);
            $this->info("User ID {$user->id} ({$user->email}) reactivated.");
        }

        $this->info('All eligible suspended users have been reactivated.');
        return 0;
    }
}