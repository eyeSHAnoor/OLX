<?php
namespace App\Http\Controllers\Traits;

use Illuminate\Support\Facades\Log;
// app/Http/Controllers/Traits/LocationFilter.php
trait LocationFilter
{
    protected function applyLocation($query)
    {
        Log::info('Applying location filter with session city: ' . session('city', 'Pakistan') . ' and region: ' . session('region'));
        $city = strtolower(session('city', 'Pakistan'));
        $region = session('region');

        if ($city !== 'pakistan') {
            $query->whereRaw('LOWER(city) = ?', [$city]);
            if ($region) {
                $query->whereRaw('LOWER(region) = ?', [strtolower($region)]);
            }
        }

        return $query;
    }
}