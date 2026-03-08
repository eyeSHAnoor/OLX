<?php

use App\Models\Notification;
use App\Models\User;
use App\Events\NotificationCreated;
use App\Models\InternalOrderHistory;
use Illuminate\Support\Facades\Log;
use App\Models\Store;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Twilio\Rest\Client;
use App\Models\Role;

function getPaginate($paginate = null)
{
    return $paginate ?? request()->perPage ?? 30;
}

function getGlobalSearchFilter($searchableFields)
{
    return AllowedFilter::callback(
        'global',
        function ($query, $value) use ($searchableFields) {
            $query->where(function ($q) use ($searchableFields, $value) {
                foreach ($searchableFields as $field) {
                    $q->orWhere($field, 'LIKE', "%{$value}%");
                }
            });
        }
    );
}

if (!function_exists('getAllowedFilters')) {

    function getAllowedFilters($columns)
    {
        return collect($columns)->map(function ($column) {
            return AllowedFilter::exact($column, null, false);
        })->push(getGlobalSearchFilter([...$columns]))
            ->toArray();
    }
}


// this function will call scopeFilterByColumn from the model
function getFilterByColumn($columnName, $requestInput)
{
    return AllowedFilter::callback(
        $requestInput,
        fn($query, $values) => $query->filterByColumn($columnName, $values)
    );
}

if (!function_exists('getCountries')) {
    function getCountries()
    {
        return (array) json_decode(file_get_contents(resource_path('views/countries.json')));
    }
}

if (!function_exists('findCountry')) {
    function findCountry($searchValue)
    {
        if (empty($searchValue))
            return null;

        $countries = getCountries();

        foreach ($countries as $country) {
            $countryArray = (array) $country;

            if (in_array($searchValue, $countryArray, true)) {
                return $countryArray;
            }
        }

        return null;
    }
}

if (!function_exists('getCountry')) {
    function getCountry($searchValue)
    {
        $c = findCountry($searchValue);
        return (!empty($c)) ? $c['country'] : null;
    }
}

// if (!function_exists('notify')) {
//     function notify(
//         string $type,
//         string $title,
//         string $description,
//         Model|array|Collection $notifiables, // now allows Eloquent collection
//         ?Model $approvable = null,
//         string $url = null,
//         int $action_by = null,
//         int $requested_by = null,
//         array $data = []
//     ): Notification {
//         $notification = Notification::create([
//             'type' => $type,
//             'title' => $title,
//             'description' => $description,
//             'approvable_id' => $approvable?->id,
//             'approvable_type' => $approvable ? get_class($approvable) : null,
//             'action_by' => $action_by,
//             'requested_by' => $requested_by,
//             'url' => $url,
//             'data' => $data,
//         ]);

//         $userIds = collect($notifiables)->pluck('id')->toArray();
//         $notification->users()->attach($userIds);

//         return $notification;
//     }
// }


if (!function_exists('notify')) {
    function notify(
        string $type,
        string $title,
        string $description,
        Model|array|Collection $notifiables, // now allows Eloquent collection
        ?Model $approvable = null,
        ?string $url = null,
        ?int $action_by = null,
        ?int $requested_by = null,
        array $data = []
    ): Notification {
        $notification = Notification::create([
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'approvable_id' => $approvable?->id,
            'approvable_type' => $approvable ? get_class($approvable) : null,
            'action_by' => $action_by,
            'requested_by' => $requested_by,
            'url' => $url,
            'data' => $data,
        ]);

        $userIds = collect($notifiables)->pluck('id')->toArray();
        $notification->users()->attach($userIds);

        return $notification;
    }
}


