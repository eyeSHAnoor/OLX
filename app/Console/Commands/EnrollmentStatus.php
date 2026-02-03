<?php

namespace App\Console\Commands;

use App\Enums\StudentEnrollmentStatus;
use App\Models\Enrollment;
use Illuminate\Console\Command;

class EnrollmentStatus extends Command
{

    protected $signature = 'app:enrollment-status';

    protected $description = 'Check and Update each enrollment status';

    public function handle()
    {
        $this->info('🔄 Enrollment Status Check job started at ' . now());
        $this->info('================');

        $overdueEnrollments = Enrollment::whereHas('installmentPlans', function ($query) {
            $query->where('due_date', '<', now())
                ->where('status', '!=', 'paid');
        })
            ->whereNotIn('status', [
                StudentEnrollmentStatus::INACTIVATED->value,
                StudentEnrollmentStatus::CANCELLED->value,
                StudentEnrollmentStatus::POSTPONED->value,
                StudentEnrollmentStatus::COMPLETED->value,
            ])
            ->with(['installmentPlans' => function ($query) {
                $query->where('due_date', '<', now())
                    ->where('status', '!=', 'paid');
            }])
            ->get();

        $enrollmentsWithStatus = $overdueEnrollments?->map(function ($enrollment) {
            // Calculate grace days from due_date
            $maxGraceDays = $enrollment->installmentPlans->map(function ($plan) {
                $dueDate = \Carbon\Carbon::parse($plan->due_date);
                return $dueDate->isPast() ? $dueDate->diffInDays(now()) : 0;
            })->max();

//            dd( $maxGraceDays);

            // Map grace days to enum value
            $status = match (true) {
                $maxGraceDays <= 7 => StudentEnrollmentStatus::SUSPENDING->value,
                $maxGraceDays <= 14 => StudentEnrollmentStatus::SUSPENDED->value,
                $maxGraceDays <= 21 => StudentEnrollmentStatus::INACTIVE->value,
                $maxGraceDays <= 29 => StudentEnrollmentStatus::INACTIVATING->value,
                default => StudentEnrollmentStatus::INACTIVATED->value,
            };

//            dd($status);

            $enrollment->status = $status;
            $enrollment->save();

            return $enrollment;
        });

        $this->info('✅ Job completed at ' . now());
    }
}
