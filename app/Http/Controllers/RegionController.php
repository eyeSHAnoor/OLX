<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    /**
     * Get all regions by city name
     */
    public function getByCityName($cityName)
    {
        // Find city with its regions
        $city = City::with('regions')
            ->where('name', $cityName)
            ->first();

        if (!$city) {
            return response()->json([
                'message' => 'City not found'
            ], 404);
        }

        return response()->json([
            'city' => $city->name,
            'regions' => $city->regions
        ]);
    }
}