if (!function_exists('numberToWords')) {
    function numberToWords($num)
    {
        $units = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine'];
        $teens = ['Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', 'Ten', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        if ($num == 0)
            return 'Zero';

        function convertLessThanOneThousand($n, $units, $teens, $tens)
        {
            if ($n == 0)
                return '';
            $result = '';

            if ($n >= 100) {
                $result .= $units[floor($n / 100)] . ' Hundred ';
                $n %= 100;
            }

            if ($n >= 20) {
                $result .= $tens[floor($n / 10)] . ' ';
                $n %= 10;
            } elseif ($n >= 10) {
                $result .= $teens[$n - 10] . ' ';
                $n = 0;
            }

            if ($n > 0) {
                $result .= $units[$n] . ' ';
            }

            return trim($result);
        }

        $scales = ['', 'Thousand', 'Million', 'Billion', 'Trillion'];
        $words = '';
        $scaleIndex = 0;

        while ($num > 0) {
            $chunk = $num % 1000;
            if ($chunk != 0) {
                $chunkWords = convertLessThanOneThousand($chunk, $units, $teens, $tens);
                if ($scaleIndex > 0) {
                    $chunkWords .= ' ' . $scales[$scaleIndex];
                }
                $words = $chunkWords . ' ' . $words;
            }
            $num = (int) ($num / 1000);
            $scaleIndex++;
        }

        return trim($words);
    }
}

if (!function_exists('normalizePhoneNumber')) {
    function normalizePhoneNumber($number)
    {
        // Remove spaces, dashes, parentheses, etc., but keep '+' if it's the first character
        $number = trim($number);
        $number = preg_replace('/[^\d+]/', '', $number);

        // If number starts with '+', remove it and treat as international
        if (strpos($number, '+') === 0) {
            $number = substr($number, 1);
        } // If number starts with '00', treat as international and remove leading 00
        elseif (strpos($number, '00') === 0) {
            $number = substr($number, 2);
        }
        // Else assume it's a local number, keep as-is (0333xxxxxxx)
        // No leading zero removal in this case

        return $number;
    }
}
if (!function_exists('convertWeightToKg')) {
    function convertWeightToKg(?string $weight, string $unit): ?float
    {
        if ($weight === null || $weight === '') {
            return null;
        }

        $value = (float) $weight;

        switch (strtolower($unit)) {
            case 'kg':
                return $value;
            case 'g':
                return $value / 1000;
            case 'lbs':
                return $value * 0.45359237;
            case 'oz':
                return $value * 0.0283495;
            default:
                return $value; // assume already in kg if unknown
        }
    }
}

if (!function_exists('hasPermissions')) {
    function hasPermissions($permissions, $message = 'Forbidden')
    {
        $user = auth()->user();

        // if (auth()->user()->hasRole('SuperAdmin')) {
        //     return true;
        // }
        if ($user && $user->hasRole('super_admin')) {
            return true;
        }

        if (!$user->hasPermissionTo($permissions)) {
            abort(403, $message);
        }
    }
}

if (!function_exists('canViewDraftOrders')) {
    function canViewDraftOrders()
    {
        $user = auth()->user();
        if (!$user)
            return false;
        if ($user->hasRole('SuperAdmin'))
            return true;
        return $user->hasPermissionTo('draft-shown_internal_order');
    }
}

if (!function_exists('updateOrderStatus')) {
    function updateOrderStatus($order)
    {
        $statuses = $order->items()->pluck('status')->toArray();
        $statusesCollection = collect($statuses);

        if ($statusesCollection->every(fn($s) => $s === 'not_available')) {
            // 1. All cancelled
            $order->update(['status' => 'cancelled']);
        } elseif ($statusesCollection->every(fn($s) => in_array($s, ['confirmed', 'not_available']))) {
            // 2. All confirmed or cancelled
            $order->update(['status' => 'completed']);
        } elseif (
            $statusesCollection->contains('partial') ||
            $statusesCollection->contains('partial_received') ||
            ($statusesCollection->contains('not_available') && $statusesCollection->contains('confirmed'))
        ) {
            // 3, 4, 5. Any mix with partials or combination of cancel+confirmed
            $order->update(['status' => 'partial']);
        } elseif ($statusesCollection->every(fn($s) => $s === 'pending')) {
            // 6. All pending
            $order->update(['status' => 'pending']);
        } else {
            // default fallback
            $order->update(['status' => 'partial']);
        }
    }
}

if (!function_exists('sendWhatsAppMessage')) {
    function sendWhatsAppMessage(string $to, string $message): array
    {
        try {
            $sid = env('TWILIO_SID');
            $token = env('TWILIO_AUTH_TOKEN');
            $from = env('TWILIO_WHATSAPP_FROM', 'whatsapp:+14155238886'); // default sandbox number

            $twilio = new Client($sid, $token);

            $twilio->messages->create($to, [
                'from' => $from,
                'body' => $message,
            ]);

            return [
                'success' => true,
                'message' => 'WhatsApp message sent successfully!',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'WhatsApp sending failed: ' . $e->getMessage(),
            ];
        }
    }
}


if (!function_exists('sendNotification')) {

    /**
     * Send notification + optional WhatsApp message
     * to users who have a given permission or are SuperAdmins.
     *
     * @param string $title
     * @param string $message
     * @param int|null $actionBy
     * @param string|null $permission Permission required to receive the notification
     * @param bool $sendWhatsApp
     * @return array
     */
    function sendNotification(
        string $title,
        string $message,
        ?int $actionBy = null,
        ?string $permission = null,
        bool $sendWhatsApp = true
    ): array {
        try {
            // --- Create DB notification ---
            $notification = Notification::create([
                'title' => $title,
                'message' => $message,
                'type' => 'received',
                'action_by' => $actionBy,
                'requested_by' => null,
            ]);

            // --- Broadcast to frontend (Pusher, etc.) ---
            event(new NotificationCreated($notification));

            // --- Determine recipients ---
            $users = User::all()->filter(function ($user) use ($permission) {
                if ($user->hasRole('SuperAdmin'))
                    return true;
                if (!$permission)
                    return true;
                return $user->hasPermissionTo($permission);
            });

            // dd($users->name);

            // --- Attach users to the notification in DB ---
            // if ($users->isNotEmpty()) {
            //     $notification->users()->attach($users->pluck('id')->toArray());
            // }

            // --- Prepare WhatsApp numbers ---
            if ($sendWhatsApp) {
                $phones = $users
                    ->pluck('phone') // get phone numbers
                    ->map(fn($p) => trim($p)) // remove whitespace
                    ->filter(fn($p) => !empty($p)) // skip empty
                    ->map(fn($p) => 'whatsapp:' . preg_replace('/[^0-9]/', '', $p)) // sanitize
                    ->toArray();

                // --- Send WhatsApp messages ---
                foreach ($phones as $to) {
                    $response = sendWhatsAppMessage($to, $message);

                    if (!$response['success']) {
                        \Log::error("WhatsApp failed for {$to}: " . $response['message']);
                    }
                }
            }

            return [
                'success' => true,
                'message' => 'Notification created and sent successfully.',
            ];
        } catch (\Exception $e) {
            \Log::error('Notification sending failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Notification sending failed: ' . $e->getMessage(),
            ];
        }
    }
}


if (!function_exists('recordItemHistory')) {
    function recordItemHistory($item, $partialQty, $user, $message, $status)
    {
        $statusForHistory = $item->status === 'pending' ? 'requested' : $item->status;
        // Prevent recording duplicate partials
        $exists = InternalOrderHistory::where('internal_order_item_id', $item->id)
            ->where('status', $item->status)
            ->where('quantity', $partialQty)
            ->latest()
            ->first();

        if ($exists) {
            return;
        }

        $user = Auth::user();
        InternalOrderHistory::create([
            'internal_order_id' => $item->order->id,
            'internal_order_item_id' => $item->id,
            'order_number' => $item->order->order_number,
            'product_name' => $item->product_name,
            'quantity' => $partialQty,
            'status' => $status,
            'message' => $message,
            'action_by' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}



