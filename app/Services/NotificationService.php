<?php

namespace App\Services;

use App\Enums\NotificationStatus;
use App\Models\Course;
use App\Models\Discount;
use App\Models\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    public static function handle(Notification $notification, string $action): void
    {
        match ($notification->type) {
            'manual_discount' => self::handleManualDiscount($notification, $action),
            'payment_transfer' => self::handlePaymentTransfer($notification, $action),
            default => throw new \Exception("Unsupported notification type: {$notification->type}"),
        };

        // update notification status
        $notification->update([
            'action_status' => $action,
            'action_by' => auth()->id(),
            'action_at' => now(),
        ]);
    }

    protected static function handleManualDiscount(Notification $notification, string $action): void
    {
        $enrollment = $notification->approvable->load('feePlans', 'installmentPlans');
        $discountAmount = (float)Arr::get($notification->data, 'discount_amount');
        $discountLabel = Arr::get($notification->data, 'discount_label');
        $course = Course::find(Arr::get($notification->data, 'course_id'));

        if (!$enrollment || !$course) {
            throw new \Exception('Related enrollment or course not found.');
        }

        if ($action === NotificationStatus::APPROVED->value) {

            DB::transaction(function () use ($enrollment, $discountAmount, $discountLabel, $notification, $course) {

                // Step 1: Attach Discount to Course
                $newDiscount = $course->discounts()->create([
                    'label' => $discountLabel,
                    'discount_unit' => 'amount',
                    'discount_value' => $discountAmount,
                    'start_date' => now(),
                    'end_date' => now(),
                    'is_rms_enabled' => true,
                    'fee_plan' => [Discount::FEE_PLAN_SINGLE],
                ]);

                // Step 2: Update Enrollment with discount
                $enrollment->update([
                    'discount_id' => $newDiscount->id,
                    'discount_label' => $newDiscount->label,
                ]);

                // Step 3: Apply discount in fee_plans
                $filteredFeePlans = $enrollment->feePlans->filter(function ($item) {
                    return str_contains(strtolower($item['label']), 'course')
                        && $item['type'] === 'local';
                });

                foreach ($filteredFeePlans as &$feePlan) {
                    $feePlan->discount = $discountAmount;
                    $feePlan->save();
                }

                // Step 4: Apply discount to installments (pending + course)
                $filteredInstallments = $enrollment->installmentPlans->filter(function ($item) {
                    return str_contains(strtolower($item['label']), 'course')
                        && $item['type'] === 'local'
                        && $item['status'] !== 'paid';
                });

                $totalAmount = $filteredInstallments->sum('amount');

                foreach ($filteredInstallments as &$installment) {
                    if ($totalAmount > 0) {
                        // Calculate discount proportion for this installment
                        $proportion = $installment->amount / $totalAmount;
                        $discount = $proportion * $discountAmount;

                        // Apply discount (create new array to avoid modifying original)
//                        $adjusted = clone $installment; // If it's an object
                        $installment->amount = max(0, $installment->amount - $discount);
                        $installment->save();
                    }
                }


                // Step 5: update notification
                $notification->update(['action_status' => NotificationStatus::APPROVED]);

                // Step 6: Add comments approval
                $enrollment->addComments('Manual Discount Request', "Discount request has been APPROVED");
            });

            // Apply logic to update fee_plans/installment_plans if needed
        } elseif ($action === 'rejected') {
            $notification->update(['action_status' => NotificationStatus::REJECTED]);

            $enrollment->addComments('Manual Discount Request', "Discount request has been REJECTED");
        }
    }

    protected static function handlePaymentTransfer(Notification $notification, string $action): void
    {
        $transfer = $notification->approvable;

        if (!$transfer) {
            throw new \Exception('Related payment transfer not found.');
        }

        if ($action === 'approved') {
            $transfer->approve(); // assumes custom method
        } elseif ($action === 'rejected') {
            $transfer->reject(); // assumes custom method
        }
    }
}
